<?php

session_start();

require_once "config/database.php";

/* Check login */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$error = "";
$success = "";


/* SAVE FOOD ITEM */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $item_name = trim($_POST["item_name"]);
    $category = trim($_POST["category"]);
    $quantity = trim($_POST["quantity"]);
    $unit = trim($_POST["unit"]);
    $purchase_date = $_POST["purchase_date"];
    $expiry_date = $_POST["expiry_date"];


    /* VALIDATION */

    if (
        $item_name == "" ||
        $category == "" ||
        $quantity == "" ||
        $unit == "" ||
        $purchase_date == "" ||
        $expiry_date == ""
    ) {

        $error = "Please fill in all fields.";

    } elseif (!is_numeric($quantity) || $quantity <= 0) {

        $error = "Quantity must be greater than 0.";

    } elseif ($expiry_date < $purchase_date) {

        $error = "Expiry date cannot be before purchase date.";

    } else {

        /* INSERT INTO DATABASE */

        $stmt = $conn->prepare(
            "INSERT INTO food_items
            (
                user_id,
                item_name,
                category,
                quantity,
                unit,
                purchase_date,
                expiry_date
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "issdsss",
            $user_id,
            $item_name,
            $category,
            $quantity,
            $unit,
            $purchase_date,
            $expiry_date
        );


        if ($stmt->execute()) {

            $success = "Food item added successfully.";

            /*
             * Clear the form after successful insertion
             */

            $item_name = "";
            $category = "";
            $quantity = "";
            $unit = "";
            $purchase_date = "";
            $expiry_date = "";

        } else {

            $error = "Unable to save the food item.";

        }

        $stmt->close();
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

    <title>Add Food Item - Smart Pantry</title>


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
                0 12px 35px rgba(0, 0, 0, 0.12);
        }


        /* HEADER */

        .header {

            height: 88px;

            padding: 25px 22px 10px;

            display: flex;

            align-items: center;

            gap: 14px;
        }


        /* BACK BUTTON */

        .back {

            width: 38px;

            height: 38px;

            border-radius: 50%;

            background: #ffffff;

            display: flex;

            justify-content: center;

            align-items: center;

            text-decoration: none;

            color: #333;

            font-size: 21px;

            box-shadow:
                0 2px 8px rgba(0, 0, 0, 0.07);
        }


        /* TITLE */

        .title {

            color: #248733;

            font-size: 22px;

            font-weight: 700;
        }


        .subtitle {

            color: #888;

            font-size: 10px;

            margin-top: 3px;
        }


        /* CONTENT */

        .content {

            height: calc(100% - 88px);

            overflow-y: auto;

            padding: 8px 22px 100px;
        }


        /* FORM CARD */

        .form-card {

            background: #ffffff;

            border-radius: 18px;

            padding: 20px;

            box-shadow:
                0 5px 18px rgba(0, 0, 0, 0.06);
        }


        /* ERROR */

        .error {

            background: #fff0ed;

            color: #c0392b;

            padding: 10px;

            border-radius: 8px;

            text-align: center;

            font-size: 11px;

            margin-bottom: 15px;
        }


        /* SUCCESS */

        .success {

            background: #edf8ee;

            color: #238733;

            padding: 10px;

            border-radius: 8px;

            text-align: center;

            font-size: 11px;

            margin-bottom: 15px;
        }


        /* FORM FIELD */

        .field {

            margin-bottom: 15px;
        }


        .field label {

            display: block;

            color: #444;

            font-size: 11px;

            font-weight: 600;

            margin-bottom: 6px;
        }


        .field input,
        .field select {

            width: 100%;

            height: 42px;

            padding: 0 11px;

            border: 1px solid #dfe3d8;

            border-radius: 9px;

            background: #fafbf7;

            color: #333;

            font-size: 12px;

            outline: none;
        }


        .field input:focus,
        .field select:focus {

            border-color: #36a842;

            background: #ffffff;
        }


        /* QUANTITY + UNIT */

        .row {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 10px;
        }


        /* SAVE BUTTON */

        .save-btn {

            width: 100%;

            height: 46px;

            margin-top: 6px;

            border: none;

            border-radius: 9px;

            background: #36a842;

            color: #ffffff;

            font-size: 13px;

            font-weight: 600;

            cursor: pointer;
        }


        .save-btn:hover {

            background: #2d9238;
        }


        /* BOTTOM NAVIGATION */

        .bottom-nav {

            position: absolute;

            left: 0;

            right: 0;

            bottom: 0;

            height: 72px;

            background: rgba(255, 255, 255, 0.97);

            border-top: 1px solid #e9ece3;

            display: flex;

            justify-content: space-around;

            align-items: center;
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


    <!-- HEADER -->

    <div class="header">


        <a
            href="dashboard.php"
            class="back"
        >

            ←

        </a>


        <div>

            <div class="title">
                Add Food Item
            </div>

            <div class="subtitle">
                Add a new item to your pantry
            </div>

        </div>


    </div>


    <!-- CONTENT -->

    <div class="content">


        <div class="form-card">


            <!-- ERROR MESSAGE -->

            <?php if ($error != "") { ?>

                <div class="error">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php } ?>


            <!-- SUCCESS MESSAGE -->

            <?php if ($success != "") { ?>

                <div class="success">

                    <?php
                    echo htmlspecialchars($success);
                    ?>

                </div>

            <?php } ?>


            <!-- FORM -->

            <form method="POST">


                <!-- FOOD NAME -->

                <div class="field">

                    <label>
                        Food Name
                    </label>

                    <input
                        type="text"
                        name="item_name"
                        placeholder="e.g. Milk"
                        value="<?php
                            echo htmlspecialchars(
                                $item_name ?? ""
                            );
                        ?>"
                        required
                    >

                </div>


                <!-- CATEGORY -->

                <div class="field">

                    <label>
                        Category
                    </label>

                    <select
                        name="category"
                        required
                    >

                        <option value="">
                            Select category
                        </option>

                        <option value="Dairy">
                            Dairy
                        </option>

                        <option value="Fruits">
                            Fruits
                        </option>

                        <option value="Vegetables">
                            Vegetables
                        </option>

                        <option value="Bakery">
                            Bakery
                        </option>

                        <option value="Grains">
                            Grains
                        </option>

                        <option value="Beverages">
                            Beverages
                        </option>

                        <option value="Snacks">
                            Snacks
                        </option>

                        <option value="Other">
                            Other
                        </option>

                    </select>

                </div>


                <!-- QUANTITY AND UNIT -->

                <div class="row">


                    <!-- QUANTITY -->

                    <div class="field">

                        <label>
                            Quantity
                        </label>

                        <input
                            type="number"
                            name="quantity"
                            step="0.01"
                            min="0.01"
                            placeholder="e.g. 2"
                            required
                        >

                    </div>


                    <!-- UNIT -->

                    <div class="field">

                        <label>
                            Unit
                        </label>

                        <select
                            name="unit"
                            required
                        >

                            <option value="">
                                Select
                            </option>

                            <option value="pieces">
                                Pieces
                            </option>

                            <option value="packets">
                                Packets
                            </option>

                            <option value="kg">
                                Kg
                            </option>

                            <option value="grams">
                                Grams
                            </option>

                            <option value="litres">
                                Litres
                            </option>

                            <option value="ml">
                                Ml
                            </option>

                        </select>

                    </div>


                </div>


                <!-- PURCHASE DATE -->

                <div class="field">

                    <label>
                        Purchase Date
                    </label>

                    <input
                        type="date"
                        name="purchase_date"
                        value="<?php
                            echo htmlspecialchars(
                                $purchase_date ?? ""
                            );
                        ?>"
                        required
                    >

                </div>


                <!-- EXPIRY DATE -->

                <div class="field">

                    <label>
                        Expiry Date
                    </label>

                    <input
                        type="date"
                        name="expiry_date"
                        value="<?php
                            echo htmlspecialchars(
                                $expiry_date ?? ""
                            );
                        ?>"
                        required
                    >

                </div>


                <!-- SAVE BUTTON -->

                <button
                    type="submit"
                    class="save-btn"
                >

                    Save Food Item

                </button>


            </form>


        </div>


    </div>


    <!-- BOTTOM NAVIGATION -->

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
            class="nav-item active"
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
            class="nav-item"
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