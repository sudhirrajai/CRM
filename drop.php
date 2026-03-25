<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->exec('DROP DATABASE IF EXISTS crm_vmcore;');
    $pdo->exec('CREATE DATABASE crm_vmcore;');
    echo "Database crm_vmcore dropped and recreated successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
