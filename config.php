<?php
/*
 * ============================================================
 * CURD_EMPLOYEE2 - config.php
 * ============================================================
 *
 * Central configuration file
 *
 * Database:
 *   TiDB Cloud
 *
 * Database name:
 *   employeer
 *
 * Tables:
 *   employee
 *   app_users
 *
 * Timezone:
 *   Asia/Kolkata
 *
 * IMPORTANT:
 * Database credentials are read from environment variables.
 * Do NOT put the database password directly in this file.
 * ============================================================
 */

// ------------------------------------------------------------
// TIMEZONE
// ------------------------------------------------------------
date_default_timezone_set("Asia/Kolkata");


// ------------------------------------------------------------
// DATABASE ENVIRONMENT VARIABLES
// ------------------------------------------------------------
$DB_HOST = getenv("DB_HOST");
$DB_USER = getenv("DB_USER");
$DB_PASSWORD = getenv("DB_PASSWORD");
$DB_NAME = getenv("DB_NAME");
$DB_PORT = getenv("DB_PORT");


// ------------------------------------------------------------
// CHECK REQUIRED DATABASE SETTINGS
// ------------------------------------------------------------
if (
    $DB_HOST === false ||
    $DB_USER === false ||
    $DB_PASSWORD === false ||
    $DB_NAME === false
) {
    die("Database environment variables are not configured.");
}


// ------------------------------------------------------------
// DEFAULT PORT
// ------------------------------------------------------------
if ($DB_PORT === false || $DB_PORT === "") {
    $DB_PORT = 4000;
}


// ------------------------------------------------------------
// DATABASE CONNECTION
// ------------------------------------------------------------
$conn = mysqli_init();

if (!$conn) {
    die("Database initialization failed.");
}


// ------------------------------------------------------------
// SSL FOR TIDB CLOUD
// ------------------------------------------------------------
mysqli_ssl_set(
    $conn,
    null,
    null,
    null,
    null,
    null
);


// ------------------------------------------------------------
// CONNECT TO DATABASE
// ------------------------------------------------------------
if (
    !mysqli_real_connect(
        $conn,
        $DB_HOST,
        $DB_USER,
        $DB_PASSWORD,
        $DB_NAME,
        (int)$DB_PORT
    )
) {
    die("Database connection failed: " . mysqli_connect_error());
}


// ------------------------------------------------------------
// CHARACTER SET
// ------------------------------------------------------------
if (!mysqli_set_charset($conn, "utf8mb4")) {
    die("Failed to set database character set.");
}


// ------------------------------------------------------------
// APPLICATION SETTINGS
// ------------------------------------------------------------

$APP_NAME = "CURD Employee 2";

$APP_TIMEZONE = "Asia/Kolkata";


// ------------------------------------------------------------
// COMMERCIAL LICENSE SETTINGS
// ------------------------------------------------------------

// Customer/license identification number.
// This value must exist in the remote license database.
$LICENSE_USER_ID = "USER001";


// ------------------------------------------------------------
// SESSION SETTINGS
// ------------------------------------------------------------

// Start session only if it has not already been started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
