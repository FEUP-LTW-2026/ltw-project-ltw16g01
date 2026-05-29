<?php
/**
 * logout.php – Destroys the user session and redirects to the homepage.
 */

require_once '../includes/session.php';
session_destroy();
header('Location: ../index.php');
exit;