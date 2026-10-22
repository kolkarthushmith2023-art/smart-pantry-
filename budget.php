
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $budgetAmount = filter_input(INPUT_POST, "budget_amount", FILTER_VALIDATE_FLOAT);

    if ($budgetAmount === false || $budgetAmount === null || $budgetAmount <= 0) {
        $error = "Please enter a valid budget greater than ₹0.";
    } else {
        $month = date("Y-m-01");

        $stmt = $conn->prepare(
            "INSERT INTO monthly_budgets (user_id, budget_month, budget_amount)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE budget_amount = VALUES(budget_amount)"
        );

        $stmt->bind_param("isd", $userId, $month, $budgetAmount);

        if ($stmt->execute()) {
            $message = "Monthly budget saved successfully.";
        } else {
            $error = "Unable to save the budget. Please try again.";
        }

        $stmt->close();
    }
}

$month = date("Y-m-01");
$budgetAmount = 0.00;

$stmt = $conn->prepare(
    "SELECT budget_amount
     FROM monthly_budgets
     WHERE user_id = ? AND budget_month = ?"
);
$stmt->bind_param("is", $userId, $month);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $budgetAmount = (float) $row["budget_amount"];
}

$stmt->close();

$spentAmount = 0.00;

$stmt = $conn->prepare(
    "SELECT COALESCE(SUM(purchase_price), 0) AS total_spent
     FROM food_items
     WHERE user_id = ?
     AND purchase_date >= ?
     AND purchase_date < DATE_ADD(?, INTERVAL 1 MONTH)"
);

$stmt->bind_param("iss", $userId, $month, $month);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $spentAmount = (float) $row["total_spent"];
}

$stmt->close();

$remainingAmount = $budgetAmount - $spentAmount;
$percentage = 0;

if ($budgetAmount > 0) {
    $percentage = min(100, ($spentAmount / $budgetAmount) * 100);
}

$progressColor = "#299447";

if ($budgetAmount > 0 && $spentAmount >= $budgetAmount) {
    $progressColor = "#d64545";
} elseif ($percentage >= 80) {
    $progressColor = "#e49b20";
}

function formatRupees($amount) {
    return "₹" . number_format($amount, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Budget - Smart Pantry</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            background: #eef2e8;
            color: #273329;
            font-family: Arial, Helvetica, sans-serif;
            padding: 30px 16px;
        }

        .container {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 24px;
        }

        .top h1 {
            color: #248733;
            font-size: 28px;
        }

        .top p {
            margin-top: 7px;
            color: #718071;
            font-size: 14px;
        }

        .back {
            color: #248733;
            text-decoration: none;
            font-weight: bold;
            white-space: nowrap;
        }

        .card {
            background: #ffffff;
            border-radius: 18px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(35, 65, 35, 0.06);
        }

        .card h2 {
            font-size: 19px;
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #dce4d8;
            border-radius: 9px;
            font-size: 15px;
            margin-bottom: 15px;
        }

        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 9px;
            background: #36a842;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #2d9238;
        }

        .message, .error {
            padding: 12px;
            border-radius: 9px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .message {
            background: #e7f6e9;
            color: #217632;
        }

        .error {
            background: #fff0ed;
            color: #b83226;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .stat {
            background: #f5f8f1;
            border-radius: 12px;
            padding: 17px 12px;
            overflow-wrap: anywhere;
        }

        .stat p {
            color: #718071;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .stat strong {
            font-size: 20px;
        }

        .green {
            color: #248733;
        }

        .red {
            color: #c0392b;
        }

        .progress-track {
            height: 12px;
            background: #e5eadf;
            border-radius: 20px;
            overflow: hidden;
            margin: 15px 0 10px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 20px;
        }

        .hint {
            color: #718071;
            font-size: 13px;
            line-height: 1.6;
        }

        @media (max-width: 520px) {
            .summary {
                grid-template-columns: 1fr;
            }

            .top h1 {
                font-size: 23px;
            }

            .card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <header class="top">
            <div>
                <h1>Monthly Budget</h1>
                <p>Manage your grocery spending</p>
            </div>

            <a class="back" href="dashboard.php">← Dashboard</a>
        </header>

        <section class="card">
            <h2>Set Your Monthly Budget</h2>

            <?php if ($message !== ""): ?>
                <div class="message">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <div class="error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label for="budget_amount">Budget amount (₹)</label>

                <input
                    type="number"
                    id="budget_amount"
                    name="budget_amount"
                    min="0.01"
                    step="0.01"
                    value="<?= $budgetAmount > 0 ? htmlspecialchars((string) $budgetAmount) : "" ?>"
                    placeholder="Example: 5000"
                    required
                >

                <button type="submit">Save Monthly Budget</button>
            </form>
        </section>

        <section class="card">
            <h2>This Month's Summary</h2>

            <div class="summary">
                <div class="stat">
                    <p>Monthly Budget</p>
                    <strong><?= formatRupees($budgetAmount) ?></strong>
                </div>

                <div class="stat">
                    <p>Amount Spent</p>
                    <strong><?= formatRupees($spentAmount) ?></strong>
                </div>

                <div class="stat">
                    <p>Remaining</p>
                    <strong class="<?= $remainingAmount < 0 ? 'red' : 'green' ?>">
                        <?= formatRupees($remainingAmount) ?>
                    </strong>
                </div>
            </div>

            <div class="progress-track">
                <div
                    class="progress-fill"
                    style="width: <?= $percentage ?>%; background: <?= $progressColor ?>;"
                ></div>
            </div>

            <p class="hint">
                <?php if ($budgetAmount <= 0): ?>
                    Set a monthly budget to start tracking your spending.
                <?php elseif ($spentAmount >= $budgetAmount): ?>
                    Your monthly budget has been reached or exceeded.
                <?php elseif ($percentage >= 80): ?>
                    Warning: You have used at least 80% of your monthly budget.
                <?php else: ?>
                    You have used <?= number_format($percentage, 1) ?>% of your monthly budget.
                <?php endif; ?>
            </p>
        </section>
    </main>
</body>
</html>
