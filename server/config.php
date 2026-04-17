<?php
/**
 * Konfigurasi Database (ASPEK: MySQL Implementation B62)
 * Sesuaikan dengan environment LKS Anda (XAMPP/WAMP default).
 */

require_once 'autoload.php';

define('DB_HOST', 'localhost');
define('DB_NAME', 'lks_game_db'); // Pastikan database ini sudah dibuat di phpMyAdmin
define('DB_USER', 'root');
define('DB_PASS', '');

// Config only - DB init happens in index.php after full autoload