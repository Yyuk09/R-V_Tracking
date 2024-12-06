<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_item'])) {
    $item_id = trim($_POST['item_id']);
    $item_name = trim($_POST['item_name']);

    // Validate input
    if (empty($item_id) || empty($item_name)) {
        echo "Item ID and Item Name are required.";
        exit;
    }

    // Check for duplicates
    $stmt_check = $conn->prepare("SELECT * FROM items WHERE item_id = ? OR item_name = ?");
    $stmt_check->bind_param("ss", $item_id, $item_name);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        echo "Error: Item ID or Item Name already exists.";
        $stmt_check->close();
        exit;
    }

    $stmt_check->close();

    // Insert new item
    $stmt_insert = $conn->prepare("INSERT INTO items (item_id, item_name) VALUES (?, ?)");
    $stmt_insert->bind_param("ss", $item_id, $item_name);

    if ($stmt_insert->execute()) {
        echo "Item registered successfully!";
    } else {
        echo "Error: " . $stmt_insert->error;
    }

    $stmt_insert->close();
    }

    if (isset($_POST['record_expense'])) {
        // Record expense
        $date = $_POST['date'];
        $item_id = $_POST['item_id'];
        $price = $_POST['price'];
        $recorded_by = $_SESSION['username']; // Detect from login

        $insert_expense = $conn->prepare("INSERT INTO expenses (date, item_id, price, recorded_by) VALUES (?, ?, ?, ?)");
        $insert_expense->bind_param("ssds", $date, $item_id, $price, $recorded_by);
        $insert_expense->execute();
        $success = "Expense recorded successfully.";
    }

    if (isset($_POST['delete_expense'])) {
        // Delete expense
        $expense_id = $_POST['expense_id'];
        $delete_expense = $conn->prepare("DELETE FROM expenses WHERE id = ?");
        $delete_expense->bind_param("i", $expense_id);
        $delete_expense->execute();
        $success = "Expense deleted successfully.";
    }

// Calculate totals
$total_expenses = $conn->query("SELECT SUM(price) AS total FROM expenses")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Expenses</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }
        .totals { margin-top: 20px; font-weight: bold; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Expenses Page</h1>

    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>

    <h1>Register Item</h1>
    <form method="POST">
        <label>Item ID:</label>
        <input type="text" name="item_id" required>
        <label>Item Name:</label>
        <input type="text" name="item_name" required>
        <button type="submit" name="register_item">Save</button>
    </form>

    <!-- Record Expense Section -->
    <form method="POST">
        <h2>Record Expense</h2>
        <label>Date:</label>
        <input type="date" name="date" required>
        <label>Item Name:</label>
        <select name="item_id" required>
            <option value="">-- Select Item --</option>
            <?php
            $items = $conn->query("SELECT * FROM items");
            while ($item = $items->fetch_assoc()) {
                echo "<option value='{$item['id']}'>{$item['item_name']}</option>";
            }
            ?>
        </select>
        <label>Price:</label>
        <input type="number" step="0.01" name="price" required>
        <button type="submit" name="record_expense">Save</button>
    </form>

    <!-- Display Records -->
    <h2>Expenses Table</h2>
    <table>
        <tr>
            <th>Date</th>
            <th>Item Name</th>
            <th>Price</th>
            <th>Recorded By</th>
            <th>Action</th>
        </tr>
        <?php
        $expenses = $conn->query("SELECT e.id, e.date, i.item_name, e.price, e.recorded_by FROM expenses e JOIN items i ON e.item_id = i.id");
        while ($expense = $expenses->fetch_assoc()) {
            echo "<tr>
                    <td>{$expense['date']}</td>
                    <td>{$expense['item_name']}</td>
                    <td>{$expense['price']}</td>
                    <td>{$expense['recorded_by']}</td>
                    <td>
                        <form method='POST' style='display:inline;'>
                            <input type='hidden' name='expense_id' value='{$expense['id']}'>
                            <button type='submit' name='delete_expense'>Delete</button>
                        </form>
                    </td>
                </tr>";
        }
        ?>
    </table>

    <!-- Total Expenses -->
    <div class="totals">
        <p>Total Expenses: <?php echo number_format($total_expenses, 2); ?></p>

        <form method="POST">
            <label>Choose Month:</label>
            <input type="month" name="month">
            <button type="submit" name="show_monthly">Show Monthly Total</button>
        </form>

        <?php if (isset($chosen_month)) echo "<p>Monthly Total for $chosen_month: RM" . number_format($total_monthly_expenses, 2) . "</p>"; ?>

        <form method="POST">
            <label>Choose Start Date:</label>
            <input type="date" name="start_date">
            <button type="submit" name="show_weekly">Show Weekly Total</button>
        </form>

        <?php if (isset($weekly_range)) echo "<p>Weekly Total ($weekly_range): RM" . number_format($total_weekly_expenses, 2) . "</p>"; ?>
    </div>
</body>
</html>
