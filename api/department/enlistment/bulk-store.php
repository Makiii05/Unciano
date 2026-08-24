<?php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Controllers\EnlistmentController;
(new EnlistmentController())->bulkStoreJson();
