<?php
/**
 * Google OAuth Callback Handler
 * Handles the callback from Google after user authorization
 */

require_once '../../config/config.php';

// Check if Google credentials are configured
if (empty(GOOGLE_CLIENT_ID) || empty(GOOGLE_CLIENT_SECRET)) {
    header('Location: ../../index.html?error=Google OAuth not configured. Please contact administrator.');
    exit;
}

// Check for errors
if (isset($_GET['error'])) {
    $error_message = isset($_GET['error_description']) ? $_GET['error_description'] : 'Google authentication failed';
    header('Location: ../../index.html?error=' . urlencode($error_message));
    exit;
}

// Verify state token (CSRF protection)
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    header('Location: ../../index.html?error=Invalid state parameter. Please try again.');
    exit;
}

// Clear state
unset($_SESSION['oauth_state']);

// Check for authorization code
if (!isset($_GET['code'])) {
    header('Location: ../../index.html?error=No authorization code received');
    exit;
}

$authorization_code = $_GET['code'];

try {
    // Exchange authorization code for access token
    $token_url = 'https://oauth2.googleapis.com/token';
    $token_data = [
        'code' => $authorization_code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $token_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception('Failed to exchange authorization code for token');
    }
    
    $token_data = json_decode($token_response, true);
    
    if (!isset($token_data['access_token'])) {
        throw new Exception('No access token received');
    }
    
    $access_token = $token_data['access_token'];
    $id_token = $token_data['id_token'] ?? null;
    
    // Get user info from Google
    $userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
    
    $ch = curl_init($userinfo_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token
    ]);
    
    $userinfo_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception('Failed to get user info from Google');
    }
    
    $userinfo = json_decode($userinfo_response, true);
    
    if (!isset($userinfo['id']) || !isset($userinfo['email'])) {
        throw new Exception('Invalid user info received from Google');
    }
    
    // Extract user data
    $google_id = $userinfo['id'];
    $email = $userinfo['email'];
    $full_name = $userinfo['name'] ?? $userinfo['email'];
    $profile_picture = $userinfo['picture'] ?? null;
    
    // Login or create user
    $user = new User();
    $result = $user->loginWithGoogle($google_id, $email, $full_name, $profile_picture);
    
    if ($result['success']) {
        // Redirect to dashboard
        header('Location: ../../dashboard.html');
        exit;
    } else {
        // Login failed
        header('Location: ../../index.html?error=' . urlencode($result['message']));
        exit;
    }
    
} catch (Exception $e) {
    error_log('Google OAuth error: ' . $e->getMessage());
    header('Location: ../../index.html?error=Google authentication failed. Please try again.');
    exit;
}

