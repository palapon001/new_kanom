<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../function.php';

$phone = preg_replace('/[^0-9]/', '', $_GET['phone'] ?? '');
$response = ['role' => 'user'];

if (strlen($phone) === 10) {
    $user = selectOne("SELECT role FROM users WHERE phone = ?", [$phone]);
    if ($user) {
        $response['role'] = $user['role'];
    }
}
echo json_encode($response);