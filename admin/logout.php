<?php

require_once __DIR__ . '/../app/bootstrap.php';

logout_admin();
flash('You have been logged out.');
redirect('/Handout%20Payment%20System/admin/login.php');
