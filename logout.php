<?php
/*
============================================================
CRUD-EMPLOYEE2
LOGOUT
============================================================
*/

session_start();

/*
 * Remove all session variables.
 */
$_SESSION = [];

/*
 * Destroy the session.
 */
session_destroy();

/*
 * Return to login page.
 */
header("Location: login.php");
exit;
?>