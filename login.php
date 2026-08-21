<?php
/*
============================================================
CRUD-EMPLOYEE2
LOGIN PAGE
============================================================

Database table:
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

$login_error = "";


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
   LOGIN
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $user_id =
        trim($_POST["user_id"] ?? "");

    $password =
        $_POST["password"] ?? "";


    /* -----------------------------------------------------
       BASIC VALIDATION
    ----------------------------------------------------- */

    if ($user_id === "") {

        $login_error =
            "Please enter User ID.";

    }
    elseif ($password === "") {

        $login_error =
            "Please enter password.";

    }
    else {


        /* -------------------------------------------------
           FIND USER
        ------------------------------------------------- */

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
                "Login system error.";

        }
        else {

            $stmt->bind_param(
                "s",
                $user_id
            );

            $stmt->execute();

            $result =
                $stmt->get_result();


            /* ---------------------------------------------
               USER NOT FOUND
            --------------------------------------------- */

            if ($result->num_rows === 0) {

                $login_error =
                    "Invalid User ID or password.";

            }
            else {

                $user =
                    $result->fetch_assoc();


                /* -----------------------------------------
                   CHECK PASSWORD
                ----------------------------------------- */

                if (
                    !password_verify(
                        $password,
                        $user["password_hash"]
                    )
                ) {

                    $login_error =
                        "Invalid User ID or password.";

                }

                /* -----------------------------------------
                   CHECK ACTIVE
                ----------------------------------------- */

                elseif (
                    (int)$user["active"] !== 1
                ) {

                    $login_error =
                        "This user account is inactive.";

                }

                else {

                    /* -------------------------------------
                       CURRENT TIME
                    ------------------------------------- */

                    $now =
                        new DateTime(
                            "now",
                            new DateTimeZone(
                                "Asia/Kolkata"
                            )
                        );


                    /* -------------------------------------
                       CHECK START TIME
                    ------------------------------------- */

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

                        if ($now < $start) {

                            $login_error =
                                "User access has not started yet.";

                        }
                    }


                    /* -------------------------------------
                       CHECK STOP TIME
                    ------------------------------------- */

                    if (
                        $login_error === "" &&
                        !empty($user["stop_time"])
                    ) {

                        $stop =
                            new DateTime(
                                $user["stop_time"],
                                new DateTimeZone(
                                    "Asia/Kolkata"
                                )
                            );

                        if ($now > $stop) {

                            $login_error =
                                "User access has expired.";

                        }
                    }


                    /* -------------------------------------
                       LOGIN SUCCESS
                    ------------------------------------- */

                    if ($login_error === "") {


                        /*
                         * Regenerate session ID
                         * for security.
                         */

                        session_regenerate_id(true);


                        /*
                         * Store logged-in user
                         * information in session.
                         */

                        $_SESSION["app_user_id"] =
                            $user["user_id"];

                        $_SESSION["app_user_name"] =
                            $user["user_name"];

                        $_SESSION["app_user_db_id"] =
                            (int)$user["id"];


                        /*
                         * Update last_login
                         */

                        $update =
                            $conn->prepare("
                                UPDATE app_users
                                SET last_login = NOW()
                                WHERE id = ?
                            ");

                        if ($update) {

                            $db_id =
                                (int)$user["id"];

                            $update->bind_param(
                                "i",
                                $db_id
                            );

                            $update->execute();

                            $update->close();
                        }


                        /*
                         * Go to employee application
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

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>CRUD Employee 2 - Login</title>


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


.login-container {

    max-width: 430px;

    margin: 80px auto;

    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow:
        0 3px 15px
        rgba(0,0,0,0.15);
}


h1 {

    text-align: center;

    margin-top: 0;

    color: #1d3557;
}


.subtitle {

    text-align: center;

    color: #666;

    margin-bottom: 25px;
}


.form-group {

    margin-bottom: 18px;
}


label {

    display: block;

    font-weight: bold;

    margin-bottom: 7px;
}


input {

    width: 100%;

    padding: 12px;

    border: 1px solid #aaa;

    border-radius: 6px;

    font-size: 16px;
}


input:focus {

    outline: none;

    border-color: #007bff;

    box-shadow:
        0 0 4px
        rgba(0,123,255,0.25);
}


.login-button {

    width: 100%;

    padding: 12px;

    border: none;

    border-radius: 6px;

    background: #007bff;

    color: white;

    font-size: 16px;

    cursor: pointer;
}


.login-button:hover {

    opacity: 0.85;
}


.error {

    background: #f8d7da;

    color: #842029;

    padding: 12px;

    border-radius: 6px;

    margin-bottom: 20px;

    text-align: center;

    font-weight: bold;
}


.footer {

    text-align: center;

    margin-top: 20px;

    color: #777;

    font-size: 13px;
}

</style>

</head>


<body>


<div class="login-container">


<h1>
CRUD Employee 2
</h1>


<div class="subtitle">
User Login
</div>


<?php

if ($login_error !== "") {

    echo
        '<div class="error">' .
        htmlspecialchars(
            $login_error,
            ENT_QUOTES,
            "UTF-8"
        ) .
        '</div>';
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
    autocomplete="username"
    required
    autofocus
    value="<?php
        echo htmlspecialchars(
            $user_id ?? "",
            ENT_QUOTES,
            "UTF-8"
        );
    ?>"
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
    autocomplete="current-password"
    required
>

</div>


<button
    type="submit"
    class="login-button"
>
LOGIN
</button>


</form>


<div class="footer">
Employee Payment Management System
</div>


</div>


</body>

</html>

<?php

mysqli_close($conn);

?>