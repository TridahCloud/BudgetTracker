<?php
/**
 * User Logout API
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

$user = new User();
$result = $user->logout();

http_response_code(200);
echo json_encode($result);

