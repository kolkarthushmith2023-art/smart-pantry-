<?php

session_start();

require_once "config/database.php";


/* CHECK LOGIN */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");

    exit();

}


$user_id = $_SESSION["user_id"];

$error = "";

$success = "";


/* CHECK ITEM ID */

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: pantry.php");

    exit();

}


$item_id = intval($_GET["id"]);


/* DELETE ITEM */

if (isset($_POST["delete_item"])) {

    $stmt = $conn->prepare(
        "DELETE FROM food_items
         WHERE id = ? AND user_id = ?"
    );

    $stmt->bind_param(
        "ii",
        $item_id,
        $user_id
    );

    if ($stmt->execute()) {

        $stmt->close();

        header("Location: pantry.php");

        exit();

    } else {

        $error = "Unable to delete the item.";

    }

    $stmt->close();

}


/* UPDATE ITEM */

if (isset($_POST["update_item"])) {

    $item_name = trim($_POST["item_name"]);
    $category = trim($_POST["category"]);
    $quantity = trim($_POST["quantity"]);
    $unit = trim($_POST["unit"]);
    $purchase_date = $_POST["purchase_date"];
    $expiry_date = $_POST["expiry_date"];


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

        $stmt = $conn->prepare(
            "UPDATE food_items
             SET item_name = ?,
                 category = ?,
                 quantity = ?,
                 unit = ?,
                 purchase_date = ?,
                 expiry_date = ?
             WHERE id = ? AND user_id = ?"
        );

        $stmt->bind_param(
            "ssdsssii",
            $item_name,
            $category,
            $quantity,
            $unit,
            $purchase_date,
            $expiry_date,
            $item_id,
            $user_id
        );


        if ($stmt->execute()) {

            $success = "Item updated successfully.";

        } else {

            $error = "Unable to update the item.";

        }

        $stmt->close();

    }

}


/* GET ITEM */

$stmt = $conn->prepare(
    "SELECT
        id,
        item_name,
        category,
        quantity,
        unit,
        purchase_date,
        expiry_date
     FROM food_items
     WHERE id = ? AND user_id = ?"
);

$stmt->bind_param(
    "ii",
    $item_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows == 0) {

    $stmt->close();

    header("Location: pantry.php");

    exit();

}


$item = $result->fetch_assoc();

$stmt->close();


/* CALCULATE DAYS LEFT */

$today = strtotime(date("Y-m-d"));

$expiry = strtotime($item["expiry_date"]);

$days_left = floor(
    ($expiry - $today) / 86400
);


/* STATUS */

if ($days_left < 0) {

    $status_class = "expired";

    $status_text = "Expired";

} elseif ($days_left <= 7) {

    $status_class = "warning";

    $status_text = $days_left . " days left";

} else {

    $status_class = "safe";

    $status_text = $days_left . " days left";

}


/* ICON */

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


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Item Details - Smart Pantry</title>


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

    height: 82px;

    padding: 23px 22px 10px;

    display: flex;

    align-items: center;

    gap: 14px;

}


.back {

    width: 38px;

    height: 38px;

    border-radius: 50%;

    background: white;

    display: flex;

    justify-content: center;

    align-items: center;

    text-decoration: none;

    color: #333;

    font-size: 21px;

    box-shadow:
        0 2px 8px rgba(0,0,0,0.07);

}


.title {

    color: #248733;

    font-size: 21px;

    font-weight: 700;

}


.subtitle {

    color: #888;

    font-size: 10px;

    margin-top: 3px;

}


/* CONTENT */

.content {

    height: calc(100% - 82px);

    overflow-y: auto;

    padding: 8px 20px 100px;

}


/* ITEM HERO */

.item-hero {

    background: white;

    border-radius: 19px;

    padding: 22px;

    text-align: center;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.06);

    margin-bottom: 12px;

}


.item-icon {

    width: 70px;

    height: 70px;

    margin: 0 auto 12px;

    border-radius: 18px;

    background: #eef5e9;

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 36px;

}


.item-name {

    color: #333;

    font-size: 20px;

    font-weight: 700;

}


.item-category {

    color: #999;

    font-size: 10px;

    margin-top: 5px;

}


/* STATUS */

.status {

    display: inline-block;

    margin-top: 12px;

    padding: 7px 14px;

    border-radius: 15px;

    font-size: 10px;

    font-weight: 600;

}


.status.safe {

    background: #edf8ee;

    color: #2f9238;

}


.status.warning {

    background: #fff5e4;

    color: #d58916;

}


.status.expired {

    background: #fff0ed;

    color: #d04437;

}


/* DETAILS CARD */

.details-card {

    background: white;

    border-radius: 17px;

    padding: 18px;

    box-shadow:
        0 4px 15px rgba(0,0,0,0.05);

}


.details-title {

    color: #333;

    font-size: 14px;

    font-weight: 700;

    margin-bottom: 14px;

}


.detail-row {

    min-height: 45px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    border-bottom: 1px solid #edf0e9;

}


.detail-row:last-child {

    border-bottom: none;

}


.detail-label {

    color: #888;

    font-size: 10px;

}


.detail-value {

    color: #333;

    font-size: 11px;

    font-weight: 600;

}


/* BUTTONS */

.buttons {

    margin-top: 13px;

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 10px;

}


.edit-btn {

    height: 45px;

    border: none;

    border-radius: 10px;

    background: #36a842;

    color: white;

    font-size: 11px;

    font-weight: 600;

    cursor: pointer;

}


.delete-btn {

    height: 45px;

    border: 1px solid #e2a7a1;

    border-radius: 10px;

    background: #fff5f3;

    color: #c0392b;

    font-size: 11px;

    font-weight: 600;

    cursor: pointer;

}


/* EDIT FORM */

.edit-card {

    background: white;

    border-radius: 17px;

    padding: 18px;

    margin-top: 12px;

    box-shadow:
        0 4px 15px rgba(0,0,0,0.05);

}


.edit-title {

    color: #333;

    font-size: 14px;

    font-weight: 700;

    margin-bottom: 14px;

}


.field {

    margin-bottom: 12px;

}


.field label {

    display: block;

    color: #444;

    font-size: 10px;

    font-weight: 600;

    margin-bottom: 5px;

}


.field input,

.field select {

    width: 100%;

    height: 39px;

    padding: 0 10px;

    border: 1px solid #dfe3d8;

    border-radius: 8px;

    background: #fafbf7;

    color: #333;

    font-size: 11px;

    outline: none;

}


.field input:focus,

.field select:focus {

    border-color: #36a842;

    background: white;

}


.row {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 9px;

}


.update-btn {

    width: 100%;

    height: 43px;

    border: none;

    border-radius: 9px;

    background: #36a842;

    color: white;

    font-size: 11px;

    font-weight: 600;

    cursor: pointer;

}


.cancel-btn {

    display: block;

    text-align: center;

    margin-top: 8px;

    color: #888;

    text-decoration: none;

    font-size: 10px;

}


/* MESSAGE */

.message {

    padding: 10px;

    border-radius: 8px;

    text-align: center;

    font-size: 10px;

    margin-bottom: 10px;

}


.error {

    background: #fff0ed;

    color: #c0392b;

}


.success {

    background: #edf8ee;

    color: #238733;

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

    <a
        href="pantry.php"
        class="back"
    >
        ←
    </a>


    <div>

        <div class="title">
            Item Details
        </div>

        <div class="subtitle">
            View and manage your food item
        </div>

    </div>

</div>



<!-- CONTENT -->

<div class="content">


<?php if ($error != "") { ?>

    <div class="message error">

        <?php echo htmlspecialchars($error); ?>

    </div>

<?php } ?>


<?php if ($success != "") { ?>

    <div class="message success">

        <?php echo htmlspecialchars($success); ?>

    </div>

<?php } ?>


<!-- ITEM SUMMARY -->

<div class="item-hero">


    <div class="item-icon">

        <?php echo $icon; ?>

    </div>


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

    </div>


    <div class="status <?php
        echo $status_class;
    ?>">

        <?php echo $status_text; ?>

    </div>


</div>



<!-- DETAILS -->

<div class="details-card">


    <div class="details-title">

        Food Information

    </div>


    <div class="detail-row">

        <div class="detail-label">
            Category
        </div>

        <div class="detail-value">

            <?php

            echo htmlspecialchars(
                $item["category"]
            );

            ?>

        </div>

    </div>


    <div class="detail-row">

        <div class="detail-label">
            Quantity
        </div>

        <div class="detail-value">

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


    <div class="detail-row">

        <div class="detail-label">
            Purchase Date
        </div>

        <div class="detail-value">

            <?php

            echo date(
                "d M Y",
                strtotime(
                    $item["purchase_date"]
                )
            );

            ?>

        </div>

    </div>


    <div class="detail-row">

        <div class="detail-label">
            Expiry Date
        </div>

        <div class="detail-value">

            <?php

            echo date(
                "d M Y",
                strtotime(
                    $item["expiry_date"]
                )
            );

            ?>

        </div>

    </div>


</div>



<!-- ACTION BUTTONS -->

<div class="buttons">


    <button
        type="button"
        class="edit-btn"
        onclick="showEditForm()"
    >

        ✏️ Edit Item

    </button>


    <form
        method="POST"
        onsubmit="return confirmDelete();"
    >

        <button
            type="submit"
            name="delete_item"
            class="delete-btn"
        >

            🗑 Delete Item

        </button>

    </form>


</div>



<!-- EDIT FORM -->

<div
    class="edit-card"
    id="editForm"
    style="display:none;"
>


    <div class="edit-title">

        Edit Food Item

    </div>


    <form method="POST">


        <!-- NAME -->

        <div class="field">

            <label>
                Food Name
            </label>

            <input
                type="text"
                name="item_name"
                value="<?php
                    echo htmlspecialchars(
                        $item["item_name"]
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

                <option value="Dairy"
                    <?php
                    if ($item["category"] == "Dairy")
                        echo "selected";
                    ?>>
                    Dairy
                </option>

                <option value="Fruits"
                    <?php
                    if ($item["category"] == "Fruits")
                        echo "selected";
                    ?>>
                    Fruits
                </option>

                <option value="Vegetables"
                    <?php
                    if ($item["category"] == "Vegetables")
                        echo "selected";
                    ?>>
                    Vegetables
                </option>

                <option value="Bakery"
                    <?php
                    if ($item["category"] == "Bakery")
                        echo "selected";
                    ?>>
                    Bakery
                </option>

                <option value="Grains"
                    <?php
                    if ($item["category"] == "Grains")
                        echo "selected";
                    ?>>
                    Grains
                </option>

                <option value="Beverages"
                    <?php
                    if ($item["category"] == "Beverages")
                        echo "selected";
                    ?>>
                    Beverages
                </option>

                <option value="Snacks"
                    <?php
                    if ($item["category"] == "Snacks")
                        echo "selected";
                    ?>>
                    Snacks
                </option>

                <option value="Other"
                    <?php
                    if ($item["category"] == "Other")
                        echo "selected";
                    ?>>
                    Other
                </option>

            </select>

        </div>


        <!-- QUANTITY + UNIT -->

        <div class="row">


            <div class="field">

                <label>
                    Quantity
                </label>

                <input
                    type="number"
                    name="quantity"
                    step="0.01"
                    min="0.01"
                    value="<?php
                        echo htmlspecialchars(
                            $item["quantity"]
                        );
                    ?>"
                    required
                >

            </div>


            <div class="field">

                <label>
                    Unit
                </label>

                <select
                    name="unit"
                    required
                >

                    <option value="pieces"
                        <?php
                        if ($item["unit"] == "pieces")
                            echo "selected";
                        ?>>
                        Pieces
                    </option>

                    <option value="packets"
                        <?php
                        if ($item["unit"] == "packets")
                            echo "selected";
                        ?>>
                        Packets
                    </option>

                    <option value="kg"
                        <?php
                        if ($item["unit"] == "kg")
                            echo "selected";
                        ?>>
                        Kg
                    </option>

                    <option value="grams"
                        <?php
                        if ($item["unit"] == "grams")
                            echo "selected";
                        ?>>
                        Grams
                    </option>

                    <option value="litres"
                        <?php
                        if ($item["unit"] == "litres")
                            echo "selected";
                        ?>>
                        Litres
                    </option>

                    <option value="ml"
                        <?php
                        if ($item["unit"] == "ml")
                            echo "selected";
                        ?>>
                        Ml
                    </option>

                </select>

            </div>


        </div>


        <!-- DATES -->

        <div class="field">

            <label>
                Purchase Date
            </label>

            <input
                type="date"
                name="purchase_date"
                value="<?php
                    echo htmlspecialchars(
                        $item["purchase_date"]
                    );
                ?>"
                required
            >

        </div>


        <div class="field">

            <label>
                Expiry Date
            </label>

            <input
                type="date"
                name="expiry_date"
                value="<?php
                    echo htmlspecialchars(
                        $item["expiry_date"]
                    );
                ?>"
                required
            >

        </div>


        <button
            type="submit"
            name="update_item"
            class="update-btn"
        >

            Save Changes

        </button>


        <a
            href="#"
            class="cancel-btn"
            onclick="hideEditForm(); return false;"
        >

            Cancel

        </a>


    </form>


</div>


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


<script>


function showEditForm() {

    document.getElementById("editForm").style.display = "block";

    document.getElementById("editForm").scrollIntoView({
        behavior: "smooth"
    });

}


function hideEditForm() {

    document.getElementById("editForm").style.display = "none";

}


function confirmDelete() {

    return confirm(
        "Are you sure you want to delete this food item?"
    );

}

</script>


</body>

</html>