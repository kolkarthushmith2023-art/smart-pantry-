<?php

session_start();

require_once "config/database.php";

/* Make sure user is logged in */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["user_name"];


/* TOTAL FOOD ITEMS */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM food_items
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$total_items = $row["total"];

$stmt->close();


/* EXPIRING SOON
   Expiry date between today and next 7 days
*/

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM food_items
     WHERE user_id = ?
     AND expiry_date >= CURDATE()
     AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$expiring_items = $row["total"];

$stmt->close();


/* EXPIRED ITEMS */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM food_items
     WHERE user_id = ?
     AND expiry_date < CURDATE()"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$expired_items = $row["total"];

$stmt->close();


/* RECENT FOOD ITEMS */

$stmt = $conn->prepare(
    "SELECT id, item_name, category, quantity, unit, expiry_date
     FROM food_items
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 4"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$items_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Smart Pantry - Dashboard</title>

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


        /* APP */

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


        /* HEADER */

        .header {

            padding: 30px 24px 15px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .welcome {

            color: #777;

            font-size: 11px;

            margin-bottom: 5px;

        }


        .user-name {

            color: #248733;

            font-size: 22px;

            font-weight: 700;

        }


        /* PROFILE */

        .profile {

            width: 43px;

            height: 43px;

            border-radius: 50%;

            background: #dcebd7;

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 21px;

        }


        /* CONTENT */

        .content {

            padding: 5px 20px 90px;

            height: calc(100% - 85px);

            overflow-y: auto;

        }


        /* SUMMARY */

        .section-title {

            color: #333;

            font-size: 15px;

            font-weight: 700;

            margin: 12px 0;

        }


        .summary {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 11px;

        }


        .card {

            background: white;

            border-radius: 15px;

            padding: 16px;

            min-height: 105px;

            box-shadow:
                0 4px 14px rgba(0,0,0,0.055);

        }


        .card-icon {

            width: 34px;

            height: 34px;

            border-radius: 10px;

            background: #edf6e9;

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 17px;

            margin-bottom: 9px;

        }


        .card-number {

            color: #248733;

            font-size: 22px;

            font-weight: 700;

        }


        .card-label {

            color: #777;

            font-size: 10px;

            margin-top: 3px;

        }


        /* ADD ITEM */

        .add-button {

            width: 100%;

            height: 48px;

            margin-top: 15px;

            border: none;

            border-radius: 12px;

            background: #36a842;

            color: white;

            font-size: 13px;

            font-weight: 600;

            cursor: pointer;

            box-shadow:
                0 4px 10px rgba(54,168,66,0.18);

        }


        .add-button:hover {

            background: #2d9238;

        }


        /* RECENT ITEMS */

        .items {

            display: flex;

            flex-direction: column;

            gap: 9px;

        }


        .item {

            min-height: 62px;

            background: white;

            border-radius: 12px;

            padding: 10px 12px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            box-shadow:
                0 3px 10px rgba(0,0,0,0.04);

            text-decoration: none;

            color: inherit;

        }


        .item-left {

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .item-icon {

            width: 38px;

            height: 38px;

            border-radius: 10px;

            background: #f0f5e9;

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 18px;

        }


        .item-name {

            font-size: 12px;

            color: #333;

            font-weight: 600;

        }


        .item-category {

            font-size: 9px;

            color: #999;

            margin-top: 3px;

        }


        .expiry {

            text-align: right;

            font-size: 9px;

        }


        .expiry-date {

            color: #777;

            margin-bottom: 3px;

        }


        .expiry-normal {

            color: #2d9238;

        }


        .expiry-warning {

            color: #d88a16;

        }


        .expiry-danger {

            color: #d04432;

        }


        .empty {

            background: white;

            border-radius: 13px;

            padding: 25px;

            text-align: center;

            color: #999;

            font-size: 12px;

        }


        /* BOTTOM NAVIGATION */

        .bottom-nav {

            position: absolute;

            bottom: 0;

            left: 0;

            right: 0;

            height: 72px;

            background: rgba(255,255,255,0.97);

            border-top: 1px solid #e9ece3;

            display: flex;

            justify-content: space-around;

            align-items: center;

            padding: 5px 8px;

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

        <div>

            <div class="welcome">
                Welcome back,
            </div>

            <div class="user-name">
                <?php echo htmlspecialchars($user_name); ?>
            </div>

        </div>


        <div class="profile">
            👤
        </div>

    </div>


    <!-- CONTENT -->

    <div class="content">


        <div class="section-title">
            Pantry Overview
        </div>


        <!-- SUMMARY CARDS -->

        <div class="summary">


            <!-- TOTAL -->

            <div class="card">

                <div class="card-icon">
                    🥫
                </div>

                <div class="card-number">
                    <?php echo $total_items; ?>
                </div>

                <div class="card-label">
                    Total Items
                </div>

            </div>


            <!-- EXPIRING -->

            <div class="card">

                <div class="card-icon">
                    ⏰
                </div>

                <div class="card-number">
                    <?php echo $expiring_items; ?>
                </div>

                <div class="card-label">
                    Expiring Soon
                </div>

            </div>


            <!-- EXPIRED -->

            <div class="card">

                <div class="card-icon">
                    ⚠️
                </div>

                <div class="card-number">
                    <?php echo $expired_items; ?>
                </div>

                <div class="card-label">
                    Expired
                </div>

            </div>


            <!-- WASTE -->

            <div class="card">

                <div class="card-icon">
                    ♻️
                </div>

                <div class="card-number">
                    0
                </div>

                <div class="card-label">
                    Items Saved
                </div>

            </div>


        </div>


        <!-- ADD ITEM -->

        <button
            class="add-button"
            onclick="window.location.href='add_item.php'">

            + &nbsp; Add Food Item

        </button>


        <!-- RECENT -->

        <div class="section-title">
            Recent Pantry Items
        </div>


        <div class="items">


            <?php

            if ($items_result->num_rows > 0) {

                while ($item = $items_result->fetch_assoc()) {

                    $expiry_timestamp =
                        strtotime($item["expiry_date"]);

                    $today_timestamp =
                        strtotime(date("Y-m-d"));

                    $days_left =
                        floor(
                            ($expiry_timestamp -
                            $today_timestamp)
                            / 86400
                        );


                    if ($days_left < 0) {

                        $expiry_class = "expiry-danger";

                        $expiry_text = "Expired";

                    } elseif ($days_left <= 7) {

                        $expiry_class = "expiry-warning";

                        $expiry_text =
                            $days_left . " days left";

                    } else {

                        $expiry_class = "expiry-normal";

                        $expiry_text =
                            $days_left . " days left";
                    }

            ?>

                    <a
                        class="item"
                        href="item_details.php?id=<?php echo $item["id"]; ?>"
                    >

                        <div class="item-left">

                            <div class="item-icon">

                                <?php

                                if ($item["category"] == "Dairy") {
                                    echo "🥛";
                                } elseif ($item["category"] == "Fruits") {
                                    echo "🍎";
                                } elseif ($item["category"] == "Vegetables") {
                                    echo "🥕";
                                } elseif ($item["category"] == "Bakery") {
                                    echo "🍞";
                                } else {
                                    echo "🥫";
                                }

                                ?>

                            </div>


                            <div>

                                <div class="item-name">

                                    <?php
                                    echo htmlspecialchars(
                                        $item["item_name"]
                                    );
                                    ?>

                                </div>

                                <div class="item-category">

                                    <?php
                                    echo htmlspecialchars(
                                        $item["category"]
                                    );
                                    ?>

                                    ·

                                    <?php
                                    echo htmlspecialchars(
                                        $item["quantity"]
                                    );
                                    ?>

                                    <?php
                                    echo htmlspecialchars(
                                        $item["unit"]
                                    );
                                    ?>

                                </div>

                            </div>

                        </div>


                        <div class="expiry">

                            <div class="expiry-date">

                                <?php
                                echo date(
                                    "d M",
                                    strtotime(
                                        $item["expiry_date"]
                                    )
                                );
                                ?>

                            </div>

                            <div class="<?php echo $expiry_class; ?>">

                                <?php
                                echo $expiry_text;
                                ?>

                            </div>

                        </div>

                    </a>

            <?php

                }

            } else {

            ?>

                <div class="empty">

                    No pantry items yet.<br>

                    Add your first food item!

                </div>

            <?php

            }

            ?>

        </div>


    </div>


    <!-- BOTTOM NAVIGATION -->

    <div class="bottom-nav">


        <a
            href="dashboard.php"
            class="nav-item active">

            <span class="nav-icon">
                🏠
            </span>

            Home

        </a>


        <a
            href="pantry.php"
            class="nav-item">

            <span class="nav-icon">
                🧺
            </span>

            Pantry

        </a>


        <a
            href="add_item.php"
            class="nav-item">

            <span class="nav-icon">
                ➕
            </span>

            Add

        </a>


        <a
            href="notifications.php"
            class="nav-item">

            <span class="nav-icon">
                🔔
            </span>

            Alerts

        </a>


        <a
            href="settings.php"
            class="nav-item">

            <span class="nav-icon">
                ⚙️
            </span>

            Profile

        </a>


    </div>


</div>


</body>

</html>