<?php
/**
 * IncomeSource Class
 * Manages income sources for users
 */

class IncomeSource {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Create a new income source
     */
    public function create($user_id, $tracker_id, $data) {
        try {
            // Handle checkbox - convert to proper boolean/integer
            $is_recurring = 0;
            if (isset($data['is_recurring']) && ($data['is_recurring'] === '1' || $data['is_recurring'] === 1 || $data['is_recurring'] === true)) {
                $is_recurring = 1;
            }
            
            // Handle expected_amount - convert empty string to null
            $expected_amount = null;
            if (isset($data['expected_amount']) && $data['expected_amount'] !== '' && $data['expected_amount'] !== null) {
                $expected_amount = floatval($data['expected_amount']);
            }
            
            $stmt = $this->db->prepare(
                "INSERT INTO income_sources (user_id, tracker_id, source_name, source_type, description, is_recurring, frequency, expected_amount) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $user_id,
                $tracker_id,
                $data['source_name'],
                $data['source_type'],
                $data['description'] ?? '',
                $is_recurring,
                $data['frequency'] ?? 'monthly',
                $expected_amount
            ]);
            
            return [
                'success' => true,
                'source_id' => $this->db->lastInsertId(),
                'message' => 'Income source created successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all income sources for a user (filtered by tracker)
     */
    public function getUserSources($user_id, $tracker_id, $is_active = true) {
        try {
            $sql = "SELECT * FROM income_sources WHERE user_id = ? AND tracker_id = ?";
            $params = [$user_id, $tracker_id];
            
            if ($is_active !== null) {
                $sql .= " AND is_active = ?";
                $params[] = $is_active;
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $sources = $stmt->fetchAll();
            
            return [
                'success' => true,
                'sources' => $sources
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an income source
     */
    public function update($source_id, $user_id, $data) {
        try {
            $allowed_fields = ['source_name', 'source_type', 'description', 'is_recurring', 'frequency', 'expected_amount', 'is_active'];
            $update_fields = [];
            $values = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowed_fields)) {
                    // Handle boolean fields properly
                    if ($key === 'is_recurring' || $key === 'is_active') {
                        $value = ($value === '1' || $value === 1 || $value === true) ? 1 : 0;
                    }
                    // Handle decimal fields - convert empty string to null
                    if ($key === 'expected_amount' && ($value === '' || $value === null)) {
                        $value = null;
                    }
                    $update_fields[] = "$key = ?";
                    $values[] = $value;
                }
            }
            
            if (empty($update_fields)) {
                throw new Exception("No valid fields to update");
            }
            
            $values[] = $source_id;
            $values[] = $user_id;
            
            $sql = "UPDATE income_sources SET " . implode(', ', $update_fields) . 
                   " WHERE source_id = ? AND user_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return [
                'success' => true,
                'message' => 'Income source updated successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete an income source
     */
    public function delete($source_id, $user_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM income_sources WHERE source_id = ? AND user_id = ?");
            $stmt->execute([$source_id, $user_id]);
            
            return [
                'success' => true,
                'message' => 'Income source deleted successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

