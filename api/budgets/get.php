<?php
/**
 * Get Budgets API
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

if (!User::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$is_active = isset($_GET['is_active']) ? (bool)$_GET['is_active'] : true;

$budget = new Budget();
$result = $budget->getUserBudgets(User::getCurrentUserId(), User::getCurrentTrackerId(), $is_active);

echo json_encode($result);

