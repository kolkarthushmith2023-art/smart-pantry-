<?php

require_once "config/database.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($name == "" || $email == "" || $password == "" || $confirm_password == "") {

        $error = "Please fill in all fields.";

    } elseif ($password != $confirm_password) {

        $error = "Passwords do not match.";

    } elseif (strlen($password) < 6) {

        $error = "Password must contain at least 6 characters.";

    } else {

        /* Check whether email already exists */

        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);

        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $error = "An account with this email already exists.";

        } else {

            /* Encrypt password */

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            /* Insert user */

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashed_password
            );

            if ($stmt->execute()) {

                $message = "Account created successfully! You can now login.";

            } else {

                $error = "Unable to create account.";

            }

            $stmt->close();
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Sign Up - Smart Pantry</title>


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


        /* BACKGROUND */

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


        /* CONTENT */

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

            background: white;

            display: flex;

            justify-content: center;

            align-items: center;

            color: #333;

            font-size: 22px;

            text-decoration: none;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }


        /* HEADER */

        .header {

            text-align: center;

            margin-top: 28px;
        }


        .header h1 {

            color: #248733;

            font-size: 27px;

            font-weight: 700;
        }


        .header p {

            color: #777;

            font-size: 13px;

            margin-top: 7px;
        }


        /* FORM CARD */

        .signup-card {

            margin-top: 24px;

            background: white;

            border-radius: 18px;

            padding: 22px 20px;

            box-shadow:
                0 5px 18px rgba(0,0,0,0.07);
        }


        /* FIELD */

        .field {

            margin-bottom: 15px;
        }


        .field label {

            display: block;

            margin-bottom: 6px;

            color: #444;

            font-size: 12px;

            font-weight: 600;
        }


        .field input {

            width: 100%;

            height: 42px;

            padding: 0 12px;

            border: 1px solid #dfe3d8;

            border-radius: 9px;

            background: #fafbf7;

            font-size: 13px;

            outline: none;
        }


        .field input:focus {

            border-color: #36a842;

            background: white;
        }


        /* BUTTON */

        .signup-btn {

            width: 100%;

            height: 45px;

            border: none;

            border-radius: 9px;

            background: #36a842;

            color: white;

            font-size: 13px;

            font-weight: 600;

            cursor: pointer;

            margin-top: 5px;
        }


        .signup-btn:hover {

            background: #2d9238;
        }


        /* ERROR */

        .error {

            padding: 10px;

            margin-bottom: 14px;

            border-radius: 8px;

            background: #fff0ed;

            color: #c0392b;

            font-size: 11px;

            text-align: center;
        }


        /* SUCCESS */

        .success {

            padding: 10px;

            margin-bottom: 14px;

            border-radius: 8px;

            background: #edf8ee;

            color: #238733;

            font-size: 11px;

            text-align: center;
        }


        /* LOGIN */

        .login-link {

            text-align: center;

            margin-top: 20px;

            color: #777;

            font-size: 12px;
        }


        .login-link a {

            color: #248733;

            font-weight: 600;

            text-decoration: none;
        }


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


    <!-- BACKGROUND PATTERN -->

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

        <a href="login.php"
           class="back">

            ←

        </a>


        <!-- HEADER -->

        <div class="header">

            <h1>
                Create Account
            </h1>

            <p>
                Start managing your pantry
            </p>

        </div>


        <!-- FORM -->

        <div class="signup-card">


            <?php if ($error != "") { ?>

                <div class="error">

                    <?php echo htmlspecialchars($error); ?>

                </div>

            <?php } ?>


            <?php if ($message != "") { ?>

                <div class="success">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php } ?>


            <form method="POST">


                <div class="field">

                    <label>
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        placeholder="Enter your name"
                        required>

                </div>


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


                <div class="field">

                    <label>
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Create a password"
                        required>

                </div>


                <div class="field">

                    <label>
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Confirm your password"
                        required>

                </div>


                <button
                    type="submit"
                    class="signup-btn">

                    Create Account

                </button>


            </form>


        </div>


        <!-- LOGIN -->

        <div class="login-link">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </div>


    </div>

</div>


</body>

</html>