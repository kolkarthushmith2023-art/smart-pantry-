<?php

session_start();

require_once "config/database.php";


/* CHECK LOGIN */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");

    exit();

}


$user_id = $_SESSION["user_id"];


/* SEARCH */

$search = "";

if (isset($_GET["search"])) {

    $search = trim($_GET["search"]);

}


/* CATEGORY FILTER */

$category = "";

if (isset($_GET["category"])) {

    $category = trim($_GET["category"]);

}


/* BUILD QUERY */

$sql = "SELECT
            id,
            item_name,
            category,
            quantity,
            unit,
            purchase_date,
            expiry_date
        FROM food_items
        WHERE user_id = ?";


$params = [$user_id];

$types = "i";


/* SEARCH FILTER */

if ($search != "") {

    $sql .= " AND item_name LIKE ?";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;

    $types .= "s";

}


/* CATEGORY FILTER */

if ($category != "") {

    $sql .= " AND category = ?";

    $params[] = $category;

    $types .= "s";

}


/* SORT */

$sql .= " ORDER BY expiry_date ASC";


/* PREPARE */

$stmt = $conn->prepare($sql);


/* BIND PARAMETERS */

$stmt->bind_param(
    $types,
    ...$params
);


/* EXECUTE */

$stmt->execute();


$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>My Pantry - Smart Pantry</title>


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

    padding: 27px 22px 10px;

}


.title-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

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


/* ADD BUTTON */

.add-small {

    width: 38px;

    height: 38px;

    border-radius: 50%;

    border: none;

    background: #36a842;

    color: white;

    font-size: 21px;

    cursor: pointer;

    box-shadow:
        0 4px 10px rgba(54,168,66,0.2);

}


/* CONTENT */

.content {

    height: calc(100% - 90px);

    overflow-y: auto;

    padding: 5px 20px 95px;

}


/* SEARCH */

.search-box {

    height: 43px;

    background: white;

    border-radius: 11px;

    display: flex;

    align-items: center;

    padding: 0 12px;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.05);

    margin-bottom: 12px;

}


.search-box span {

    font-size: 16px;

    margin-right: 8px;

}


.search-box input {

    width: 100%;

    border: none;

    outline: none;

    background: transparent;

    font-size: 11px;

    color: #333;

}


/* CATEGORY FILTER */

.filters {

    display: flex;

    gap: 7px;

    overflow-x: auto;

    padding-bottom: 5px;

    margin-bottom: 12px;

}


.filters a {

    text-decoration: none;

    white-space: nowrap;

    padding: 7px 12px;

    border-radius: 15px;

    background: white;

    color: #777;

    font-size: 9px;

    border: 1px solid #e3e7dd;

}


.filters a.active {

    background: #36a842;

    color: white;

    border-color: #36a842;

}


/* ITEM COUNT */

.item-count {

    color: #777;

    font-size: 10px;

    margin-bottom: 10px;

}


/* ITEM CARD */

.item-card {

    background: white;

    border-radius: 14px;

    padding: 12px;

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


/* WARNING */

.item-card.warning {

    border-left-color: #e49a24;

}


/* EXPIRED */

.item-card.expired {

    border-left-color: #d84b3e;

}


/* LEFT */

.item-left {

    display: flex;

    align-items: center;

    gap: 10px;

    min-width: 0;

}


.item-icon {

    width: 43px;

    height: 43px;

    flex-shrink: 0;

    border-radius: 11px;

    background: #eef5e9;

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 20px;

}


.item-info {

    min-width: 0;

}


.item-name {

    font-size: 12px;

    font-weight: 600;

    color: #333;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;

}


.item-category {

    font-size: 9px;

    color: #999;

    margin-top: 4px;

}


/* RIGHT */

.item-right {

    text-align: right;

    margin-left: 8px;

}


.expiry-date {

    color: #777;

    font-size: 9px;

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

    color: #999;

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

    <div class="title-row">

        <div>

            <div class="title">
                My Pantry
            </div>

            <div class="subtitle">
                Manage your food inventory
            </div>

        </div>


        <button
            class="add-small"
            onclick="window.location.href='add_item.php'"
        >

            +

        </button>

    </div>

</div>



<!-- CONTENT -->

<div class="content">


    <!-- SEARCH -->

    <form method="GET">

        <div class="search-box">

            <span>🔍</span>

            <input
                type="text"
                name="search"
                placeholder="Search food items..."
                value="<?php
                    echo htmlspecialchars($search);
                ?>"
            >

        </div>

    </form>


    <!-- FILTERS -->

    <div class="filters">

        <a
            href="pantry.php"
            class="<?php
                if ($category == "") {
                    echo "active";
                }
            ?>"
        >
            All
        </a>


        <a
            href="pantry.php?category=Dairy"
            class="<?php
                if ($category == "Dairy") {
                    echo "active";
                }
            ?>"
        >
            Dairy
        </a>


        <a
            href="pantry.php?category=Fruits"
            class="<?php
                if ($category == "Fruits") {
                    echo "active";
                }
            ?>"
        >
            Fruits
        </a>


        <a
            href="pantry.php?category=Vegetables"
            class="<?php
                if ($category == "Vegetables") {
                    echo "active";
                }
            ?>"
        >
            Vegetables
        </a>


        <a
            href="pantry.php?category=Bakery"
            class="<?php
                if ($category == "Bakery") {
                    echo "active";
                }
            ?>"
        >
            Bakery
        </a>


        <a
            href="pantry.php?category=Grains"
            class="<?php
                if ($category == "Grains") {
                    echo "active";
                }
            ?>"
        >
            Grains
        </a>

    </div>


    <!-- COUNT -->

    <div class="item-count">

        <?php echo $result->num_rows; ?>

        item(s) in your pantry

    </div>


    <!-- ITEMS -->

    <?php

    if ($result->num_rows > 0) {


        while ($item = $result->fetch_assoc()) {


            $expiry_timestamp =
                strtotime($item["expiry_date"]);


            $today_timestamp =
                strtotime(date("Y-m-d"));


            $days_left =
                floor(
                    (
                        $expiry_timestamp -
                        $today_timestamp
                    ) / 86400
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
            class="item-card <?php
                echo $card_class;
            ?>"
        >


            <div class="item-left">


                <div class="item-icon">

                    <?php echo $icon; ?>

                </div>


                <div class="item-info">

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


            <div class="item-right">


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
                🧺
            </div>

            No food items found.

            <br><br>

            Add an item to your pantry.

        </div>


    <?php

    }

    ?>


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
        class="nav-item active"
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