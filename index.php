<?php
/*
============================================================
 CURD-EMPLOYEE2
 EMPLOYEE PAYMENT CRUD
 USER LOGIN + START / STOP TIME CONTROL
============================================================
*/

date_default_timezone_set("Asia/Kolkata");

session_start();

require_once __DIR__ . "/db.php";


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
   CURRENT LOGGED-IN USER
========================================================= */

$current_user_id =
    $_SESSION["app_user_id"];

$current_user_name =
    $_SESSION["app_user_name"] ?? "";


/* =========================================================
   CHECK USER ACTIVE + START / STOP TIME
========================================================= */

$stmt = $conn->prepare("
    SELECT
        user_id,
        user_name,
        active,
        start_time,
        stop_time
    FROM app_users
    WHERE user_id = ?
    LIMIT 1
");


if (!$stmt) {

    $_SESSION = [];

    session_destroy();

    header("Location: login.php");

    exit;
}


$stmt->bind_param(
    "s",
    $current_user_id
);

$stmt->execute();

$result =
    $stmt->get_result();


if (
    !$result ||
    $result->num_rows === 0
) {

    $stmt->close();

    $_SESSION = [];

    session_destroy();

    header("Location: login.php");

    exit;
}


$user =
    $result->fetch_assoc();

$stmt->close();


/* =========================================================
   CURRENT INDIA TIME
========================================================= */

$now =
    new DateTime(
        "now",
        new DateTimeZone(
            "Asia/Kolkata"
        )
    );


/* =========================================================
   ACTIVE CHECK
========================================================= */

if (
    (int)$user["active"] !== 1
) {

    $_SESSION = [];

    session_destroy();

    header("Location: login.php");

    exit;
}


/* =========================================================
   START TIME CHECK
========================================================= */

if (
    !empty($user["start_time"])
) {

    $start =
        new DateTime(
            $user["start_time"],
            new DateTimeZone(
                "Asia/Kolkata"
            )
        );


    if (
        $now < $start
    ) {

        $_SESSION = [];

        session_destroy();

        header("Location: login.php");

        exit;
    }
}


/* =========================================================
   STOP TIME CHECK
========================================================= */

if (
    !empty($user["stop_time"])
) {

    $stop =
        new DateTime(
            $user["stop_time"],
            new DateTimeZone(
                "Asia/Kolkata"
            )
        );


    if (
        $now > $stop
    ) {

        $_SESSION = [];

        session_destroy();

        header("Location: login.php");

        exit;
    }
}


/* =========================================================
   LOGOUT
========================================================= */

if (isset($_GET["logout"])) {

    $_SESSION = [];

    session_destroy();

    header("Location: login.php");

    exit;
}
