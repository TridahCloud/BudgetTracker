<?php
/**
 * Get Financial Summary API
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

if (!User::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-t'); // Last day of current month

$transaction = new Transaction();
$result = $transaction->getSummary(User::getCurrentUserId(), User::getCurrentTrackerId(), $start_date, $end_date);

echo json_encode($result);

