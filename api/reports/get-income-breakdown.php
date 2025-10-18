<?php
/**
 * Get Income Breakdown API
 * Returns income grouped by source for a date range
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
    
    // Get income by source
    $stmt = $db->prepare("
        SELECT 
            isc.source_name,
            isc.source_type,
            COUNT(it.transaction_id) as transaction_count,
            SUM(it.amount) as total,
            AVG(it.amount) as average
        FROM income_transactions it
        LEFT JOIN income_sources isc ON it.source_id = isc.source_id
        WHERE it.user_id = ? AND it.tracker_id = ?
            AND it.transaction_date BETWEEN ? AND ?
        GROUP BY it.source_id, isc.source_name, isc.source_type
        ORDER BY total DESC
    ");
    $stmt->execute([$user_id, $tracker_id, $start_date, $end_date]);
    $sources = $stmt->fetchAll();
    
    // Calculate total and percentages
    $total = array_sum(array_column($sources, 'total'));
    
    foreach ($sources as &$source) {
        $source['percentage'] = $total > 0 ? ($source['total'] / $total) * 100 : 0;
        $source['total'] = floatval($source['total']);
        $source['average'] = floatval($source['average']);
        $source['source_name'] = $source['source_name'] ?? 'Uncategorized';
    }
    
    echo json_encode([
        'success' => true,
        'sources' => $sources,
        'total' => $total
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load income breakdown'
    ]);
}

