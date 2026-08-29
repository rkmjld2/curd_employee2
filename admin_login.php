<?php
/*
============================================================
 CURD-EMPLOYEE2
 ADMINISTRATOR LOGIN
============================================================

Purpose:
    Administrator-only login for User Management.

Authentication:
    Administrator is stored in app_users.

IMPORTANT:
    The administrator account may log in even when
    active = 0.

    This is intentional so that an inactive administrator
    can enter admin.php and activate the administrator
    account again.

Normal users:
    login.php

Administrator:
    admin_login.php

Database:
    employeer

Table:
    app_users

Administrator User ID:
    admin

Timezone:
    Asia/Kolkata

============================================================
*/

date_default_timezone_set("Asia/Kolkata");


/* =========================================================
   SESSION
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   DATABASE
========================================================= */

require_once __DIR__ . "/db.php";


/* =========================================================
   IF ADMIN ALREADY LOGGED IN
========================================================= */

if (
    isset($_SESSION["admin_logged_in"]) &&
    $_SESSION["admin_logged_in"] === true
) {

    header("Location: admin.php");

    exit;
}


/* =========================================================
   LOGIN ERROR
========================================================= */

$login_error = "";


/* =========================================================
   ADMIN LOGIN
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["admin_login"])
) {

    $password =
        $_POST["password"] ?? "";


    /* =====================================================
       BASIC VALIDATION
    ===================================================== */

    if ($password === "") {

        $login_error =
            "Please enter Administrator Password.";

    } else {


        /* =================================================
           FIND ADMINISTRATOR
        ================================================= */

        $stmt = $conn->prepare("
            SELECT
                id,
                user_id,
                user_name,
                password_hash,
                active
            FROM app_users
            WHERE user_id = 'admin'
            LIMIT 1
        ");


        if (!$stmt) {

            $login_error =
                "Administrator login preparation failed: "
                . $conn->error;

        } else {

            $stmt->execute();

            $result =
                $stmt->get_result();


            /* =============================================
               ADMIN NOT FOUND
            ============================================= */

            if (
                !$result ||
                $result->num_rows === 0
            ) {

                $login_error =
                    "Administrator account was not found.";

            } else {

                $admin =
                    $result->fetch_assoc();


                /* =========================================
                   PASSWORD CHECK
                ========================================= */

                if (
                    !password_verify(
                        $password,
                        $admin["password_hash"]
                    )
                ) {

                    $login_error =
                        "Invalid administrator password.";

                } else {


                    /*
                     * IMPORTANT:
                     *
                     * We deliberately DO NOT check:
                     *
                     *     active == 1
                     *
                     * here.
                     *
                     * This allows an inactive administrator
                     * to enter admin.php and activate the
                     * administrator account.
                     */


                    /* =====================================
                       REGENERATE SESSION
                    ===================================== */

                    session_regenerate_id(true);


                    /* =====================================
                       ADMIN SESSION
                    ===================================== */

                    $_SESSION["admin_logged_in"] =
                        true;

                    $_SESSION["admin_name"] =
                        $admin["user_name"];

                    $_SESSION["admin_user_id"] =
                        $admin["user_id"];

                    $_SESSION["admin_db_id"] =
                        $admin["id"];


                    /* =====================================
                       UPDATE LAST LOGIN
                    ===================================== */

                    $update =
                        $conn->prepare("
                            UPDATE app_users
                            SET last_login = ?
                            WHERE user_id = 'admin'
                        ");


                    if ($update) {

                        $login_time =
                            date("Y-m-d H:i:s");

                        $update->bind_param(
                            "s",
                            $login_time
                        );

                        $update->execute();

                        $update->close();
                    }


                    /* =====================================
                       GO TO ADMIN PANEL
                    ===================================== */

                    header(
                        "Location: admin.php"
                    );

                    exit;
                }
            }


            $stmt->close();
        }
    }
}

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
CURD-EMPLOYEE2 - Administrator Login
</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    padding: 20px;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f2f2f2;
}


.login-box {

    max-width: 420px;

    margin: 80px auto;

    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow:
        0 3px 15px
        rgba(0,0,0,0.15);

    text-align: center;
}


h1 {

    margin-top: 0;

    color: #1d3557;
}


.subtitle {

    color: #666;

    margin-bottom: 25px;
}


input[type="password"] {

    width: 100%;

    padding: 12px;

    font-size: 16px;

    border: 1px solid #aaa;

    border-radius: 6px;

    margin-bottom: 15px;
}


button {

    width: 100%;

    padding: 12px;

    border: none;

    border-radius: 6px;

    background: #6f42c1;

    color: white;

    font-size: 16px;

    cursor: pointer;
}


button:hover {

    opacity: 0.85;
}


.error {

    color: #842029;

    background: #f8d7da;

    border-radius: 6px;

    padding: 10px;

    margin-bottom: 15px;

    font-weight: bold;
}


.small {

    margin-top: 20px;

    color: #777;

    font-size: 13px;
}

</style>

</head>


<body>


<div class="login-box">


<h1>
CURD-EMPLOYEE2
</h1>


<div class="subtitle">
Administrator Login
</div>


<?php

if ($login_error !== "") {

?>

<div class="error">

<?php

echo htmlspecialchars(
    $login_error,
    ENT_QUOTES,
    "UTF-8"
);

?>

</div>

<?php

}

?>


<form
    method="POST"
    action="admin_login.php"
>


<input
    type="password"
    name="password"
    placeholder="Enter Administrator Password"
    autocomplete="current-password"
    required
    autofocus
>


<button
    type="submit"
    name="admin_login"
>
ADMIN LOGIN
</button>


</form>


<div class="small">
User Management Administration
</div>


</div>


</body>

</html>

<?php

mysqli_close($conn);

?>
