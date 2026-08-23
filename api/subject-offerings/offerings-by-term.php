<?php
require_once __DIR__ . '/../../bootstrap.php';
use App\Controllers\SubjectOfferingController;
(new SubjectOfferingController())->offeringsByTerm();