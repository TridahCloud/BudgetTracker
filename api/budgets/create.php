<?php
/**
 * Create Budget API
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

if (!User::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['budget_name']) || empty($data['amount']) || empty($data['start_date'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Budget name, amount, and start date are required']);
    exit;
}

$budget = new Budget();
$result = $budget->create(User::getCurrentUserId(), User::getCurrentTrackerId(), $data);

http_response_code($result['success'] ? 201 : 400);
echo json_encode($result);

