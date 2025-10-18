<?php
/**
 * User Class
 * Handles user authentication and management
 */

class User {
    private $db;
    private $user_id;
    private $email;
    private $full_name;
    private $account_type;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Register a new user with email and password
     */
    public function register($email, $password, $full_name, $account_type = 'personal') {
        try {
            // Validate input
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }
            
            if (strlen($password) < 8) {
                throw new Exception("Password must be at least 8 characters");
            }
            
            // Check if user already exists
            $stmt = $this->db->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception("Email already registered");
            }
            
            // Hash password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert user
            $stmt = $this->db->prepare(
                "INSERT INTO users (email, password_hash, full_name, account_type) 
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$email, $password_hash, $full_name, $account_type]);
            
            $user_id = $this->db->lastInsertId();
            
            // Create default expense categories for user
            $this->createDefaultUserCategories($user_id);
            
            // Create default tracker for user
            Tracker::createDefaultForUser($user_id, $full_name);
            
            return [
                'success' => true,
                'user_id' => $user_id,
                'message' => 'Registration successful'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Login with email and password
     */
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare(
                "SELECT user_id, email, password_hash, full_name, account_type, is_active 
                 FROM users WHERE email = ?"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception("Invalid email or password");
            }
            
            if (!$user['is_active']) {
                throw new Exception("Account is deactivated");
            }
            
            if (!password_verify($password, $user['password_hash'])) {
                throw new Exception("Invalid email or password");
            }
            
            // Update last login
            $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);
            
            // Set session
            $this->setSession($user);
            
            // Set default tracker in session
            $tracker = new Tracker();
            $defaultTracker = $tracker->getDefaultTracker($user['user_id']);
            if ($defaultTracker['success']) {
                $_SESSION['active_tracker_id'] = $defaultTracker['tracker']['tracker_id'];
                $_SESSION['active_tracker_name'] = $defaultTracker['tracker']['tracker_name'];
            }
            
            return [
                'success' => true,
                'user' => [
                    'user_id' => $user['user_id'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'account_type' => $user['account_type']
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Login with Google OAuth
     */
    public function loginWithGoogle($google_id, $email, $full_name, $profile_picture = null) {
        try {
            // Check if user exists with this Google ID
            $stmt = $this->db->prepare(
                "SELECT user_id, email, full_name, account_type, is_active 
                 FROM users WHERE google_id = ?"
            );
            $stmt->execute([$google_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                // Check if email exists
                $stmt = $this->db->prepare("SELECT user_id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    throw new Exception("Email already registered. Please use password login.");
                }
                
                // Create new user
                $stmt = $this->db->prepare(
                    "INSERT INTO users (email, google_id, full_name, profile_picture, account_type) 
                     VALUES (?, ?, ?, ?, 'personal')"
                );
                $stmt->execute([$email, $google_id, $full_name, $profile_picture]);
                $user_id = $this->db->lastInsertId();
                
                // Create default categories
                $this->createDefaultUserCategories($user_id);
                
                // Create default tracker
                Tracker::createDefaultForUser($user_id, $full_name);
                
                $user = [
                    'user_id' => $user_id,
                    'email' => $email,
                    'full_name' => $full_name,
                    'account_type' => 'personal'
                ];
            }
            
            if (!$user['is_active']) {
                throw new Exception("Account is deactivated");
            }
            
            // Update last login
            $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);
            
            // Set session
            $this->setSession($user);
            
            // Set default tracker in session
            $tracker = new Tracker();
            $defaultTracker = $tracker->getDefaultTracker($user['user_id']);
            if ($defaultTracker['success']) {
                $_SESSION['active_tracker_id'] = $defaultTracker['tracker']['tracker_id'];
                $_SESSION['active_tracker_name'] = $defaultTracker['tracker']['tracker_name'];
            }
            
            return [
                'success' => true,
                'user' => [
                    'user_id' => $user['user_id'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'account_type' => $user['account_type']
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Set user session
     */
    private function setSession($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['account_type'] = $user['account_type'];
        $_SESSION['logged_in'] = true;
    }
    
    /**
     * Get current active tracker ID
     */
    public static function getCurrentTrackerId() {
        return $_SESSION['active_tracker_id'] ?? null;
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Get current user ID
     */
    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        return ['success' => true];
    }
    
    /**
     * Create default expense categories for new user
     */
    private function createDefaultUserCategories($user_id) {
        // Users inherit system categories, no need to create duplicates
        // This method is here for future custom category initialization
        return true;
    }
    
    /**
     * Get user profile
     */
    public function getProfile($user_id) {
        try {
            $stmt = $this->db->prepare(
                "SELECT user_id, email, full_name, profile_picture, account_type, created_at 
                 FROM users WHERE user_id = ?"
            );
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception("User not found");
            }
            
            return [
                'success' => true,
                'user' => $user
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($user_id, $data) {
        try {
            $allowed_fields = ['full_name', 'account_type', 'profile_picture'];
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
            
            $values[] = $user_id;
            $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE user_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

