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
   TOTAL ITEMS
   ========================================= */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM food_items
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$total_items = $result->fetch_assoc()["total"];

$stmt->close();


/* =========================================
   EXPIRED ITEMS
   ========================================= */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM food_items
     WHERE user_id = ?
     AND expiry_date < CURDATE()"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$expired_items = $result->fetch_assoc()["total"];

$stmt->close();


/* =========================================
   EXPIRING WITHIN 7 DAYS
   ========================================= */

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

$expiring_items = $result->fetch_assoc()["total"];

$stmt->close();


/* =========================================
   FRESH ITEMS
   ========================================= */

$fresh_items =
    $total_items
    - $expired_items
    - $expiring_items;


/* =========================================
   CATEGORY DATA
   ========================================= */

$stmt = $conn->prepare(
    "SELECT
        category,
        COUNT(*) AS total
     FROM food_items
     WHERE user_id = ?
     GROUP BY category
     ORDER BY total DESC"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$category_result = $stmt->get_result();


$categories = [];

$category_counts = [];


while ($row = $category_result->fetch_assoc()) {

    $categories[] = $row["category"];

    $category_counts[] = (int)$row["total"];

}

$stmt->close();


/* =========================================
   RECENTLY ADDED ITEMS
   ========================================= */

$stmt = $conn->prepare(
    "SELECT
        item_name,
        category,
        created_at
     FROM food_items
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 5"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$recent_result = $stmt->get_result();

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
    Statistics - Smart Pantry
</title>


<!-- CHART.JS -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<style>


/* =========================================
   BASIC RESET
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
        20px
        100px;

}


/* =========================================
   STATISTICS GRID
   ========================================= */

.stats {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 10px;

    margin-bottom: 13px;

}


.stat-card {

    background: #ffffff;

    border-radius: 15px;

    padding: 14px;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.05);

}


.stat-number {

    font-size: 22px;

    font-weight: 700;

    color: #333;

}


.stat-label {

    font-size: 9px;

    color: #888;

    margin-top: 4px;

}


.total {

    border-left:
        4px solid #36a842;

}


.warning {

    border-left:
        4px solid #e49a24;

}


.expired {

    border-left:
        4px solid #d04437;

}


.fresh {

    border-left:
        4px solid #43a047;

}


/* =========================================
   SECTION
   ========================================= */

.section {

    background: #ffffff;

    border-radius: 16px;

    padding: 16px;

    margin-bottom: 12px;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.05);

}


.section-title {

    color: #333;

    font-size: 13px;

    font-weight: 700;

    margin-bottom: 14px;

}


/* =========================================
   CATEGORY BARS
   ========================================= */

.category-row {

    margin-bottom: 13px;

}


.category-header {

    display: flex;

    justify-content: space-between;

    margin-bottom: 5px;

}


.category-name {

    font-size: 10px;

    color: #555;

}


.category-number {

    font-size: 10px;

    color: #777;

    font-weight: 600;

}


.bar-bg {

    height: 7px;

    background: #edf0e8;

    border-radius: 10px;

    overflow: hidden;

}


.bar {

    height: 100%;

    background: #55aa5e;

    border-radius: 10px;

}


/* =========================================
   PIE CHART
   ========================================= */

.chart-container {

    width: 230px;

    height: 230px;

    margin:
        0 auto;

    display: flex;

    justify-content: center;

    align-items: center;

}


/* =========================================
   INSIGHT
   ========================================= */

.insight {

    background: #eef6e9;

    border-radius: 12px;

    padding: 12px;

    font-size: 10px;

    line-height: 1.5;

    color: #54705a;

}


/* =========================================
   RECENT ITEMS
   ========================================= */

.recent-item {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 10px 0;

    border-bottom:
        1px solid #edf0e9;

}


.recent-item:last-child {

    border-bottom: none;

}


.recent-name {

    font-size: 11px;

    color: #333;

    font-weight: 600;

}


.recent-category {

    font-size: 9px;

    color: #999;

    margin-top: 3px;

}


.recent-date {

    font-size: 9px;

    color: #888;

}


/* =========================================
   EMPTY
   ========================================= */

.empty {

    color: #999;

    text-align: center;

    font-size: 10px;

    padding: 15px 0;

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

        Statistics

    </div>


    <div class="subtitle">

        Understand your pantry usage

    </div>

</div>



<!-- ======================================
     CONTENT
     ====================================== -->

<div class="content">



<!-- ======================================
     STATISTICS CARDS
     ====================================== -->

<div class="stats">


    <!-- TOTAL -->

    <div class="stat-card total">

        <div class="stat-number">

            <?php

            echo $total_items;

            ?>

        </div>


        <div class="stat-label">

            Total Items

        </div>

    </div>



    <!-- EXPIRING -->

    <div class="stat-card warning">

        <div class="stat-number">

            <?php

            echo $expiring_items;

            ?>

        </div>


        <div class="stat-label">

            Expiring Soon

        </div>

    </div>



    <!-- EXPIRED -->

    <div class="stat-card expired">

        <div class="stat-number">

            <?php

            echo $expired_items;

            ?>

        </div>


        <div class="stat-label">

            Expired Items

        </div>

    </div>



    <!-- FRESH -->

    <div class="stat-card fresh">

        <div class="stat-number">

            <?php

            echo $fresh_items;

            ?>

        </div>


        <div class="stat-label">

            Fresh Items

        </div>

    </div>


</div>



<!-- ======================================
     CATEGORY DISTRIBUTION
     ====================================== -->

<div class="section">


    <div class="section-title">

        Pantry by Category

    </div>


<?php

if (count($categories) > 0) {


    $max_count =
        max($category_counts);


    for (
        $i = 0;
        $i < count($categories);
        $i++
    ) {


        $percentage =
            (
                $category_counts[$i]
                /
                $max_count
            )
            * 100;

?>


    <div class="category-row">


        <div class="category-header">


            <div class="category-name">

                <?php

                echo htmlspecialchars(
                    $categories[$i]
                );

                ?>

            </div>


            <div class="category-number">

                <?php

                echo $category_counts[$i];

                ?>

            </div>


        </div>


        <div class="bar-bg">


            <div
                class="bar"
                style="width:
                    <?php
                    echo $percentage;
                    ?>%;"
            ></div>


        </div>


    </div>


<?php

    }

} else {

?>


    <div class="empty">

        No category data available yet.

    </div>


<?php

}

?>


</div>



<!-- ======================================
     PIE CHART
     ====================================== -->

<div class="section">


    <div class="section-title">

        Food Distribution

    </div>


<?php

if (count($categories) > 0) {

?>


    <div class="chart-container">


        <canvas
            id="categoryChart"
        ></canvas>


    </div>


<?php

} else {

?>


    <div class="empty">

        Add food items to view
        the distribution chart.

    </div>


<?php

}

?>


</div>



<!-- ======================================
     PANTRY INSIGHT
     ====================================== -->

<div class="section">


    <div class="section-title">

        Pantry Insight

    </div>


    <div class="insight">


<?php


if ($total_items == 0) {


    echo
        "Start adding food items "
        .
        "to receive useful pantry insights.";


}


elseif ($expired_items > 0) {


    echo
        "You have "
        .
        $expired_items
        .
        " expired item(s). "
        .
        "Check your pantry and "
        .
        "remove or use them.";


}


elseif ($expiring_items > 0) {


    echo
        "You have "
        .
        $expiring_items
        .
        " item(s) expiring "
        .
        "within 7 days. "
        .
        "Consider using them soon.";


}


else {


    echo
        "Great! Your pantry currently "
        .
        "has no expired or soon-to-expire items.";


}


?>


    </div>


</div>



<!-- ======================================
     RECENTLY ADDED
     ====================================== -->

<div class="section">


    <div class="section-title">

        Recently Added

    </div>


<?php


if ($recent_result->num_rows > 0) {


    while (
        $recent =
        $recent_result->fetch_assoc()
    ) {


?>


    <div class="recent-item">


        <div>


            <div class="recent-name">

                <?php

                echo htmlspecialchars(
                    $recent["item_name"]
                );

                ?>

            </div>


            <div class="recent-category">

                <?php

                echo htmlspecialchars(
                    $recent["category"]
                );

                ?>

            </div>


        </div>


        <div class="recent-date">

            <?php

            echo date(
                "d M Y",
                strtotime(
                    $recent["created_at"]
                )
            );

            ?>

        </div>


    </div>


<?php

    }


} else {


?>


    <div class="empty">

        No food items added yet.

    </div>


<?php

}


?>


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
        href="statistics.php"
        class="nav-item active"
    >

        <span class="nav-icon">
            📊
        </span>

        Reports

    </a>


</div>


</div>



<!-- ======================================
     PIE CHART JAVASCRIPT
     ====================================== -->

<script>


const categories = <?php

echo json_encode(
    $categories
);

?>;


const categoryCounts = <?php

echo json_encode(
    $category_counts
);

?>;


if (
    categories.length > 0
) {


    const chartElement =
        document.getElementById(
            "categoryChart"
        );


    new Chart(
        chartElement,
        {

            type: "pie",


            data: {

                labels: categories,


                datasets: [{

                    data:
                        categoryCounts

                }]

            },


            options: {

                responsive: true,

                maintainAspectRatio: false,


                plugins: {

                    legend: {

                        position: "bottom",


                        labels: {

                            font: {

                                size: 9

                            },


                            padding: 10

                        }

                    }

                }

            }

        }

    );

}

</script>


</body>

</html>