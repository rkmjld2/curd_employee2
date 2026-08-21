<?php
/*
============================================================
 CURD-EMPLOYEE2
 USER LOGIN
============================================================

Database:
    app_users

Fields used:
    user_id
    user_name
    password_hash
    active
    start_time
    stop_time
    last_login

Timezone:
    Asia/Kolkata

============================================================
*/

date_default_timezone_set("Asia/Kolkata");

session_start();

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
   LOGIN MESSAGE
========================================================= */

$login_error = "";


/* =========================================================
   LOGIN PROCESS
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["login"])
) {

    $user_id =
        trim($_POST["user_id"] ?? "");

    $password =
        $_POST["password"] ?? "";


    /* =====================================================
       BASIC VALIDATION
    ===================================================== */

    if ($user_id === "") {

        $login_error =
            "Please enter User ID.";

    }
    elseif ($password === "") {

        $login_error =
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

            $login_error =
                "Login preparation failed.";

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

                $login_error =
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

                    $login_error =
                        "Invalid User ID or password.";

                }


                /* =========================================
                   ACTIVE CHECK
                ========================================= */

                elseif (
                    (int)$user["active"] !== 1
                ) {

                    $login_error =
                        "Your account is inactive.";

                }


                else {

                    /* =====================================
                       CURRENT TIME
                    ===================================== */

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
                        !empty($user["start_time"])
                    ) {

                        try {

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

                                $login_error =
                                    "Your account is not active yet.";

                            }

                        }
                        catch (Exception $e) {

                            $login_error =
                                "Invalid account start time.";

                        }
                    }


                    /* =====================================
                       STOP TIME CHECK
                    ===================================== */

                    if (
                        $login_error === "" &&
                        !empty($user["stop_time"])
                    ) {

                        try {

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

                                $login_error =
                                    "Your account access has expired.";

                            }

                        }
                        catch (Exception $e) {

                            $login_error =
                                "Invalid account stop time.";

                        }
                    }


                    /* =====================================
                       SUCCESSFUL LOGIN
                    ===================================== */

                    if (
                        $login_error === ""
                    ) {


                        /*
                         * Regenerate session ID
                         * after successful authentication.
                         */

                        session_regenerate_id(true);


                        /* =================================
                           STORE LOGIN SESSION
                        ================================= */

                        $_SESSION["app_user_id"] =
                            $user["user_id"];

                        $_SESSION["app_user_name"] =
                            $user["user_name"];


                        /* =================================
                           UPDATE LAST LOGIN
                        ================================= */

                        $update =
                            $conn->prepare("
                                UPDATE app_users
                                SET last_login = NOW()
                                WHERE user_id = ?
                            ");


                        if ($update) {

                            $update->bind_param(
                                "s",
                                $user["user_id"]
                            );

                            $update->execute();

                            $update->close();
                        }


                        /* =================================
                           GO TO EMPLOYEE PAGE
                        ================================= */

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

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>CURD-EMPLOYEE2 - Login</title>

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

    margin-bottom: 20px;
}


input {

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

    background: #007bff;

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
Employee Payment System
</div>


<?php

if (
    $login_error !== ""
) {

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
    action="login.php"
>


<input
    type="text"
    name="user_id"
    placeholder="Enter User ID"
    maxlength="50"
    autocomplete="username"
    required
    autofocus
>


<input
    type="password"
    name="password"
    placeholder="Enter Password"
    autocomplete="current-password"
    required
>


<button
    type="submit"
    name="login"
>
LOGIN
</button>


</form>


<div class="small">
Authorized User Login
</div>


</div>


</body>

</html>

<?php

mysqli_close($conn);

?>
