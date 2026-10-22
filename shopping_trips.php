
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
    $tripName = trim($_POST["trip_name"] ?? "");
    $tripDate = $_POST["trip_date"] ?? "";
    $tripBudget = filter_input(INPUT_POST, "trip_budget", FILTER_VALIDATE_FLOAT);

    $dateObject = DateTime::createFromFormat("!Y-m-d", $tripDate);
    $validDate = $dateObject && $dateObject->format("Y-m-d") === $tripDate;

    if ($tripName === "" || strlen($tripName) > 100) {
        $error = "Enter a trip name containing up to 100 characters.";
    } elseif (!$validDate) {
        $error = "Please select a valid trip date.";
    } elseif ($tripBudget === false || $tripBudget === null || $tripBudget <= 0) {
        $error = "Enter a trip budget greater than ₹0.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO shopping_trips (user_id, trip_name, trip_date, budget_amount)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param("issd", $userId, $tripName, $tripDate, $tripBudget);

        if ($stmt->execute()) {
            $message = "Shopping trip created successfully.";
        } else {
            $error = "Unable to create the shopping trip. Please try again.";
        }

        $stmt->close();
    }
}

$stmt = $conn->prepare(
    "SELECT
        st.id,
        st.trip_name,
        st.trip_date,
        st.budget_amount,
        COALESCE(SUM(fi.purchase_price * fi.quantity), 0) AS total_spent
     FROM shopping_trips st
     LEFT JOIN food_items fi
        ON fi.trip_id = st.id
        AND fi.user_id = st.user_id
     WHERE st.user_id = ?
     GROUP BY st.id, st.trip_name, st.trip_date, st.budget_amount
     ORDER BY st.trip_date DESC, st.id DESC"
);

$stmt->bind_param("i", $userId);
$stmt->execute();
$trips = $stmt->get_result();

function rupees($amount) {
    return "₹" . number_format((float) $amount, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Trips - Smart Pantry</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            padding: 30px 16px;
            background: #eef2e8;
            color: #273329;
            font-family: Arial, Helvetica, sans-serif;
        }

        .container {
            max-width: 850px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        h1 {
            color: #248733;
            font-size: 27px;
        }

        .subtitle {
            color: #718071;
            font-size: 14px;
            margin-top: 7px;
        }

        a {
            color: #248733;
            text-decoration: none;
            font-weight: bold;
        }

        .card {
            background: #ffffff;
            border-radius: 17px;
            padding: 24px;
            margin-bottom: 22px;
            box-shadow: 0 5px 20px rgba(35, 65, 35, 0.06);
        }

        h2 {
            font-size: 19px;
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #dce4d8;
            border-radius: 9px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 13px;
            background: #36a842;
            color: white;
            border: none;
            border-radius: 9px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #2d9238;
        }

        .message, .error {
            padding: 12px;
            border-radius: 9px;
            margin-bottom: 16px;
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

        .trip {
            border: 1px solid #e5eadf;
            border-radius: 13px;
            padding: 18px;
            margin-top: 14px;
        }

        .trip-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 12px;
            margin-bottom: 15px;
        }

        .trip h3 {
            font-size: 16px;
            margin-bottom: 6px;
            overflow-wrap: anywhere;
        }

        .date {
            color: #718071;
            font-size: 12px;
        }

        .trip-limit {
            background: #edf7eb;
            color: #248733;
            border-radius: 8px;
            padding: 7px 10px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }

        .numbers {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .number {
            background: #f5f8f1;
            padding: 12px;
            border-radius: 9px;
            overflow-wrap: anywhere;
        }

        .number p {
            color: #718071;
            font-size: 12px;
            margin-bottom: 7px;
        }

        .number strong {
            font-size: 16px;
        }

        .green {
            color: #248733;
        }

        .red {
            color: #c0392b;
        }

        .empty {
            text-align: center;
            padding: 25px 10px;
            color: #718071;
            line-height: 1.7;
        }

        @media (max-width: 540px) {
            .header {
                align-items: start;
            }

            h1 {
                font-size: 22px;
            }

            .card {
                padding: 18px;
            }

            .numbers {
                grid-template-columns: 1fr;
            }

            .trip-header {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <header class="header">
            <div>
                <h1>Shopping Trips</h1>
                <p class="subtitle">Plan your grocery shopping budget</p>
            </div>
            <a href="dashboard.php">← Dashboard</a>
        </header>

        <section class="card">
            <h2>Create a Shopping Trip</h2>

            <?php if ($message !== ""): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="trip_name">Trip name</label>
                <input
                    type="text"
                    id="trip_name"
                    name="trip_name"
                    maxlength="100"
                    placeholder="Example: Weekend Grocery Shopping"
                    required
                >

                <label for="trip_date">Shopping date</label>
                <input
                    type="date"
                    id="trip_date"
                    name="trip_date"
                    value="<?= date("Y-m-d") ?>"
                    required
                >

                <label for="trip_budget">Trip budget (₹)</label>
                <input
                    type="number"
                    id="trip_budget"
                    name="trip_budget"
                    min="0.01"
                    step="0.01"
                    placeholder="Example: 1500"
                    required
                >

                <button type="submit">Create Shopping Trip</button>
            </form>
        </section>

        <section class="card">
            <h2>Your Shopping Trips</h2>

            <?php if ($trips->num_rows === 0): ?>
                <div class="empty">
                    <p>No shopping trips yet.</p>
                    <p>Create your first trip above to start tracking your budget.</p>
                </div>
            <?php else: ?>
                <?php while ($trip = $trips->fetch_assoc()): ?>
                    <?php
                    $budget = (float) $trip["budget_amount"];
                    $spent = (float) $trip["total_spent"];
                    $remaining = $budget - $spent;
                    ?>
                    <article class="trip">
                        <div class="trip-header">
                            <div>
                                <h3><?= htmlspecialchars($trip["trip_name"]) ?></h3>
                                <p class="date">
                                    <?= htmlspecialchars($trip["trip_date"]) ?>
                                </p>
                            </div>
                            <span class="trip-limit">
                                Limit: <?= rupees($budget) ?>
                            </span>
                        </div>

                        <div class="numbers">
                            <div class="number">
                                <p>Spent</p>
                                <strong><?= rupees($spent) ?></strong>
                            </div>
                            <div class="number">
                                <p>Remaining</p>
                                <strong class="<?= $remaining < 0 ? 'red' : 'green' ?>">
                                    <?= rupees($remaining) ?>
                                </strong>
                            </div>
                            <div class="number">
                                <p>Trip status</p>
                                <strong class="<?= $remaining < 0 ? 'red' : 'green' ?>">
                                    <?= $remaining < 0 ? 'Over budget' : 'Within budget' ?>
                                </strong>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
