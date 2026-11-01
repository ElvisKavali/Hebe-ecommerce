<?php
require_once __DIR__ . '/../config/config.php';
logout_user();
flash('success', 'You have been logged out.');
redirect('/');
