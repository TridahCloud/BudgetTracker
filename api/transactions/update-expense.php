<?php
/**
 * Update Expense Transaction API
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

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['transaction_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Transaction ID is required']);
    exit;
}

$transaction = new Transaction();
$result = $transaction->updateExpense($data['transaction_id'], User::getCurrentUserId(), $data);

http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);

