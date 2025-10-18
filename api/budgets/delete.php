<?php
/**
 * Delete Budget API
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

if (!$data || empty($data['budget_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Budget ID is required']);
    exit;
}

$budget = new Budget();
$result = $budget->delete($data['budget_id'], User::getCurrentUserId());

http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);

