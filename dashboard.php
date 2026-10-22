<?php

session_start();

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["user_name"];

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

$fresh_items = $total_items - $expiring_items - $expired_items;

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

        .app {
            width: 375px;
            height: 760px;
            position: relative;
            overflow: hidden;
            background: #f8f9ed;
            border-radius: 32px;
            box-shadow: 0 12px 35px rgba(0,0,0,0.12);
        }

        .header {
            padding: 28px 20px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome {
            color: #888;
            font-size: 11px;
            margin-bottom: 5px;
        }

        .user-name {
            color: #248733;
            font-size: 23px;
            font-weight: 700;
        }

        .header-subtitle {
            color: #777;
            font-size: 10px;
            margin-top: 5px;
        }

        .profile {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #dcebd7;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 21px;
            text-decoration: none;
            transition: 0.2s;
        }

        .profile:hover {
            transform: scale(1.05);
        }

        .content {
            padding: 4px 18px 95px;
            height: calc(100% - 85px);
            overflow-y: auto;
        }

        .content::-webkit-scrollbar {
            width: 4px;
        }

        .content::-webkit-scrollbar-thumb {
            background: #cbd8c4;
            border-radius: 10px;
        }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 12px 2px;
        }

        .section-title {
            color: #333;
            font-size: 15px;
            font-weight: 700;
        }

        .section-link {
            color: #248733;
            font-size: 10px;
            text-decoration: none;
            font-weight: 600;
        }

        .summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 14px;
            min-height: 104px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.055);
            position: relative;
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
            margin-bottom: 8px;
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

        .card-info {
            color: #aaa;
            font-size: 8px;
            margin-top: 5px;
        }

        .card.warning .card-icon {
            background: #fff3dc;
        }

        .card.warning .card-number {
            color: #d88a16;
        }

        .card.danger .card-icon {
            background: #fde8e5;
        }

        .card.danger .card-number {
            color: #d04432;
        }

        .card.fresh .card-icon {
            background: #e6f3e5;
        }

        .card.fresh .card-number {
            color: #2d9238;
        }

        .add-button {
            width: 100%;
            height: 48px;
            margin-top: 14px;
            border: none;
            border-radius: 13px;
            background: #36a842;
            color: white;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(54,168,66,0.18);
        }

        .add-button:hover {
            background: #2d9238;
        }

        .items {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .item {
            min-height: 68px;
            background: white;
            border-radius: 13px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 3px 10px rgba(0,0,0,0.04);
            text-decoration: none;
            color: inherit;
            transition: 0.2s;
        }

        .item:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(0,0,0,0.07);
        }

        .item-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .item-icon {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: 11px;
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
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }

        .item-category {
            font-size: 9px;
            color: #999;
            margin-top: 3px;
        }

        .expiry {
            text-align: right;
            flex-shrink: 0;
        }

        .expiry-date {
            color: #777;
            font-size: 9px;
            margin-bottom: 4px;
        }

        .expiry-status {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 6px;
            font-size: 8px;
            font-weight: 600;
        }

        .expiry-normal {
            color: #2d9238;
            background: #eaf5e8;
        }

        .expiry-warning {
            color: #c47b0d;
            background: #fff2d9;
        }

        .expiry-danger {
            color: #c33c2b;
            background: #fde7e4;
        }

        .empty {
            background: white;
            border-radius: 14px;
            padding: 28px 20px;
            text-align: center;
            color: #999;
            font-size: 11px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.04);
        }

        .empty-icon {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .empty-title {
            color: #555;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .empty-text {
            color: #999;
            font-size: 10px;
        }

        .view-all {
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 10px;
            padding: 10px;
            border-radius: 10px;
            background: #edf6e9;
            color: #248733;
            text-decoration: none;
            font-size: 10px;
            font-weight: 600;
        }

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

    <div class="header">

        <div>

            <div class="welcome">
                Welcome back,
            </div>

            <div class="user-name">
                <?php echo htmlspecialchars($user_name); ?>
            </div>

            <div class="header-subtitle">
                Keep your pantry fresh and organized
            </div>

        </div>

        <a href="settings.php" class="profile">
            👤
        </a>

    </div>


    <div class="content">

        <div class="section-heading">

            <div class="section-title">
                Pantry Overview
            </div>

            <a href="pantry.php" class="section-link">
                View Pantry →
            </a>

        </div>


        <div class="summary">


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

                <div class="card-info">
                    Items in your pantry
                </div>

            </div>


            <div class="card warning">

                <div class="card-icon">
                    ⏰
                </div>

                <div class="card-number">
                    <?php echo $expiring_items; ?>
                </div>

                <div class="card-label">
                    Expiring Soon
                </div>

                <div class="card-info">
                    Within 7 days
                </div>

            </div>


            <div class="card danger">

                <div class="card-icon">
                    ⚠️
                </div>

                <div class="card-number">
                    <?php echo $expired_items; ?>
                </div>

                <div class="card-label">
                    Expired
                </div>

                <div class="card-info">
                    Need attention
                </div>

            </div>


            <div class="card fresh">

                <div class="card-icon">
                    🌿
                </div>

                <div class="card-number">
                    <?php echo $fresh_items; ?>
                </div>

                <div class="card-label">
                    Fresh Items
                </div>

                <div class="card-info">
                    More than 7 days left
                </div>

            </div>


        </div>


        <button
            class="add-button"
            onclick="window.location.href='add_item.php'">

            + &nbsp; Add Food Item

        </button>


        <div class="section-heading">

            <div class="section-title">
                Recent Pantry Items
            </div>

            <a href="pantry.php" class="section-link">
                See All
            </a>

        </div>


        <div class="items">


            <?php

            if ($items_result->num_rows > 0) {

                while ($item = $items_result->fetch_assoc()) {

                    $expiry_timestamp = strtotime($item["expiry_date"]);

                    $today_timestamp = strtotime(date("Y-m-d"));

                    $days_left = floor(
                        ($expiry_timestamp - $today_timestamp) / 86400
                    );


                    if ($days_left < 0) {

                        $expiry_class = "expiry-danger";

                        $expiry_text = "Expired";

                    } elseif ($days_left == 0) {

                        $expiry_class = "expiry-danger";

                        $expiry_text = "Expires today";

                    } elseif ($days_left <= 3) {

                        $expiry_class = "expiry-danger";

                        $expiry_text = $days_left . " days left";

                    } elseif ($days_left <= 7) {

                        $expiry_class = "expiry-warning";

                        $expiry_text = $days_left . " days left";

                    } else {

                        $expiry_class = "expiry-normal";

                        $expiry_text = $days_left . " days left";

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

                                } elseif ($item["category"] == "Beverages") {

                                    echo "🥤";

                                } elseif ($item["category"] == "Grains") {

                                    echo "🌾";

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
                                    strtotime($item["expiry_date"])
                                );

                                ?>

                            </div>

                            <div class="expiry-status <?php echo $expiry_class; ?>">

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

                    <div class="empty-icon">
                        🧺
                    </div>

                    <div class="empty-title">
                        Your pantry is empty
                    </div>

                    <div class="empty-text">
                        Add your first food item to start tracking expiry dates.
                    </div>

                </div>

                <?php

            }

            ?>

        </div>


        <?php

        if ($total_items > 0) {

            ?>

            <a href="pantry.php" class="view-all">
                View All Pantry Items →
            </a>

            <?php

        }

        ?>

    </div>


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