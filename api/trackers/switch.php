<?php
/**
 * Switch Active Tracker API
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

try {
    $tracker = new Tracker();
    $result = $tracker->getTracker($data['tracker_id'], User::getCurrentUserId());
    
    if ($result['success']) {
        // Update session with new active tracker
        $_SESSION['active_tracker_id'] = $result['tracker']['tracker_id'];
        $_SESSION['active_tracker_name'] = $result['tracker']['tracker_name'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Switched to ' . $result['tracker']['tracker_name'],
            'tracker' => $result['tracker']
        ]);
    } else {
        http_response_code(404);
        echo json_encode($result);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

