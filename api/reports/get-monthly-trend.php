<?php
/**
 * Get Monthly Trend Data API
 * Returns income and expense data for the last 12 months
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
    $months = isset($_GET['months']) ? (int)$_GET['months'] : 12;
    
    $db = Database::getInstance()->getConnection();
    
    // Get monthly income data
    $stmt = $db->prepare("
        SELECT 
            DATE_FORMAT(transaction_date, '%Y-%m') as month,
            SUM(amount) as total
        FROM income_transactions
        WHERE user_id = ? AND tracker_id = ?
            AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute([$user_id, $tracker_id, $months]);
    $income_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Get monthly expense data
    $stmt = $db->prepare("
        SELECT 
            DATE_FORMAT(transaction_date, '%Y-%m') as month,
            SUM(amount) as total
        FROM expense_transactions
        WHERE user_id = ? AND tracker_id = ?
            AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute([$user_id, $tracker_id, $months]);
    $expense_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Generate all months for the period
    $all_months = [];
    for ($i = $months - 1; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $all_months[] = [
            'month' => $month,
            'month_name' => date('M Y', strtotime($month . '-01')),
            'income' => isset($income_data[$month]) ? floatval($income_data[$month]) : 0,
            'expenses' => isset($expense_data[$month]) ? floatval($expense_data[$month]) : 0
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $all_months
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load trend data'
    ]);
}

