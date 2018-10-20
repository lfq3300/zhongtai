<?php
error_reporting(1);
$token = 'ZyDbXzyro6Likbww';
$json = json_decode(file_get_contents('php://input'), true);
if (empty($json['token']) || $json['token'] !== $token) {
    exit('error request');
}
echo exec('git pull origin develop');