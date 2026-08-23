<?php
require_once __DIR__ . '/../../../bootstrap.php';

// Legacy URL – redirect to new accounts location for Apache compatibility
redirect(url('views/admin/accounts/index.php'));
