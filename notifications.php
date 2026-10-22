<?php

session_start();

require_once "config/database.php";


/* CHECK LOGIN */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

$user_id = $_SESSION["user_id"];


/* GET FOOD ITEMS */

$stmt = $conn->prepare(
    "SELECT
        id,
        item_name,
        category,
        quantity,
        unit,
        expiry_date
     FROM food_items
     WHERE user_id = ?
     ORDER BY expiry_date ASC"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Expiry Alerts - Smart Pantry</title>


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


/* HEADER */

.header {

    height: 90px;

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


/* CONTENT */

.content {

    height: calc(100% - 90px);

    overflow-y: auto;

    padding: 5px 20px 100px;
}


/* SUMMARY */

.summary {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 10px;

    margin-bottom: 14px;
}


.summary-card {

    background: white;

    border-radius: 14px;

    padding: 14px;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.05);
}


.summary-number {

    font-size: 20px;

    font-weight: 700;

    color: #333;
}


.summary-label {

    font-size: 9px;

    color: #888;

    margin-top: 4px;
}


.expired-summary {

    border-left: 4px solid #d04437;
}


.warning-summary {

    border-left: 4px solid #e49a24;
}


/* SECTION TITLE */

.section-title {

    font-size: 13px;

    font-weight: 700;

    color: #333;

    margin: 10px 0;
}


/* ALERT CARD */

.alert-card {

    background: white;

    border-radius: 15px;

    padding: 13px;

    margin-bottom: 9px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    text-decoration: none;

    color: inherit;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.045);

    border-left: 4px solid #43a047;
}


.alert-card.warning {

    border-left-color: #e49a24;
}


.alert-card.expired {

    border-left-color: #d04437;
}


.alert-left {

    display: flex;

    align-items: center;

    gap: 10px;
}


.alert-icon {

    width: 43px;

    height: 43px;

    border-radius: 11px;

    background: #eef5e9;

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 20px;
}


.alert-name {

    font-size: 12px;

    font-weight: 600;

    color: #333;
}


.alert-category {

    font-size: 9px;

    color: #999;

    margin-top: 4px;
}


.alert-right {

    text-align: right;
}


.expiry-date {

    font-size: 9px;

    color: #777;

    margin-bottom: 4px;
}


.status {

    font-size: 9px;

    font-weight: 600;
}


.status.safe {

    color: #2f9238;
}


.status.warning {

    color: #d58916;
}


.status.expired {

    color: #d04437;
}


/* EMPTY */

.empty {

    background: white;

    border-radius: 15px;

    padding: 35px 20px;

    text-align: center;

    color: #888;

    font-size: 11px;
}


.empty-icon {

    font-size: 35px;

    margin-bottom: 10px;
}


/* BOTTOM NAV */

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


<!-- HEADER -->

<div class="header">

    <div class="title">
        Expiry Alerts
    </div>

    <div class="subtitle">
        Stay informed before your food expires
    </div>

</div>



<div class="content">


<?php

/*
 * Count different expiry conditions
 */

$expired_count = 0;

$warning_count = 0;


/*
 * First calculate counts.
 * We use a separate query so the main result
 * can still be displayed afterward.
 */

$count_stmt = $conn->prepare(
    "SELECT expiry_date
     FROM food_items
     WHERE user_id = ?"
);

$count_stmt->bind_param(
    "i",
    $user_id
);

$count_stmt->execute();

$count_result = $count_stmt->get_result();


while ($count_item = $count_result->fetch_assoc()) {

    $today = strtotime(date("Y-m-d"));

    $expiry = strtotime(
        $count_item["expiry_date"]
    );

    $days = floor(
        ($expiry - $today) / 86400
    );


    if ($days < 0) {

        $expired_count++;

    }

    elseif ($days <= 7) {

        $warning_count++;

    }

}

$count_stmt->close();

?>


<!-- SUMMARY -->

<div class="summary">


    <div class="summary-card expired-summary">

        <div class="summary-number">

            <?php echo $expired_count; ?>

        </div>

        <div class="summary-label">

            Expired Items

        </div>

    </div>


    <div class="summary-card warning-summary">

        <div class="summary-number">

            <?php echo $warning_count; ?>

        </div>

        <div class="summary-label">

            Expiring Soon

        </div>

    </div>


</div>



<div class="section-title">

    Food Expiry Status

</div>



<?php

if ($result->num_rows > 0) {


    while ($item = $result->fetch_assoc()) {


        $today = strtotime(date("Y-m-d"));

        $expiry = strtotime(
            $item["expiry_date"]
        );


        $days_left = floor(
            ($expiry - $today) / 86400
        );


        /* STATUS */

        if ($days_left < 0) {

            $card_class = "expired";

            $status_class = "expired";

            $status_text = "Expired";

        }

        elseif ($days_left <= 7) {

            $card_class = "warning";

            $status_class = "warning";

            $status_text =
                $days_left . " days left";

        }

        else {

            $card_class = "";

            $status_class = "safe";

            $status_text =
                $days_left . " days left";

        }


        /* ICON */

        if ($item["category"] == "Dairy") {

            $icon = "🥛";

        }

        elseif ($item["category"] == "Fruits") {

            $icon = "🍎";

        }

        elseif ($item["category"] == "Vegetables") {

            $icon = "🥕";

        }

        elseif ($item["category"] == "Bakery") {

            $icon = "🍞";

        }

        elseif ($item["category"] == "Grains") {

            $icon = "🌾";

        }

        elseif ($item["category"] == "Beverages") {

            $icon = "🧃";

        }

        elseif ($item["category"] == "Snacks") {

            $icon = "🍪";

        }

        else {

            $icon = "🥫";

        }

?>


<a
    href="item_details.php?id=<?php
        echo $item["id"];
    ?>"
    class="alert-card <?php
        echo $card_class;
    ?>"
>


    <div class="alert-left">


        <div class="alert-icon">

            <?php echo $icon; ?>

        </div>


        <div>

            <div class="alert-name">

                <?php

                echo htmlspecialchars(
                    $item["item_name"]
                );

                ?>

            </div>


            <div class="alert-category">

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


    <div class="alert-right">


        <div class="expiry-date">

            <?php

            echo date(
                "d M Y",
                strtotime(
                    $item["expiry_date"]
                )
            );

            ?>

        </div>


        <div class="status <?php
            echo $status_class;
        ?>">

            <?php echo $status_text; ?>

        </div>


    </div>


</a>


<?php

    }

}

else {

?>


<div class="empty">

    <div class="empty-icon">
        🔔
    </div>

    No food items in your pantry yet.

    <br><br>

    Add food items to start tracking expiry dates.

</div>


<?php

}

?>


</div>



<!-- BOTTOM NAV -->

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
    class="nav-item active"
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