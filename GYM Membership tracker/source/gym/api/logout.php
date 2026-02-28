<?php

require_once '../includes/db_config.php';

startSecureSession();
session_unset();
session_destroy();
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}
header('Location: ../index.php?logout=success');
exit;
?>
