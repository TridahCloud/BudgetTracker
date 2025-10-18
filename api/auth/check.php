<?php
/**
 * Check Authentication Status
 */

// Prevent any output before JSON
ob_start();

require_once '../../config/config.php';

// Clear any buffered output
ob_end_clean();

header('Content-Type: application/json');

if (User::isLoggedIn()) {
    $response = [
        'success' => true,
        'logged_in' => true,
        'user' => [
            'user_id' => $_SESSION['user_id'],
            'email' => $_SESSION['email'],
            'full_name' => $_SESSION['full_name'],
            'account_type' => $_SESSION['account_type']
        ]
    ];
    
    // Include active tracker info if available
    if (isset($_SESSION['active_tracker_id'])) {
        $response['active_tracker'] = [
            'tracker_id' => $_SESSION['active_tracker_id'],
            'tracker_name' => $_SESSION['active_tracker_name'] ?? 'My Budget'
        ];
    }
    
    echo json_encode($response);
} else {
    echo json_encode([
        'success' => false,
        'logged_in' => false
    ]);
}

