<?php
/**
 * Tracker Class
 * Handles budget tracker/workspace management
 */

class Tracker {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Create a new tracker
     */
    public function create($user_id, $data) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO trackers (user_id, tracker_name, tracker_type, description, icon, color) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $user_id,
                $data['tracker_name'],
                $data['tracker_type'] ?? 'personal',
                $data['description'] ?? '',
                $data['icon'] ?? '💰',
                $data['color'] ?? '#6366f1'
            ]);
            
            $tracker_id = $this->db->lastInsertId();
            
            // If this is user's first tracker, make it default
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM trackers WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $count = $stmt->fetch()['count'];
            
            if ($count == 1) {
                $this->setDefault($tracker_id, $user_id);
            }
            
            return [
                'success' => true,
                'tracker_id' => $tracker_id,
                'message' => 'Tracker created successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all trackers for a user
     */
    public function getUserTrackers($user_id, $is_active = true) {
        try {
            $sql = "SELECT * FROM trackers WHERE user_id = ?";
            $params = [$user_id];
            
            if ($is_active !== null) {
                $sql .= " AND is_active = ?";
                $params[] = $is_active;
            }
            
            $sql .= " ORDER BY is_default DESC, created_at ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $trackers = $stmt->fetchAll();
            
            return [
                'success' => true,
                'trackers' => $trackers
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get a specific tracker
     */
    public function getTracker($tracker_id, $user_id) {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM trackers WHERE tracker_id = ? AND user_id = ?"
            );
            $stmt->execute([$tracker_id, $user_id]);
            $tracker = $stmt->fetch();
            
            if (!$tracker) {
                throw new Exception("Tracker not found");
            }
            
            return [
                'success' => true,
                'tracker' => $tracker
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update a tracker
     */
    public function update($tracker_id, $user_id, $data) {
        try {
            // Verify ownership
            if (!$this->verifyOwnership($tracker_id, $user_id)) {
                throw new Exception("Unauthorized");
            }
            
            $allowed_fields = ['tracker_name', 'tracker_type', 'description', 'icon', 'color', 'is_active'];
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
            
            $values[] = $tracker_id;
            $values[] = $user_id;
            
            $sql = "UPDATE trackers SET " . implode(', ', $update_fields) . 
                   " WHERE tracker_id = ? AND user_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return [
                'success' => true,
                'message' => 'Tracker updated successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a tracker
     */
    public function delete($tracker_id, $user_id) {
        try {
            // Verify ownership
            if (!$this->verifyOwnership($tracker_id, $user_id)) {
                throw new Exception("Unauthorized");
            }
            
            // Don't allow deleting if it's the only tracker
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM trackers WHERE user_id = ? AND is_active = 1");
            $stmt->execute([$user_id]);
            $count = $stmt->fetch()['count'];
            
            if ($count <= 1) {
                throw new Exception("Cannot delete your only tracker");
            }
            
            // Check if it's the default tracker
            $stmt = $this->db->prepare("SELECT is_default FROM trackers WHERE tracker_id = ?");
            $stmt->execute([$tracker_id]);
            $is_default = $stmt->fetch()['is_default'];
            
            // If deleting default tracker, set another as default first
            if ($is_default) {
                $stmt = $this->db->prepare(
                    "SELECT tracker_id FROM trackers 
                     WHERE user_id = ? AND tracker_id != ? AND is_active = 1 
                     LIMIT 1"
                );
                $stmt->execute([$user_id, $tracker_id]);
                $new_default = $stmt->fetch();
                
                if ($new_default) {
                    $this->setDefault($new_default['tracker_id'], $user_id);
                }
            }
            
            // Soft delete (set inactive)
            $stmt = $this->db->prepare(
                "UPDATE trackers SET is_active = 0 WHERE tracker_id = ? AND user_id = ?"
            );
            $stmt->execute([$tracker_id, $user_id]);
            
            return [
                'success' => true,
                'message' => 'Tracker deleted successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Set tracker as default
     */
    public function setDefault($tracker_id, $user_id) {
        try {
            // Verify ownership
            if (!$this->verifyOwnership($tracker_id, $user_id)) {
                throw new Exception("Unauthorized");
            }
            
            // Remove default from all user's trackers
            $stmt = $this->db->prepare("UPDATE trackers SET is_default = 0 WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Set new default
            $stmt = $this->db->prepare(
                "UPDATE trackers SET is_default = 1 WHERE tracker_id = ? AND user_id = ?"
            );
            $stmt->execute([$tracker_id, $user_id]);
            
            return [
                'success' => true,
                'message' => 'Default tracker updated'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get default tracker for user
     */
    public function getDefaultTracker($user_id) {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM trackers WHERE user_id = ? AND is_default = 1 AND is_active = 1 LIMIT 1"
            );
            $stmt->execute([$user_id]);
            $tracker = $stmt->fetch();
            
            // If no default, get first active tracker
            if (!$tracker) {
                $stmt = $this->db->prepare(
                    "SELECT * FROM trackers WHERE user_id = ? AND is_active = 1 ORDER BY created_at ASC LIMIT 1"
                );
                $stmt->execute([$user_id]);
                $tracker = $stmt->fetch();
            }
            
            if (!$tracker) {
                throw new Exception("No active trackers found");
            }
            
            return [
                'success' => true,
                'tracker' => $tracker
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify user owns the tracker
     */
    private function verifyOwnership($tracker_id, $user_id) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM trackers WHERE tracker_id = ? AND user_id = ?"
        );
        $stmt->execute([$tracker_id, $user_id]);
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Create default tracker for new user
     */
    public static function createDefaultForUser($user_id, $user_name) {
        try {
            $tracker = new self();
            return $tracker->create($user_id, [
                'tracker_name' => $user_name . "'s Budget",
                'tracker_type' => 'personal',
                'icon' => '💰',
                'color' => '#6366f1'
            ]);
        } catch (Exception $e) {
            error_log("Failed to create default tracker: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

