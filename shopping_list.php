
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add_item'])) {
        $itemName = trim($_POST['item_name'] ?? "");
        $quantity = (float) ($_POST['quantity'] ?? 0);
        $unit = trim($_POST['unit'] ?? "item");
        $price = (float) ($_POST['estimated_unit_price'] ?? 0);

        if ($itemName === "" || $quantity <= 0 || $price < 0 || $unit === "") {
            $error = "Enter valid item details.";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO shopping_list_items
                (user_id, item_name, quantity, unit, estimated_unit_price)
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param("isdsd", $userId, $itemName, $quantity, $unit, $price);

            if ($stmt->execute()) {
                $message = "Item added successfully.";
            } else {
                $error = "Unable to add the item.";
            }

            $stmt->close();
        }
    }

    if (isset($_POST['delete_item'])) {
        $itemId = (int) ($_POST['item_id'] ?? 0);

        $stmt = $conn->prepare(
            "DELETE FROM shopping_list_items WHERE id = ? AND user_id = ?"
        );
        $stmt->bind_param("ii", $itemId, $userId);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = "Item deleted successfully.";
        } else {
            $error = "Item could not be deleted.";
        }

        $stmt->close();
    }
}

$stmt = $conn->prepare(
    "SELECT id, item_name, quantity, unit, estimated_unit_price
     FROM shopping_list_items
     WHERE user_id = ?
     ORDER BY id DESC"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$totalEstimate = 0;

while ($row = $result->fetch_assoc()) {
    $row['item_total'] = (float) $row['quantity'] * (float) $row['estimated_unit_price'];
    $totalEstimate += $row['item_total'];
    $items[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping List - Smart Pantry</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 16px;
            font-family: Arial, sans-serif;
            background: #f4f7f5;
            color: #26352d;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        h1 {
            margin: 0 0 8px;
            color: #236b48;
        }

        .subtitle {
            color: #68756d;
            margin: 0;
        }

        .back-link {
            color: #236b48;
            text-decoration: none;
            font-weight: bold;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: 14px;
            margin-bottom: 22px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }

        h2 {
            margin-top: 0;
            font-size: 21px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d5ded8;
            border-radius: 8px;
            font-size: 15px;
        }

        button {
            border: 0;
            padding: 11px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .add-button {
            margin-top: 18px;
            background: #26734d;
            color: white;
        }

        .delete-button {
            background: #ffe9e7;
            color: #a52d25;
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            background: #e4f6e9;
            color: #20643b;
            margin-bottom: 16px;
        }

        .error {
            padding: 12px;
            border-radius: 8px;
            background: #ffeded;
            color: #a52d25;
            margin-bottom: 16px;
        }

        .summary {
            background: #e5f4e9;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .summary p {
            margin: 0 0 6px;
            color: #42634e;
        }

        .total {
            font-size: 30px;
            font-weight: bold;
            color: #236b48;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px 10px;
            text-align: left;
            border-bottom: 1px solid #e8eee9;
        }

        th {
            color: #526258;
            background: #f7faf8;
        }

        .empty {
            text-align: center;
            padding: 28px;
            color: #77827a;
        }

        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .card {
                padding: 17px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>Shopping List</h1>
            <p class="subtitle">Plan your grocery shopping and estimate the cost.</p>
        </div>
        <a class="back-link" href="dashboard.php">← Back to Dashboard</a>
    </div>

    <?php if ($message !== ""): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="summary">
        <p>Estimated Shopping Cost</p>
        <div class="total">₹<?= number_format($totalEstimate, 2) ?></div>
        <p><?= count($items) ?> item(s) in your shopping list</p>
    </div>

    <div class="card">
        <h2>Add Shopping Item</h2>

        <form method="POST">
            <div class="form-grid">
                <div>
                    <label for="item_name">Item Name</label>
<input type="text" id="item_name" name="item_name"
       placeholder="Enter item name, e.g. Tomatoes"
       autocomplete="off" required maxlength="100">                </div>

                <div>
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" min="0.01" step="0.01" value="1" required>
                </div>

                <div>
                    <label for="unit">Unit</label>
                    <select id="unit" name="unit" required>
                        <option value="item">Item</option>
                        <option value="kg">Kilogram (kg)</option>
                        <option value="g">Gram (g)</option>
                        <option value="litre">Litre</option>
                        <option value="ml">Millilitre (ml)</option>
                        <option value="pack">Pack</option>
                        <option value="dozen">Dozen</option>
                    </select>
                </div>

                <div>
                    <label for="estimated_unit_price">Estimated Price per Unit (₹)</label>
                    <input type="number" id="estimated_unit_price" name="estimated_unit_price" min="0" step="0.01" value="0" required>
                </div>
            </div>

            <button class="add-button" type="submit" name="add_item">Add to Shopping List</button>
        </form>
    </div>

    <div class="card">
        <h2>Your Shopping Items</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price/Unit</th>
                        <th>Estimated Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($items) > 0): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= number_format((float) $item['quantity'], 2) ?> <?= htmlspecialchars($item['unit']) ?></td>
                            <td>₹<?= number_format((float) $item['estimated_unit_price'], 2) ?></td>
                            <td>₹<?= number_format($item['item_total'], 2) ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Delete this item?');">
                                    <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                                    <button class="delete-button" type="submit" name="delete_item">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty">Your shopping list is empty. Add your first item above.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
