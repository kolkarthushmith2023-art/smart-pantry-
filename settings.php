<?php

session_start();

require_once "config/database.php";


/* =========================================
   CHECK LOGIN
   ========================================= */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

$user_id = $_SESSION["user_id"];


/* =========================================
   GET USER DETAILS
   ========================================= */

$stmt = $conn->prepare(
    "SELECT id, name, email
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();


/* =========================================
   UPDATE PROFILE
   ========================================= */

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);


    if ($name != "" && $email != "") {

        $stmt = $conn->prepare(
            "UPDATE users
             SET name = ?, email = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "ssi",
            $name,
            $email,
            $user_id
        );


        if ($stmt->execute()) {

            $message =
                "Profile updated successfully.";

            $user["name"] = $name;
            $user["email"] = $email;

        }


        $stmt->close();

    } else {

        $message =
            "Please fill all profile details.";

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
    Settings - Smart Pantry
</title>


<style>

/* =========================================
   RESET
   ========================================= */

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

}


/* =========================================
   BODY
   ========================================= */

body {

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    background: #e8eee1;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

}


/* =========================================
   APP
   ========================================= */

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


/* =========================================
   HEADER
   ========================================= */

.header {

    height: 88px;

    padding: 25px 22px 10px;

}


.title {

    color: #248733;

    font-size: 22px;

    font-weight: 700;

}


.subtitle {

    color: #888;

    font-size: 10px;

    margin-top: 4px;

}


/* =========================================
   CONTENT
   ========================================= */

.content {

    height: calc(100% - 88px);

    overflow-y: auto;

    padding:
        5px
        15px
        100px;

}


/* =========================================
   PROFILE CARD
   ========================================= */

.profile-card {

    background: white;

    border-radius: 17px;

    padding: 18px;

    text-align: center;

    margin-bottom: 12px;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.05);

}


.profile-icon {

    width: 60px;

    height: 60px;

    border-radius: 50%;

    margin: 0 auto 10px;

    background: #eaf4e6;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 27px;

}


.profile-name {

    font-size: 15px;

    font-weight: 700;

    color: #333;

}


.profile-email {

    font-size: 9px;

    color: #999;

    margin-top: 4px;

}


/* =========================================
   SECTION
   ========================================= */

.section {

    background: white;

    border-radius: 16px;

    padding: 15px;

    margin-bottom: 12px;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.05);

}


.section-title {

    font-size: 13px;

    font-weight: 700;

    color: #333;

    margin-bottom: 13px;

}


/* =========================================
   FORM
   ========================================= */

label {

    display: block;

    font-size: 9px;

    color: #777;

    margin-bottom: 5px;

}


input {

    width: 100%;

    padding: 10px;

    border: 1px solid #e2e6dd;

    border-radius: 9px;

    outline: none;

    font-size: 11px;

    margin-bottom: 11px;

    background: #fafbf8;

}


input:focus {

    border-color: #55aa5e;

}


/* =========================================
   SAVE BUTTON
   ========================================= */

.save-btn {

    width: 100%;

    padding: 10px;

    border: none;

    border-radius: 10px;

    background: #318d3b;

    color: white;

    font-size: 11px;

    font-weight: 600;

    cursor: pointer;

}


/* =========================================
   MESSAGE
   ========================================= */

.message {

    background: #eef6e9;

    color: #4d7952;

    border-radius: 9px;

    padding: 9px;

    margin-bottom: 12px;

    text-align: center;

    font-size: 9px;

}


/* =========================================
   REMINDER
   ========================================= */

.preference {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 5px 0;

}


.preference-text {

    font-size: 11px;

    color: #444;

}


.preference-sub {

    font-size: 9px;

    color: #999;

    margin-top: 3px;

}


select {

    padding: 7px;

    border: 1px solid #e1e5dd;

    border-radius: 8px;

    font-size: 9px;

    background: white;

}


/* =========================================
   STATISTICS BUTTON
   ========================================= */

.statistics-btn {

    display: block;

    width: 100%;

    padding: 11px;

    text-align: center;

    border-radius: 10px;

    background: #eef6e9;

    color: #248733;

    text-decoration: none;

    font-size: 11px;

    font-weight: 600;

}


/* =========================================
   LOGOUT
   ========================================= */

.logout {

    display: block;

    width: 100%;

    padding: 11px;

    text-align: center;

    border-radius: 10px;

    background: #fff0ee;

    color: #d04437;

    text-decoration: none;

    font-size: 11px;

    font-weight: 600;

}


/* =========================================
   BOTTOM NAVIGATION
   ========================================= */

.bottom-nav {

    position: absolute;

    bottom: 0;

    left: 0;

    right: 0;

    height: 72px;

    background:
        rgba(255,255,255,0.97);

    border-top:
        1px solid #e9ece3;

    display: flex;

    justify-content:
        space-around;

    align-items:
        center;

    z-index: 10;

}


.nav-item {

    flex: 1;

    text-align: center;

    text-decoration: none;

    color: #999;

    font-size: 9px;

}


.nav-icon {

    display: block;

    font-size: 18px;

    margin-bottom: 4px;

}


.nav-item.active {

    color: #248733;

    font-weight: 600;

}


/* =========================================
   SCROLLBAR
   ========================================= */

.content::-webkit-scrollbar {

    width: 5px;

}


.content::-webkit-scrollbar-track {

    background: transparent;

}


.content::-webkit-scrollbar-thumb {

    background: #a7aaa2;

    border-radius: 10px;

}


/* =========================================
   MOBILE
   ========================================= */

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


<!-- ======================================
     HEADER
     ====================================== -->

<div class="header">

    <div class="title">

        Settings

    </div>


    <div class="subtitle">

        Manage your account and preferences

    </div>

</div>



<!-- ======================================
     CONTENT
     ====================================== -->

<div class="content">


<!-- ======================================
     PROFILE
     ====================================== -->

<div class="profile-card">


    <div class="profile-icon">

        👤

    </div>


    <div class="profile-name">

        <?php

        echo htmlspecialchars(
            $user["name"]
        );

        ?>

    </div>


    <div class="profile-email">

        <?php

        echo htmlspecialchars(
            $user["email"]
        );

        ?>

    </div>


</div>



<!-- ======================================
     SUCCESS MESSAGE
     ====================================== -->

<?php

if ($message != "") {

?>

<div class="message">

    <?php

    echo htmlspecialchars(
        $message
    );

    ?>

</div>

<?php

}

?>



<!-- ======================================
     PROFILE INFORMATION
     ====================================== -->

<div class="section">


    <div class="section-title">

        Profile Information

    </div>


    <form
        method="POST"
        action=""
    >


        <label>

            Name

        </label>


        <input
            type="text"
            name="name"
            value="<?php

                echo htmlspecialchars(
                    $user["name"]
                );

            ?>"
            required
        >



        <label>

            Email

        </label>


        <input
            type="email"
            name="email"
            value="<?php

                echo htmlspecialchars(
                    $user["email"]
                );

            ?>"
            required
        >



        <button
            type="submit"
            class="save-btn"
        >

            Save Changes

        </button>


    </form>


</div>



<!-- ======================================
     REMINDER PREFERENCES
     ====================================== -->

<div class="section">


    <div class="section-title">

        Reminder Preferences

    </div>


    <div class="preference">


        <div>

            <div class="preference-text">

                Expiry Reminder

            </div>


            <div class="preference-sub">

                Remind me before food expires

            </div>

        </div>


        <select>

            <option>

                1 Day

            </option>


            <option selected>

                3 Days

            </option>


            <option>

                5 Days

            </option>


            <option>

                7 Days

            </option>

        </select>


    </div>


</div>



<!-- ======================================
     STATISTICS & REPORTS
     ====================================== -->

<div class="section">


    <div class="section-title">

        Statistics & Reports

    </div>


    <a
        href="statistics.php"
        class="statistics-btn"
    >

        📊 View Pantry Statistics

    </a>


</div>



<!-- ======================================
     ACCOUNT
     ====================================== -->

<div class="section">


    <div class="section-title">

        Account

    </div>


    <a
        href="logout.php"
        class="logout"
    >

        Log Out

    </a>


</div>


</div>



<!-- ======================================
     BOTTOM NAVIGATION
     ====================================== -->

<div class="bottom-nav">


    <a
        href="dashboard.php"
        class="nav-item"
    >

        <span class="nav-icon">

            🏠

        </span>

        Home

    </a>



    <a
        href="pantry.php"
        class="nav-item"
    >

        <span class="nav-icon">

            🧺

        </span>

        Pantry

    </a>



    <a
        href="add_item.php"
        class="nav-item"
    >

        <span class="nav-icon">

            ➕

        </span>

        Add

    </a>



    <a
        href="notifications.php"
        class="nav-item"
    >

        <span class="nav-icon">

            🔔

        </span>

        Alerts

    </a>



    <a
        href="settings.php"
        class="nav-item active"
    >

        <span class="nav-icon">

            ⚙️

        </span>

        Profile

    </a>


</div>


</div>


</body>

</html>