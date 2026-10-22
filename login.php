<?php

session_start();

require_once "config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($email == "" || $password == "") {

        $error = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, name, email, password
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];

                header("Location: dashboard.php");
                exit();

            } else {

                $error = "Incorrect password.";

            }

        } else {

            $error = "Account not found.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - Smart Pantry</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            background: #e8eee1;

            font-family: Arial, Helvetica, sans-serif;

        }


        /* APP FRAME */

        .app {

            width: 375px;

            height: 760px;

            position: relative;

            overflow: hidden;

            background: #f8f9ed;

            border-radius: 32px;

            box-shadow:
                0 12px 35px rgba(0,0,0,0.12);

        }


        /* BACKGROUND PATTERN */

        .pattern {

            position: absolute;

            inset: -30px;

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            grid-auto-rows: 85px;

            transform: rotate(-8deg);

            opacity: 0.045;

            pointer-events: none;

        }


        .pattern span {

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 27px;

        }


        /* MAIN */

        .content {

            position: relative;

            z-index: 2;

            width: 100%;

            height: 100%;

            padding: 35px 28px;

        }


        /* BACK BUTTON */

        .back {

            width: 38px;

            height: 38px;

            border-radius: 50%;

            border: none;

            background: #ffffff;

            color: #333;

            font-size: 22px;

            display: flex;

            justify-content: center;

            align-items: center;

            cursor: pointer;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

            text-decoration: none;

        }


        /* HEADER */

        .header {

            text-align: center;

            margin-top: 45px;

        }


        .header h1 {

            color: #248733;

            font-size: 27px;

            font-weight: 700;

        }


        .header p {

            color: #777;

            font-size: 13px;

            margin-top: 8px;

        }


        /* LOGIN CARD */

        .login-card {

            margin-top: 32px;

            background: #ffffff;

            border-radius: 18px;

            padding: 24px 20px;

            box-shadow:
                0 5px 18px rgba(0,0,0,0.07);

        }


        /* LABEL */

        .field {

            margin-bottom: 18px;

        }


        .field label {

            display: block;

            color: #444;

            font-size: 12px;

            font-weight: 600;

            margin-bottom: 7px;

        }


        /* INPUT */

        .field input {

            width: 100%;

            height: 44px;

            padding: 0 13px;

            border: 1px solid #dfe3d8;

            border-radius: 9px;

            background: #fafbf7;

            color: #333;

            font-size: 13px;

            outline: none;

        }


        .field input:focus {

            border-color: #36a842;

            background: #ffffff;

        }


        /* FORGOT PASSWORD */

        .forgot {

            text-align: right;

            margin-top: -7px;

            margin-bottom: 20px;

        }


        .forgot a {

            color: #31853a;

            font-size: 11px;

            text-decoration: none;

        }


        /* LOGIN BUTTON */

        .login-btn {

            width: 100%;

            height: 45px;

            border: none;

            border-radius: 9px;

            background: #36a842;

            color: white;

            font-size: 13px;

            font-weight: 600;

            cursor: pointer;

        }


        .login-btn:hover {

            background: #2d9238;

        }


        /* ERROR */

        .error {

            margin-bottom: 15px;

            padding: 10px;

            border-radius: 8px;

            background: #fff0ed;

            color: #c0392b;

            font-size: 11px;

            text-align: center;

        }


        /* DIVIDER */

        .divider {

            display: flex;

            align-items: center;

            gap: 10px;

            margin: 22px 0;

            color: #999;

            font-size: 11px;

        }


        .divider::before,
        .divider::after {

            content: "";

            flex: 1;

            height: 1px;

            background: #e4e6df;

        }


        /* GOOGLE */

        .google {

            width: 100%;

            height: 44px;

            border: 1px solid #dfe3d8;

            border-radius: 9px;

            background: white;

            color: #444;

            font-size: 12px;

            cursor: pointer;

        }


        /* SIGN UP */

        .signup {

            text-align: center;

            margin-top: 24px;

            color: #777;

            font-size: 12px;

        }


        .signup a {

            color: #248733;

            font-weight: 600;

            text-decoration: none;

        }


        /* MOBILE */

        @media (max-width: 420px) {

            body {

                background: #f8f9ed;

            }

            .app {

                width: 100vw;

                height: 100vh;

                border-radius: 0;

                box-shadow: none;

            }

        }

    </style>

</head>


<body>


<div class="app">


    <!-- BACKGROUND -->

    <div class="pattern">

        <span>🥕</span>
        <span>🍎</span>
        <span>🥬</span>
        <span>🥛</span>

        <span>🍞</span>
        <span>🧀</span>
        <span>🍅</span>
        <span>🥕</span>

        <span>🥛</span>
        <span>🍎</span>
        <span>🍞</span>
        <span>🥬</span>

        <span>🍅</span>
        <span>🥕</span>
        <span>🧀</span>
        <span>🍎</span>

        <span>🥬</span>
        <span>🥛</span>
        <span>🍞</span>
        <span>🍅</span>

    </div>


    <div class="content">


        <!-- BACK -->

        <a href="index.php"
           class="back">

            ←

        </a>


        <!-- HEADER -->

        <div class="header">

            <h1>
                Welcome Back!
            </h1>

            <p>
                Login to continue managing your pantry
            </p>

        </div>


        <!-- LOGIN CARD -->

        <div class="login-card">


            <?php

            if ($error != "") {

                echo '<div class="error">'
                    . htmlspecialchars($error)
                    . '</div>';

            }

            ?>


            <form method="POST">


                <!-- EMAIL -->

                <div class="field">

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter your email"
                        required>

                </div>


                <!-- PASSWORD -->

                <div class="field">

                    <label>
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Enter your password"
                        required>

                </div>


                <!-- FORGOT -->

                <div class="forgot">

                    <a href="#">
                        Forgot Password?
                    </a>

                </div>


                <!-- LOGIN -->

                <button
                    type="submit"
                    class="login-btn">

                    Login

                </button>


                <!-- DIVIDER -->

                <div class="divider">
                    or
                </div>


                <!-- GOOGLE -->

                <button
                    type="button"
                    class="google">

                    🌐 &nbsp;
                    Continue with Google

                </button>


            </form>


        </div>


        <!-- SIGN UP -->

        <div class="signup">

            Don't have an account?

            <a href="register.php">
                Sign Up
            </a>

        </div>


    </div>

</div>


</body>

</html>