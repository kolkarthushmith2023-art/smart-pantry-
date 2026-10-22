<?php

session_start();

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

$category = "";

if (isset($_GET["category"])) {
    $category = trim($_GET["category"]);
}

$status = "";

if (isset($_GET["status"])) {
    $status = trim($_GET["status"]);
}

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

if ($search != "") {
    $sql .= " AND item_name LIKE ?";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $types .= "s";
}

if ($category != "") {
    $sql .= " AND category = ?";

    $params[] = $category;
    $types .= "s";
}

if ($status == "fresh") {
    $sql .= " AND expiry_date > DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
}

if ($status == "expiring") {
    $sql .= " AND expiry_date >= CURDATE()
              AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
}

if ($status == "expired") {
    $sql .= " AND expiry_date < CURDATE()";
}

$sql .= " ORDER BY expiry_date ASC";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    $types,
    ...$params
);

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

.add-small {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: none;
    background: #36a842;
    color: white;
    font-size: 21px;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(54,168,66,0.2);
}

.content {
    height: calc(100% - 90px);
    overflow-y: auto;
    padding: 5px 20px 95px;
}

.content::-webkit-scrollbar {
    width: 4px;
}

.content::-webkit-scrollbar-thumb {
    background: #ccd8c6;
    border-radius: 10px;
}

.search-box {
    height: 43px;
    background: white;
    border-radius: 11px;
    display: flex;
    align-items: center;
    padding: 0 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.05);
    margin-bottom: 10px;
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

.filter-title {
    color: #777;
    font-size: 9px;
    font-weight: 600;
    margin: 8px 0 6px;
}

.filters {
    display: flex;
    gap: 7px;
    overflow-x: auto;
    padding-bottom: 5px;
    margin-bottom: 4px;
}

.filters::-webkit-scrollbar {
    display: none;
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

.status-filters a.active {
    background: #248733;
    border-color: #248733;
}

.filter-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 7px 0 10px;
}

.item-count {
    color: #777;
    font-size: 10px;
}

.clear-filter {
    color: #d04437;
    text-decoration: none;
    font-size: 9px;
    font-weight: 600;
}

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
    box-shadow: 0 3px 12px rgba(0,0,0,0.045);
    border-left: 4px solid #43a047;
    transition: 0.2s;
}

.item-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 14px rgba(0,0,0,0.07);
}

.item-card.warning {
    border-left-color: #e49a24;
}

.item-card.expired {
    border-left-color: #d84b3e;
}

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
    max-width: 145px;
}

.item-category {
    font-size: 9px;
    color: #999;
    margin-top: 4px;
}

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
    display: inline-block;
    padding: 3px 6px;
    border-radius: 6px;
    font-size: 8px;
    font-weight: 600;
}

.status.safe {
    color: #2f9238;
    background: #eaf5e8;
}

.status.warning {
    color: #d58916;
    background: #fff2d9;
}

.status.expired {
    color: #d04437;
    background: #fde7e4;
}

.empty {
    background: white;
    border-radius: 15px;
    padding: 35px 20px;
    text-align: center;
    color: #999;
    font-size: 11px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.04);
}

.empty-icon {
    font-size: 35px;
    margin-bottom: 10px;
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
    line-height: 1.5;
}

.empty-button {
    display: inline-block;
    margin-top: 12px;
    padding: 8px 14px;
    border-radius: 9px;
    background: #edf6e9;
    color: #248733;
    text-decoration: none;
    font-size: 9px;
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


<div class="content">

<form method="GET">

    <div class="search-box">

        <span>
            🔍
        </span>

        <input
            type="text"
            name="search"
            placeholder="Search food items..."
            value="<?php echo htmlspecialchars($search); ?>"
        >

        <?php

        if ($category != "") {

            ?>

            <input
                type="hidden"
                name="category"
                value="<?php echo htmlspecialchars($category); ?>"
            >

            <?php

        }

        if ($status != "") {

            ?>

            <input
                type="hidden"
                name="status"
                value="<?php echo htmlspecialchars($status); ?>"
            >

            <?php

        }

        ?>

    </div>

</form>


<div class="filter-title">
    CATEGORY
</div>


<div class="filters">

    <a
        href="pantry.php"
        class="<?php echo $category == "" ? "active" : ""; ?>"
    >
        All
    </a>

    <a
        href="pantry.php?category=Dairy"
        class="<?php echo $category == "Dairy" ? "active" : ""; ?>"
    >
        Dairy
    </a>

    <a
        href="pantry.php?category=Fruits"
        class="<?php echo $category == "Fruits" ? "active" : ""; ?>"
    >
        Fruits
    </a>

    <a
        href="pantry.php?category=Vegetables"
        class="<?php echo $category == "Vegetables" ? "active" : ""; ?>"
    >
        Vegetables
    </a>

    <a
        href="pantry.php?category=Bakery"
        class="<?php echo $category == "Bakery" ? "active" : ""; ?>"
    >
        Bakery
    </a>

    <a
        href="pantry.php?category=Grains"
        class="<?php echo $category == "Grains" ? "active" : ""; ?>"
    >
        Grains
    </a>

    <a
        href="pantry.php?category=Beverages"
        class="<?php echo $category == "Beverages" ? "active" : ""; ?>"
    >
        Beverages
    </a>

    <a
        href="pantry.php?category=Snacks"
        class="<?php echo $category == "Snacks" ? "active" : ""; ?>"
    >
        Snacks
    </a>

</div>


<div class="filter-title">
    EXPIRY STATUS
</div>


<div class="filters status-filters">

    <a
        href="pantry.php<?php echo $category != "" ? "?category=" . urlencode($category) : ""; ?>"
        class="<?php echo $status == "" ? "active" : ""; ?>"
    >
        All
    </a>

    <a
        href="pantry.php?status=fresh<?php echo $category != "" ? "&category=" . urlencode($category) : ""; ?>"
        class="<?php echo $status == "fresh" ? "active" : ""; ?>"
    >
        Fresh
    </a>

    <a
        href="pantry.php?status=expiring<?php echo $category != "" ? "&category=" . urlencode($category) : ""; ?>"
        class="<?php echo $status == "expiring" ? "active" : ""; ?>"
    >
        Expiring Soon
    </a>

    <a
        href="pantry.php?status=expired<?php echo $category != "" ? "&category=" . urlencode($category) : ""; ?>"
        class="<?php echo $status == "expired" ? "active" : ""; ?>"
    >
        Expired
    </a>

</div>


<div class="filter-actions">

    <div class="item-count">

        <?php echo $result->num_rows; ?>

        item(s) found

    </div>

    <?php

    if ($search != "" || $category != "" || $status != "") {

        ?>

        <a
            href="pantry.php"
            class="clear-filter"
        >
            Clear filters
        </a>

        <?php

    }

    ?>

</div>


<?php

if ($result->num_rows > 0) {

    while ($item = $result->fetch_assoc()) {

        $expiry_timestamp = strtotime(
            $item["expiry_date"]
        );

        $today_timestamp = strtotime(
            date("Y-m-d")
        );

        $days_left = floor(
            (
                $expiry_timestamp -
                $today_timestamp
            ) / 86400
        );


        if ($days_left < 0) {

            $card_class = "expired";

            $status_class = "expired";

            $status_text = "Expired";

        } elseif ($days_left == 0) {

            $card_class = "expired";

            $status_class = "expired";

            $status_text = "Expires today";

        } elseif ($days_left <= 3) {

            $card_class = "warning";

            $status_class = "warning";

            $status_text = $days_left . " days left";

        } elseif ($days_left <= 7) {

            $card_class = "warning";

            $status_class = "warning";

            $status_text = $days_left . " days left";

        } else {

            $card_class = "";

            $status_class = "safe";

            $status_text = $days_left . " days left";

        }


        if ($item["category"] == "Dairy") {

            $icon = "🥛";

        } elseif ($item["category"] == "Fruits") {

            $icon = "🍎";

        } elseif ($item["category"] == "Vegetables") {

            $icon = "🥕";

        } elseif ($item["category"] == "Bakery") {

            $icon = "🍞";

        } elseif ($item["category"] == "Grains") {

            $icon = "🌾";

        } elseif ($item["category"] == "Beverages") {

            $icon = "🧃";

        } elseif ($item["category"] == "Snacks") {

            $icon = "🍪";

        } else {

            $icon = "🥫";

        }

        ?>

        <a
            href="item_details.php?id=<?php echo $item["id"]; ?>"
            class="item-card <?php echo $card_class; ?>"
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

                <div
                    class="status <?php echo $status_class; ?>"
                >

                    <?php echo $status_text; ?>

                </div>

            </div>

        </a>

        <?php

    }

} else {

    ?>

    <div class="empty">

        <div class="empty-icon">
            🔎
        </div>

        <div class="empty-title">
            No food items found
        </div>

        <div class="empty-text">
            Try changing your search or filters.
        </div>

        <a
            href="pantry.php"
            class="empty-button"
        >
            Clear Filters
        </a>

    </div>

    <?php

}

?>

</div>


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