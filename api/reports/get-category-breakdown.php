<?php
/**
 * Get Category Breakdown API
 * Returns expenses grouped by category for a date range
 */

ob_start();
require_once '../../config/config.php';
ob_end_clean();

header('Content-Type: application/json');

if (!User::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $user_id = User::getCurrentUserId();
    $tracker_id = User::getCurrentTrackerId();
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date = $_GET['end_date'] ?? date('Y-m-t');
    
    $db = Database::getInstance()->getConnection();
    
    // Get expenses by category
    $stmt = $db->prepare("
        SELECT 
            ec.category_name,
            ec.icon,
            ec.color,
            COUNT(et.transaction_id) as transaction_count,
            SUM(et.amount) as total,
            AVG(et.amount) as average
        FROM expense_transactions et
        LEFT JOIN expense_categories ec ON et.category_id = ec.category_id
        WHERE et.user_id = ? AND et.tracker_id = ?
            AND et.transaction_date BETWEEN ? AND ?
        GROUP BY et.category_id, ec.category_name, ec.icon, ec.color
        ORDER BY total DESC
    ");
    $stmt->execute([$user_id, $tracker_id, $start_date, $end_date]);
    $categories = $stmt->fetchAll();
    
    // Calculate total and percentages
    $total = array_sum(array_column($categories, 'total'));
    
    foreach ($categories as &$category) {
        $category['percentage'] = $total > 0 ? ($category['total'] / $total) * 100 : 0;
        $category['total'] = floatval($category['total']);
        $category['average'] = floatval($category['average']);
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'total' => $total
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load category breakdown'
    ]);
}

