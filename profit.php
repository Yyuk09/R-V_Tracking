<?php
session_start();
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['record_profit'])) {
        $customer_name = $_POST['customer_name'];
        $record_date = $_POST['record_date'];
        $order_id = $_POST['order_id'];
        $price = $_POST['price'];
        $delivery_address = $_POST['delivery_address'];
        $recorded_by = $_SESSION['username'];

        $query = $conn->prepare("INSERT INTO profits (customer_name, record_date, order_id, price, delivery_address, recorded_by) VALUES (?, ?, ?, ?, ?, ?)");
        $query->bind_param("ssssss", $customer_name, $record_date, $order_id, $price, $delivery_address, $recorded_by);

        if ($query->execute()) {
            $success = "Profit recorded successfully!";
        } else {
            $error = "Error recording profit.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profit Management</title>
</head>
<body>
    <h1>Profit Page</h1>

    <!-- Record Profit Section -->
    <form method="POST">
        <h2>Record Profit</h2>
        <label>Customer Name:</label>
        <input type="text" name="customer_name" required>
        <label>Date:</label>
        <input type="date" name="record_date" required>
        <label>Order ID:</label>
        <input type="text" name="order_id" required>
        <label>Price:</label>
        <input type="number" step="0.01" name="price" required>
        <label>Delivery Address:</label>
        <textarea name="delivery_address" required></textarea>
        <button type="submit" name="record_profit">Save</button>
    </form>

    <!-- Display Profit Records -->
    <h2>Profit Records</h2>
    <table>
        <tr>
            <th>Customer Name</th>
            <th>Date</th>
            <th>Order ID</th>
            <th>Price</th>
            <th>Delivery Address</th>
            <th>Recorded By</th>
            <th>Action</th>
        </tr>
        <?php
        $profits = $conn->query("SELECT * FROM profits");
        while ($profit = $profits->fetch_assoc()) {
            echo "<tr>
                    <td>{$profit['customer_name']}</td>
                    <td>{$profit['record_date']}</td>
                    <td>{$profit['order_id']}</td>
                    <td>{$profit['price']}</td>
                    <td>{$profit['delivery_address']}</td>
                    <td>{$profit['recorded_by']}</td>
                    <td>
                        <button>Edit</button>
                        <button>Delete</button>
                    </td>
                </tr>";
        }
        ?>
    </table>

    <!-- Display Total Profit -->
    <h2>Total Profit</h2>
    <?php
    $total_profit = $conn->query("SELECT SUM(price) AS total FROM profits")->fetch_assoc()['total'];
    echo "<p>Total Profit: " . ($total_profit ? $total_profit : 0) . "</p>";
    ?>
</body>
</html>
