<?php
/**
 * Transaction Class
 * Handles income and expense transactions
 */

class Transaction {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Add income transaction
     */
    public function addIncome($user_id, $tracker_id, $data) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO income_transactions (user_id, tracker_id, source_id, amount, transaction_date, description, notes) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $user_id,
                $tracker_id,
                $data['source_id'] ?? null,
                $data['amount'],
                $data['transaction_date'],
                $data['description'] ?? '',
                $data['notes'] ?? ''
            ]);
            
            return [
                'success' => true,
                'transaction_id' => $this->db->lastInsertId(),
                'message' => 'Income added successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Add expense transaction
     */
    public function addExpense($user_id, $tracker_id, $data) {
        try {
            // Handle checkbox for is_recurring if present
            $is_recurring = 0;
            if (isset($data['is_recurring']) && ($data['is_recurring'] === '1' || $data['is_recurring'] === 1 || $data['is_recurring'] === true)) {
                $is_recurring = 1;
            }
            
            $stmt = $this->db->prepare(
                "INSERT INTO expense_transactions (user_id, tracker_id, category_id, amount, transaction_date, description, notes, payment_method, is_recurring) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $user_id,
                $tracker_id,
                $data['category_id'] ?? null,
                $data['amount'],
                $data['transaction_date'],
                $data['description'] ?? '',
                $data['notes'] ?? '',
                $data['payment_method'] ?? 'cash',
                $is_recurring
            ]);
            
            return [
                'success' => true,
                'transaction_id' => $this->db->lastInsertId(),
                'message' => 'Expense added successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get income transactions
     */
    public function getIncome($user_id, $tracker_id, $filters = []) {
        try {
            $sql = "SELECT it.*, isc.source_name, isc.source_type 
                    FROM income_transactions it 
                    LEFT JOIN income_sources isc ON it.source_id = isc.source_id 
                    WHERE it.user_id = ? AND it.tracker_id = ?";
            
            $params = [$user_id, $tracker_id];
            
            if (isset($filters['start_date'])) {
                $sql .= " AND it.transaction_date >= ?";
                $params[] = $filters['start_date'];
            }
            
            if (isset($filters['end_date'])) {
                $sql .= " AND it.transaction_date <= ?";
                $params[] = $filters['end_date'];
            }
            
            if (isset($filters['source_id'])) {
                $sql .= " AND it.source_id = ?";
                $params[] = $filters['source_id'];
            }
            
            $sql .= " ORDER BY it.transaction_date DESC";
            
            if (isset($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $transactions = $stmt->fetchAll();
            
            return [
                'success' => true,
                'transactions' => $transactions
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get expense transactions
     */
    public function getExpenses($user_id, $tracker_id, $filters = []) {
        try {
            $sql = "SELECT et.*, ec.category_name, ec.icon, ec.color 
                    FROM expense_transactions et 
                    LEFT JOIN expense_categories ec ON et.category_id = ec.category_id 
                    WHERE et.user_id = ? AND et.tracker_id = ?";
            
            $params = [$user_id, $tracker_id];
            
            if (isset($filters['start_date'])) {
                $sql .= " AND et.transaction_date >= ?";
                $params[] = $filters['start_date'];
            }
            
            if (isset($filters['end_date'])) {
                $sql .= " AND et.transaction_date <= ?";
                $params[] = $filters['end_date'];
            }
            
            if (isset($filters['category_id'])) {
                $sql .= " AND et.category_id = ?";
                $params[] = $filters['category_id'];
            }
            
            $sql .= " ORDER BY et.transaction_date DESC";
            
            if (isset($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $transactions = $stmt->fetchAll();
            
            return [
                'success' => true,
                'transactions' => $transactions
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update transaction
     */
    public function updateIncome($transaction_id, $user_id, $data) {
        try {
            $allowed_fields = ['source_id', 'amount', 'transaction_date', 'description', 'notes'];
            $update_fields = [];
            $values = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowed_fields)) {
                    $update_fields[] = "$key = ?";
                    $values[] = $value;
                }
            }
            
            if (empty($update_fields)) {
                throw new Exception("No valid fields to update");
            }
            
            $values[] = $transaction_id;
            $values[] = $user_id;
            
            $sql = "UPDATE income_transactions SET " . implode(', ', $update_fields) . 
                   " WHERE transaction_id = ? AND user_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return [
                'success' => true,
                'message' => 'Income updated successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function updateExpense($transaction_id, $user_id, $data) {
        try {
            $allowed_fields = ['category_id', 'amount', 'transaction_date', 'description', 'notes', 'payment_method'];
            $update_fields = [];
            $values = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowed_fields)) {
                    $update_fields[] = "$key = ?";
                    $values[] = $value;
                }
            }
            
            if (empty($update_fields)) {
                throw new Exception("No valid fields to update");
            }
            
            $values[] = $transaction_id;
            $values[] = $user_id;
            
            $sql = "UPDATE expense_transactions SET " . implode(', ', $update_fields) . 
                   " WHERE transaction_id = ? AND user_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return [
                'success' => true,
                'message' => 'Expense updated successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete transactions
     */
    public function deleteIncome($transaction_id, $user_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM income_transactions WHERE transaction_id = ? AND user_id = ?");
            $stmt->execute([$transaction_id, $user_id]);
            
            return [
                'success' => true,
                'message' => 'Income deleted successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function deleteExpense($transaction_id, $user_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM expense_transactions WHERE transaction_id = ? AND user_id = ?");
            $stmt->execute([$transaction_id, $user_id]);
            
            return [
                'success' => true,
                'message' => 'Expense deleted successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get financial summary
     */
    public function getSummary($user_id, $tracker_id, $start_date, $end_date) {
        try {
            // Get total income
            $stmt = $this->db->prepare(
                "SELECT COALESCE(SUM(amount), 0) as total_income 
                 FROM income_transactions 
                 WHERE user_id = ? AND tracker_id = ? AND transaction_date BETWEEN ? AND ?"
            );
            $stmt->execute([$user_id, $tracker_id, $start_date, $end_date]);
            $income = $stmt->fetch()['total_income'];
            
            // Get total expenses
            $stmt = $this->db->prepare(
                "SELECT COALESCE(SUM(amount), 0) as total_expenses 
                 FROM expense_transactions 
                 WHERE user_id = ? AND tracker_id = ? AND transaction_date BETWEEN ? AND ?"
            );
            $stmt->execute([$user_id, $tracker_id, $start_date, $end_date]);
            $expenses = $stmt->fetch()['total_expenses'];
            
            // Get expenses by category
            $stmt = $this->db->prepare(
                "SELECT ec.category_name, ec.icon, ec.color, COALESCE(SUM(et.amount), 0) as total 
                 FROM expense_transactions et 
                 LEFT JOIN expense_categories ec ON et.category_id = ec.category_id 
                 WHERE et.user_id = ? AND et.tracker_id = ? AND et.transaction_date BETWEEN ? AND ? 
                 GROUP BY et.category_id, ec.category_name, ec.icon, ec.color 
                 ORDER BY total DESC"
            );
            $stmt->execute([$user_id, $tracker_id, $start_date, $end_date]);
            $expenses_by_category = $stmt->fetchAll();
            
            return [
                'success' => true,
                'summary' => [
                    'total_income' => $income,
                    'total_expenses' => $expenses,
                    'net_savings' => $income - $expenses,
                    'expenses_by_category' => $expenses_by_category
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

