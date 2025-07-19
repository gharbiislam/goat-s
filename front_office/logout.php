<?php
session_start();

// Clear session variables
session_unset();
session_destroy();

// Delete cookies
setcookie('user_id', '', time() - 3600, "/");
setcookie('user_name', '', time() - 3600, "/");
setcookie('stay_connected', '', time() - 3600, '/', '', true, true);

// Redirect to login page
header("Location: index.php");
exit();
?>