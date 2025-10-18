<?php
/**
 * Budget Class
 * Handles budget management and tracking
 */

class Budget {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Create a new budget
     */
    public function create($user_id, $tracker_id, $data) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO budgets (user_id, tracker_id, budget_name, budget_type, category_id, amount, period, start_date, end_date, alert_threshold) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $user_id,
                $tracker_id,
                $data['budget_name'],
                $data['budget_type'] ?? 'personal',
                $data['category_id'] ?? null,
                $data['amount'],
                $data['period'] ?? 'monthly',
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['alert_threshold'] ?? 80.00
            ]);
            
            return [
                'success' => true,
                'budget_id' => $this->db->lastInsertId(),
                'message' => 'Budget created successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all budgets for a user (filtered by tracker)
     */
    public function getUserBudgets($user_id, $tracker_id, $is_active = true) {
        try {
            $sql = "SELECT b.*, c.category_name, c.icon, c.color 
                    FROM budgets b 
                    LEFT JOIN expense_categories c ON b.category_id = c.category_id 
                    WHERE b.user_id = ? AND b.tracker_id = ?";
            
            if ($is_active !== null) {
                $sql .= " AND b.is_active = ?";
                $params = [$user_id, $tracker_id, $is_active];
            } else {
                $params = [$user_id, $tracker_id];
            }
            
            $sql .= " ORDER BY b.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $budgets = $stmt->fetchAll();
            
            // Calculate spending for each budget
            foreach ($budgets as &$budget) {
                $budget['spent'] = $this->calculateSpending($user_id, $budget);
                $budget['remaining'] = $budget['amount'] - $budget['spent'];
                $budget['percentage'] = ($budget['spent'] / $budget['amount']) * 100;
            }
            
            return [
                'success' => true,
                'budgets' => $budgets
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate spending for a budget
     */
    private function calculateSpending($user_id, $budget) {
        $start_date = $budget['start_date'];
        $end_date = $budget['end_date'] ?? date('Y-m-d');
        
        $sql = "SELECT COALESCE(SUM(amount), 0) as total 
                FROM expense_transactions 
                WHERE user_id = ? AND tracker_id = ?
                AND transaction_date BETWEEN ? AND ?";
        
        $params = [$user_id, $budget['tracker_id'], $start_date, $end_date];
        
        if ($budget['category_id']) {
            $sql .= " AND category_id = ?";
            $params[] = $budget['category_id'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'];
    }
    
    /**
     * Update a budget
     */
    public function update($budget_id, $user_id, $data) {
        try {
            $allowed_fields = ['budget_name', 'amount', 'period', 'start_date', 'end_date', 'alert_threshold', 'is_active'];
            $update_fields = [];
            $values = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowed_fields)) {
                    // Handle boolean fields properly
                    if ($key === 'is_active') {
                        $value = ($value === '1' || $value === 1 || $value === true) ? 1 : 0;
                    }
                    $update_fields[] = "$key = ?";
                    $values[] = $value;
                }
            }
            
            if (empty($update_fields)) {
                throw new Exception("No valid fields to update");
            }
            
            $values[] = $budget_id;
            $values[] = $user_id;
            
            $sql = "UPDATE budgets SET " . implode(', ', $update_fields) . " WHERE budget_id = ? AND user_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return [
                'success' => true,
                'message' => 'Budget updated successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a budget
     */
    public function delete($budget_id, $user_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM budgets WHERE budget_id = ? AND user_id = ?");
            $stmt->execute([$budget_id, $user_id]);
            
            return [
                'success' => true,
                'message' => 'Budget deleted successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

