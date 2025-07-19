<?php
session_start();

// Destroy the session
setcookie('stay_connected', '', time() - 3600, '/', '', true, true);
session_unset();
session_destroy();

// Remove the "stay connected" cookie if it exists
if (isset($_COOKIE['admin_logged_in'])) {
    setcookie("admin_logged_in", "", time() - 3600, "/"); // Set the cookie to expire in the past
}

// Redirect to the login page after logout
header("Location: index.php");
exit();
?>
