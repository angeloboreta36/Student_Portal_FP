<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helper.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();
set_flash('info', 'You have been logged out securely.');
header('Location: ' . base_url('login.php'));
exit;
