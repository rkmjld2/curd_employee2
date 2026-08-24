<?php

/*
============================================================
 CURD-EMPLOYEE2
 EMPLOYEE PAYMENT CRUD
============================================================
*/

/* =========================================================
   COMMERCIAL LICENSE PROTECTION
========================================================= */

require_once __DIR__ . "/license_guard.php";


/* =========================================================
   TIMEZONE
========================================================= */

date_default_timezone_set("Asia/Kolkata");

error_reporting(E_ALL);
ini_set("display_errors", "1");

session_start();

require_once __DIR__ . "/db.php";
