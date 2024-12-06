<?php
session_start();
include 'database.php';

// Calculate totals
$total_profit = $conn->query("SELECT SUM(price) AS total FROM profits")->fetch_assoc()['total'];
$total_expenses = $conn->query("SELECT SUM(price) AS total FROM expenses")->fetch_assoc()['total'];

$revenue_loss = $total_profit - $total_expenses;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Report</title>
</head>
<body>
    <h1>Report Page</h1>

    <h2>Summary</h2>
    <p>Total Profit: <?php echo $total_profit ? $total_profit : 0; ?></p>
    <p>Total Expenses: <?php echo $total_expenses ? $total_expenses : 0; ?></p>
    <p>
        <?php
        if ($revenue_loss >= 0) {
            echo "Total Revenue: $revenue_loss";
        } else {
            echo "Total Loss: " . abs($revenue_loss);
        }
        ?>
    </p>

    <!-- Yearly Summary -->
    <h2>Yearly Summary</h2>
    <form method="GET">
        <label>Choose Year:</label>
        <input type="number" name="year" value="<?php echo date('Y'); ?>" required>
        <button type="submit">Show</button>
    </form>

    <?php
    if (isset($_GET['year'])) {
        $year = $_GET['year'];
        echo "<h3>Year: $year</h3>";

        $monthly_summary = $conn->query("
            SELECT 
                MONTH(record_date) AS month,
                SUM(price) AS monthly_profit 
            FROM profits 
            WHERE YEAR(record_date) = $year 
            GROUP BY MONTH(record_date)
        ");

        echo "<table>
                <tr>
                    <th>Month</th>
                    <th>Monthly Profit</th>
                    <th>Monthly Expenses</th>
                    <th>Balance</th>
                </tr>";

        while ($row = $monthly_summary->fetch_assoc()) {
            $month = $row['month'];
            $monthly_expenses = $conn->query("
                SELECT SUM(price) AS total 
                FROM expenses 
                WHERE YEAR(date) = $year AND MONTH(date) = $month
            ")->fetch_assoc()['total'];

            $balance = $row['monthly_profit'] - $monthly_expenses;
            echo "<tr>
                    <td>$month</td>
                    <td>{$row['monthly_profit']}</td>
                    <td>" . ($monthly_expenses ? $monthly_expenses : 0) . "</td>
                    <td>$balance</td>
                </tr>";
        }

        echo "</table>";
    }
    ?>
</body>
</html>
