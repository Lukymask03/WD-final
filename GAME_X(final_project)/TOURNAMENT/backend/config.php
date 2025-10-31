<?php
// ==========================================
// config.php
// Central configuration for GAME_X project
// ==========================================

//  Load database connection
require_once __DIR__ . '/db.php';

//  Load common functions
require_once __DIR__ . '/functions.php';

//  Load helpers
require_once __DIR__ . '/helpers/auth_guard.php';
require_once __DIR__ . '/helpers/log_activity.php';

//  Optionally load message handler (if used globally)
require_once __DIR__ . '/handle_message.php';

//  Optional: Set timezone and error mode
date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 1);
