<?php
/**
 * Get Income Sources API
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

if (!User::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$incomeSource = new IncomeSource();
$result = $incomeSource->getUserSources(User::getCurrentUserId(), User::getCurrentTrackerId());

echo json_encode($result);

