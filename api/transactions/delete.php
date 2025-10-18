<?php
/**
 * Delete Transaction API
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

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['transaction_id']) || empty($data['type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Transaction ID and type are required']);
    exit;
}

$transaction = new Transaction();

if ($data['type'] === 'income') {
    $result = $transaction->deleteIncome($data['transaction_id'], User::getCurrentUserId());
} else {
    $result = $transaction->deleteExpense($data['transaction_id'], User::getCurrentUserId());
}

http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);

