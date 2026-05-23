<?php
session_start();
if (isset($_POST['role'])) {
    $_SESSION['active_role'] = $_POST['role'];
}