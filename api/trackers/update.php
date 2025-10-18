<?php
/**
 * Update Tracker API
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

if (!$data || empty($data['tracker_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tracker ID is required']);
    exit;
}

$tracker = new Tracker();
$result = $tracker->update($data['tracker_id'], User::getCurrentUserId(), $data);

http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);

