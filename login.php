<?php
/*
============================================================
 CURD-EMPLOYEE2
 USER LOGIN
============================================================

Database:
    app_users

Login checks:

    1. User ID exists
    2. Password is correct
    3. User is active
    4. Start time has been reached
    5. Stop time has not expired

Timezone:
    Asia/Kolkata

Session variables:

    $_SESSION["app_user_id"]
    $_SESSION["app_user_name"]

After successful login:
    index.php
============================================================
*/


/* =========================================================
   TIMEZONE
========================================================= */

date_default_timezone_set("Asia/Kolkata");


/* =========================================================
   SESSION
========================================================= */

session_start();


/* =========================================================
   DATABASE
========================================================= */

require_once __DIR__ . "/db.php";


/* =========================================================
   IF ALREADY LOGGED IN
========================================================= */

if (
    isset($_SESSION["app_user_id"]) &&
    $_SESSION["app_user_id"] !== ""
) {

    header("Location: index.php");

    exit;
}


/* =========================================================
   VARIABLES
========================================================= */

$error = "";

$user_id = "";


/* =========================================================
   LOGIN PROCESS
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["login"])
) {

    $user_id =
        trim(
            $_POST["user_id"] ?? ""
        );

    $password =
        $_POST["password"] ?? "";


    /* =====================================================
       BASIC VALIDATION
    ===================================================== */

    if ($user_id === "") {

        $error =
            "Please enter User ID.";

    }
    elseif ($password === "") {

        $error =
            "Please enter password.";

    }
    else {


        /* =================================================
           FIND USER
        ================================================= */

        $stmt = $conn->prepare("
            SELECT
                id,
                user_id,
                user_name,
                password_hash,
                active,
                start_time,
                stop_time
            FROM app_users
            WHERE user_id = ?
            LIMIT 1
        ");


        if (!$stmt) {

            $error =
                "Database preparation failed.";

        }
        else {

            $stmt->bind_param(
                "s",
                $user_id
            );


            $stmt->execute();


            $result =
                $stmt->get_result();


            /* =============================================
               USER NOT FOUND
            ============================================= */

            if (
                !$result ||
                $result->num_rows === 0
            ) {

                $error =
                    "Invalid User ID or password.";

            }
            else {

                $user =
                    $result->fetch_assoc();


                /* =========================================
                   PASSWORD CHECK
                ========================================= */

                if (
                    !password_verify(
                        $password,
                        $user["password_hash"]
                    )
                ) {

                    $error =
                        "Invalid User ID or password.";

                }


                /* =========================================
                   ACTIVE CHECK
                ========================================= */

                elseif (
                    (int)$user["active"] !== 1
                ) {

                    $error =
                        "Your account is inactive. Please contact the administrator.";

                }


                /* =========================================
                   CURRENT TIME
                ========================================= */

                else {

                    $now =
                        new DateTime(
                            "now",
                            new DateTimeZone(
                                "Asia/Kolkata"
                            )
                        );


                    /* =====================================
                       START TIME CHECK
                    ===================================== */

                    if (
                        !empty(
                            $user["start_time"]
                        )
                    ) {

                        try {

                            $start_time =
                                new DateTime(
                                    $user["start_time"],
                                    new DateTimeZone(
                                        "Asia/Kolkata"
                                    )
                                );


                            if (
                                $now < $start_time
                            ) {

                                $error =
                                    "Your account has not started yet.";

                            }

                        }
                        catch (
                            Exception $e
                        ) {

                            $error =
                                "Invalid account start time.";
                        }
                    }


                    /* =====================================
                       STOP TIME CHECK
                    ===================================== */

                    if (
                        $error === "" &&
                        !empty(
                            $user["stop_time"]
                        )
                    ) {

                        try {

                            $stop_time =
                                new DateTime(
                                    $user["stop_time"],
                                    new DateTimeZone(
                                        "Asia/Kolkata"
                                    )
                                );


                            if (
                                $now >= $stop_time
                            ) {

                                $error =
                                    "Your account access has expired.";

                            }

                        }
                        catch (
                            Exception $e
                        ) {

                            $error =
                                "Invalid account stop time.";
                        }
                    }


                    /* =====================================
                       SUCCESSFUL LOGIN
                    ===================================== */

                    if ($error === "") {


                        /*
                         * Regenerate session ID
                         * for security.
                         */

                        session_regenerate_id(
                            true
                        );


                        /*
                         * Store logged-in user.
                         */

                        $_SESSION[
                            "app_user_id"
                        ] =
                            $user["user_id"];


                        $_SESSION[
                            "app_user_name"
                        ] =
                            $user["user_name"];


                        /*
                         * Store login time.
                         */

                        $_SESSION[
                            "app_login_time"
                        ] =
                            date(
                                "Y-m-d H:i:s"
                            );


                        /* =================================
                           UPDATE LAST LOGIN
                        ================================= */

                        $update =
                            $conn->prepare("
                                UPDATE app_users
                                SET last_login = ?
                                WHERE user_id = ?
                            ");


                        if ($update) {

                            $login_time =
                                date(
                                    "Y-m-d H:i:s"
                                );


                            $update->bind_param(
                                "ss",
                                $login_time,
                                $user["user_id"]
                            );


                            $update->execute();

                            $update->close();
                        }


                        /*
                         * GO TO EMPLOYEE APPLICATION
                         */

                        header(
                            "Location: index.php"
                        );

                        exit;
                    }
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
CURD-EMPLOYEE2 - User Login
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


.error {

    background: #f8d7da;

    color: #842029;

    border: 1px solid #f5c2c7;

    padding: 10px;

    border-radius: 6px;

    margin-bottom: 15px;

    font-weight: bold;
}


.form-group {

    text-align: left;

    margin-bottom: 15px;
}


label {

    display: block;

    font-weight: bold;

    margin-bottom: 6px;
}


input {

    width: 100%;

    padding: 12px;

    font-size: 16px;

    border: 1px solid #aaa;

    border-radius: 6px;
}


input:focus {

    outline: none;

    border-color: #0d6efd;
}


.login-button {

    width: 100%;

    padding: 12px;

    border: none;

    border-radius: 6px;

    background: #0d6efd;

    color: white;

    font-size: 16px;

    cursor: pointer;

    margin-top: 5px;
}


.login-button:hover {

    opacity: 0.85;
}


.small {

    margin-top: 20px;

    color: #777;

    font-size: 13px;

    line-height: 1.5;
}

</style>

</head>


<body>


<div class="login-box">


<h1>
CURD-EMPLOYEE2
</h1>


<div class="subtitle">
Employee Payment System
</div>


<?php

if ($error !== "") {

?>

<div class="error">

<?php

echo htmlspecialchars(
    $error,
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
    action="login.php"
>


<div class="form-group">

<label for="user_id">
User ID
</label>

<input
    type="text"
    id="user_id"
    name="user_id"
    maxlength="50"
    value="<?php

        echo htmlspecialchars(
            $user_id,
            ENT_QUOTES,
            "UTF-8"
        );

    ?>"
    required
    autofocus
>

</div>


<div class="form-group">

<label for="password">
Password
</label>

<input
    type="password"
    id="password"
    name="password"
    required
>

</div>


<button
    type="submit"
    name="login"
    class="login-button"
>
LOGIN
</button>


</form>


<div class="small">

Your account access is controlled by the administrator.

<br>

Start and Stop times are in
<strong>Asia/Kolkata (IST)</strong>.

</div>


</div>


</body>

</html>

<?php

mysqli_close($conn);

?>
