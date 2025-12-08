<?php
require_once __DIR__ . '/../config/config.php';

startSession();

// Destroy session
$_SESSION = [];
session_destroy();

// Redirect to login
redirect('/admin-uk0i3/login.php');
