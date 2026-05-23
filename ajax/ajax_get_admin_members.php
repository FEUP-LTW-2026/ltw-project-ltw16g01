<?php
session_start();

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/members.php';
require_once '../template/members.php';

requireAuth('admin');

$pdo = getDatabaseConnection();

$filters = [
    'name' => $_GET['name'] ?? '',
    'plan' => $_GET['plan'] ?? ''
];

$members = getAllMembersAdmin($pdo, $filters);

output_members_admin_table($members);