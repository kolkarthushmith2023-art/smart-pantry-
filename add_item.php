
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];
$error = "";
$success = "";

$item_name = "";
$category = "";
$quantity = "";
$unit = "";
$purchase_date = "";
$expiry_date = "";
$purchase_price = "0.00";
$trip_id = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = trim($_POST["item_name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $quantity = trim($_POST["quantity"] ?? "");
    $unit = trim($_POST["unit"] ?? "");
    $purchase_date = $_POST["purchase_date"] ?? "";
    $expiry_date = $_POST["expiry_date"] ?? "";
    $purchase_price = trim($_POST["purchase_price"] ?? "0");
    $trip_id = trim($_POST["trip_id"] ?? "");

    if (
        $item_name == "" ||
        $category == "" ||
        $quantity == "" ||
        $unit == "" ||
        $purchase_date == "" ||
        $expiry_date == "" ||
        $purchase_price == ""
    ) {
        $error = "Please fill in all required fields.";
    } elseif (!is_numeric($quantity) || (float)$quantity <= 0) {
        $error = "Quantity must be greater than 0.";
    } elseif (!is_numeric($purchase_price) || (float)$purchase_price < 0) {
        $error = "Purchase price cannot be negative.";
    } elseif ($expiry_date < $purchase_date) {
        $error = "Expiry date cannot be before purchase date.";
    } else {
        $quantity = (float)$quantity;
        $purchase_price = (float)$purchase_price;
        $selected_trip = null;
        $valid_trip = true;

        if ($trip_id !== "") {
            $selected_trip = (int)$trip_id;

            $trip_check = $conn->prepare(
                "SELECT id FROM shopping_trips WHERE id = ? AND user_id = ?"
            );
            $trip_check->bind_param("ii", $selected_trip, $user_id);
            $trip_check->execute();
            $trip_result = $trip_check->get_result();

            if ($trip_result->num_rows === 0) {
                $valid_trip = false;
                $error = "Please select a valid shopping trip.";
            }

            $trip_check->close();
        }

        if ($valid_trip) {
            $stmt = $conn->prepare(
                "INSERT INTO food_items
                (user_id, item_name, category, quantity, unit,
                 purchase_date, expiry_date, purchase_price, trip_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "issdsssdi",
                $user_id,
                $item_name,
                $category,
                $quantity,
                $unit,
                $purchase_date,
                $expiry_date,
                $purchase_price,
                $selected_trip
            );

            if ($stmt->execute()) {
                $success = "Food item added successfully.";

                $item_name = "";
                $category = "";
                $quantity = "";
                $unit = "";
                $purchase_date = "";
                $expiry_date = "";
                $purchase_price = "0.00";
                $trip_id = "";
            } else {
                $error = "Unable to save the food item. Please try again.";
            }

            $stmt->close();
        }
    }
}

$trips = $conn->prepare(
    "SELECT id, trip_name, trip_date, budget_amount
     FROM shopping_trips
     WHERE user_id = ?
     ORDER BY trip_date DESC, id DESC"
);
$trips->bind_param("i", $user_id);
$trips->execute();
$trip_result = $trips->get_result();

$shopping_trips = [];

while ($trip = $trip_result->fetch_assoc()) {
    $shopping_trips[] = $trip;
}

$trips->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        .app {
            width: 375px;
            height: 760px;
            position: relative;
            overflow: hidden;
            background: #f8f9ed;
            border-radius: 32px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
        }

        .header {
            height: 88px;
            padding: 25px 22px 10px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .back {
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            color: #333;
            font-size: 21px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
        }

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

        .content {
            height: calc(100% - 160px);
            overflow-y: auto;
            padding: 8px 22px 24px;
        }

        .form-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.06);
        }

        .error, .success {
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            font-size: 11px;
            margin-bottom: 15px;
        }

        .error {
            background: #fff0ed;
            color: #c0392b;
        }

        .success {
            background: #edf8ee;
            color: #238733;
        }

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

        .field input, .field select {
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

        .field input:focus, .field select:focus {
            border-color: #36a842;
            background: #ffffff;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .section-title {
            color: #248733;
            font-size: 13px;
            font-weight: bold;
            margin: 8px 0 14px;
        }

        .save-btn {
            width: 100%;
            min-height: 46px;
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
        <a href="dashboard.php" class="back">←</a>
        <div>
            <div class="title">Add Food Item</div>
            <div class="subtitle">Track your pantry and grocery spending</div>
        </div>
    </div>

    <div class="content">
        <div class="form-card">

            <?php if ($error != "") { ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php } ?>

            <?php if ($success != "") { ?>
                <div class="success"><?= htmlspecialchars($success) ?></div>
            <?php } ?>

            <form method="POST">

                <div class="field">
                    <label for="item_name">Food Name</label>
                    <input
                        type="text"
                        id="item_name"
                        name="item_name"
                        placeholder="e.g. Milk"
                        maxlength="100"
                        value="<?= htmlspecialchars($item_name) ?>"
                        required
                    >
                </div>

                <div class="field">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="">Select category</option>
                        <?php
                        $categories = [
                            "Dairy", "Fruits", "Vegetables", "Bakery",
                            "Grains", "Beverages", "Snacks", "Other"
                        ];

                        foreach ($categories as $cat) {
                            $selected = ($category === $cat) ? "selected" : "";
                            echo '<option value="' . htmlspecialchars($cat) . '" ' . $selected . '>' . htmlspecialchars($cat) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="row">
                    <div class="field">
                        <label for="quantity">Quantity</label>
                        <input
                            type="number"
                            id="quantity"
                            name="quantity"
                            step="0.01"
                            min="0.01"
                            placeholder="e.g. 2"
                            value="<?= htmlspecialchars((string)$quantity) ?>"
                            required
                        >
                    </div>

                    <div class="field">
                        <label for="unit">Unit</label>
                        <select id="unit" name="unit" required>
                            <option value="">Select</option>
                            <?php
                            $units = ["pieces", "packets", "kg", "grams", "litres", "ml"];

                            foreach ($units as $u) {
                                $selected = ($unit === $u) ? "selected" : "";
                                echo '<option value="' . htmlspecialchars($u) . '" ' . $selected . '>' . htmlspecialchars($u) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label for="purchase_date">Purchase Date</label>
                    <input
                        type="date"
                        id="purchase_date"
                        name="purchase_date"
                        value="<?= htmlspecialchars($purchase_date) ?>"
                        required
                    >
                </div>

                <div class="field">
                    <label for="expiry_date">Expiry Date</label>
                    <input
                        type="date"
                        id="expiry_date"
                        name="expiry_date"
                        value="<?= htmlspecialchars($expiry_date) ?>"
                        required
                    >
                </div>

                <div class="section-title">Budget Tracking</div>

                <div class="field">
                    <label for="purchase_price">Purchase Price (₹) for the entered quantity</label>
                    <input
                        type="number"
                        id="purchase_price"
                        name="purchase_price"
                        min="0"
                        step="0.01"
                        value="<?= htmlspecialchars($purchase_price) ?>"
                        required
                    >
                </div>

                <div class="field">
                    <label for="trip_id">Shopping Trip (Optional)</label>
                    <select id="trip_id" name="trip_id">
                        <option value="">No shopping trip selected</option>
                        <?php foreach ($shopping_trips as $trip) { ?>
                            <option
                                value="<?= (int)$trip['id'] ?>"
                                <?= ((string)$trip_id === (string)$trip['id']) ? "selected" : "" ?>
                            >
                                <?= htmlspecialchars($trip['trip_name']) ?>
                                — <?= htmlspecialchars($trip['trip_date']) ?>
                            </option>
                        <?php } ?>
                    </select>
                    <?php if (count($shopping_trips) === 0) { ?>
                        <small style="display:block;color:#888;margin-top:6px;">
                            No shopping trips yet. You can add one from the Shopping Trips page.
                        </small>
                    <?php } ?>
                </div>

                <button type="submit" class="save-btn">Save Food Item</button>

            </form>
        </div>
    </div>

    <div class="bottom-nav">
        <a href="dashboard.php" class="nav-item">
            <span class="nav-icon">🏠</span>
            Home
        </a>

        <a href="pantry.php" class="nav-item">
            <span class="nav-icon">🧺</span>
            Pantry
        </a>

        <a href="add_item.php" class="nav-item active">
            <span class="nav-icon">➕</span>
            Add
        </a>

        <a href="notifications.php" class="nav-item">
            <span class="nav-icon">🔔</span>
            Alerts
        </a>

        <a href="settings.php" class="nav-item">
            <span class="nav-icon">⚙️</span>
            Profile
        </a>
    </div>

</div>
</body>
</html>
