<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Expenses and Revenue Tracking</title>
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
    <button onclick="location.href='expenses.php'">Expenses</button>
    <button onclick="location.href='profit.php'">Profit</button>
    <button onclick="location.href='report.php'">Report</button>
</body>
</html>
