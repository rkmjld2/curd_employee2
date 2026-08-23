<?php

/*
===========================================================
 COMMERCIAL LICENSE GUARD

 CURD_EMPLOYEE2

 Purpose:
     Protect individual application modules.

 This file:
     1. Loads the application's config.php
     2. Gets the customer's LICENSE_USER_ID
     3. Contacts the remote commercial license server
     4. Checks whether the application is authorized
     5. Stops the application if the license is OFF

 IMPORTANT:
     This file does NOT connect directly to TiDB Cloud.

     It communicates only with:
     license-commercial2-remote.onrender.com

     The license database remains on the remote server.
===========================================================
*/


/* ---------------------------------------------------------
   LOAD APPLICATION CONFIGURATION
--------------------------------------------------------- */

require_once __DIR__ . "/config.php";


/* ---------------------------------------------------------
   CHECK CUSTOMER LICENSE ID
--------------------------------------------------------- */

if (
    !isset($LICENSE_USER_ID) ||
    trim($LICENSE_USER_ID) === ""
) {

    http_response_code(500);

    die("License USER_ID is not configured.");
}


$user_id =
    trim($LICENSE_USER_ID);


/* ---------------------------------------------------------
   REMOTE LICENSE SERVER
--------------------------------------------------------- */

$license_url =
    "https://license-commercial2-remote.onrender.com/license_check.php";


/* ---------------------------------------------------------
   CHECK LICENSE
--------------------------------------------------------- */

function guard_check_license(
    $user_id,
    $license_url
) {

    $post_data =
        http_build_query([
            "user_id" => $user_id
        ]);


    $ch =
        curl_init($license_url);


    curl_setopt(
        $ch,
        CURLOPT_POST,
        true
    );


    curl_setopt(
        $ch,
        CURLOPT_POSTFIELDS,
        $post_data
    );


    curl_setopt(
        $ch,
        CURLOPT_RETURNTRANSFER,
        true
    );


    curl_setopt(
        $ch,
        CURLOPT_CONNECTTIMEOUT,
        10
    );


    curl_setopt(
        $ch,
        CURLOPT_TIMEOUT,
        20
    );


    curl_setopt(
        $ch,
        CURLOPT_FOLLOWLOCATION,
        true
    );


    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        [
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: application/json"
        ]
    );


    $response =
        curl_exec($ch);


    /* -----------------------------------------------------
       CURL ERROR
    ----------------------------------------------------- */

    if ($response === false) {

        $error =
            curl_error($ch);

        curl_close($ch);


        return [

            "success" => false,

            "status" => "OFF",

            "message" =>
                "License server could not be contacted: "
                . $error
        ];
    }


    /* -----------------------------------------------------
       HTTP STATUS
    ----------------------------------------------------- */

    $http_code =
        curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );


    curl_close($ch);


    if (
        $http_code < 200 ||
        $http_code >= 300
    ) {

        return [

            "success" => false,

            "status" => "OFF",

            "message" =>
                "License checker returned HTTP "
                . $http_code
        ];
    }


    /* -----------------------------------------------------
       CLEAN RESPONSE
    ----------------------------------------------------- */

    $response =
        trim($response);


    /*
     * Remove UTF-8 BOM.
     */

    $response =
        preg_replace(
            '/^\xEF\xBB\xBF/',
            '',
            $response
        );


    /*
     * Remove accidental Markdown fences.
     *
     * Normally the remote server should return
     * pure JSON without these fences.
     */

    $response =
        preg_replace(
            '/^```(?:json|php)?\s*/i',
            '',
            $response
        );


    $response =
        preg_replace(
            '/\s*```\s*$/',
            '',
            $response
        );


    $response =
        trim($response);


    /* -----------------------------------------------------
       JSON DECODE
    ----------------------------------------------------- */

    $data =
        json_decode(
            $response,
            true
        );


    if (!is_array($data)) {

        return [

            "success" => false,

            "status" => "OFF",

            "message" =>
                "Invalid response from license server. "
                . "JSON error: "
                . json_last_error_msg()
        ];
    }


    return $data;
}


/* ---------------------------------------------------------
   PERFORM LICENSE CHECK
--------------------------------------------------------- */

$license =
    guard_check_license(
        $user_id,
        $license_url
    );


/* ---------------------------------------------------------
   AUTHORIZATION
--------------------------------------------------------- */

$authorized =
    isset($license["status"]) &&
    $license["status"] === "ON";


/* ---------------------------------------------------------
   STOP APPLICATION IF NOT AUTHORIZED
--------------------------------------------------------- */

if (!$authorized) {

    http_response_code(403);

    ?>

    <!DOCTYPE html>

    <html>

    <head>

        <meta charset="UTF-8">

        <title>
            Application Disabled
        </title>

        <style>

        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            text-align: center;
            padding: 50px;
        }

        .box {
            background: white;
            max-width: 700px;
            margin: auto;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 0 10px #aaa;
            border: 2px solid red;
        }

        h1 {
            color: red;
        }

        .message {
            font-size: 18px;
            margin: 20px;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #eee;
            border: 1px solid #ccc;
            border-radius: 6px;
            text-decoration: none;
            color: black;
        }

        </style>

    </head>

    <body>

        <div class="box">

            <h1>
                APPLICATION DISABLED
            </h1>

            <p class="message">

                <?= htmlspecialchars(
                    $license["message"]
                    ?? "Application is not authorized.",
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>

            </p>

            <p>

                User ID:

                <?= htmlspecialchars(
                    $user_id,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>

            </p>

            <a href="index.php">
                Back to Main Application
            </a>

        </div>

    </body>

    </html>

    <?php

    exit;
}

?>