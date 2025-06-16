<?php
session_start();
$_SESSION = array();
session_destroy();
if (isset($_COOKIE['loove_remember'])) {
    setcookie('loove_remember', '', time() - 3600, '/');
}
header("location: direct_login.php");
exit();
?>
