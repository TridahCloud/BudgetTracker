<?php
/**
 * Get Expense Categories API
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    
    // Get all categories (system categories and user's custom categories)
    $user_id = User::getCurrentUserId();
    
    $sql = "SELECT category_id, category_name, icon, color, is_system 
            FROM expense_categories 
            WHERE user_id IS NULL OR user_id = ? 
            ORDER BY is_system DESC, category_name ASC";
    
    $params = [];
    if ($user_id) {
        $params[] = $user_id;
    } else {
        // If not logged in, only show system categories
        $sql = "SELECT category_id, category_name, icon, color, is_system 
                FROM expense_categories 
                WHERE user_id IS NULL 
                ORDER BY category_name ASC";
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $categories = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load categories'
    ]);
}

