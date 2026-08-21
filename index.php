<?php
/*
============================================================
CRUD-EMPLOYEE2
EMPLOYEE PAYMENT CRUD
LOGIN PROTECTED
============================================================
*/

date_default_timezone_set("Asia/Kolkata");

session_start();

/* =========================================================
   LOGIN PROTECTION
========================================================= */

if (
    !isset($_SESSION["app_user_id"]) ||
    $_SESSION["app_user_id"] === ""
) {
    header("Location: login.php");
    exit;
}


/* =========================================================
   DATABASE
========================================================= */

include("db.php");

$message = "";
