<?php
/**
 * Get Trackers API
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

$tracker = new Tracker();
$result = $tracker->getUserTrackers(User::getCurrentUserId());

echo json_encode($result);

