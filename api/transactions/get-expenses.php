<?php
/**
 * Get Expense Transactions API
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

// Get filters from query string
$filters = [];
if (isset($_GET['start_date'])) {
    $filters['start_date'] = $_GET['start_date'];
}
if (isset($_GET['end_date'])) {
    $filters['end_date'] = $_GET['end_date'];
}
if (isset($_GET['category_id'])) {
    $filters['category_id'] = $_GET['category_id'];
}
if (isset($_GET['limit'])) {
    $filters['limit'] = (int)$_GET['limit'];
}

$transaction = new Transaction();
$result = $transaction->getExpenses(User::getCurrentUserId(), User::getCurrentTrackerId(), $filters);

echo json_encode($result);

