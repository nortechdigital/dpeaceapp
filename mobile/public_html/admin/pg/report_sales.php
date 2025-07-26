<?php
include "../conn.php";
//session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: ./?page=login");
    exit;
}

// Get selected date range filter from user input
$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : 'all'; // Default to 'all'

// Build SQL based on selected date range
switch ($date_range) {
    case 'today':
        $date_condition = "DATE(transactions.created_at) = CURDATE()";
        break;
    case 'one_week_ago':
        $date_condition = "DATE(transactions.created_at) >= CURDATE() - INTERVAL 1 WEEK";
        break;
    case 'two_weeks_ago':
        $date_condition = "DATE(transactions.created_at) >= CURDATE() - INTERVAL 2 WEEK";
        break;
    case 'one_month_ago':
        $date_condition = "DATE(transactions.created_at) >= CURDATE() - INTERVAL 1 MONTH";
        break;
    case 'two_months_ago':
        $date_condition = "DATE(transactions.created_at) >= CURDATE() - INTERVAL 2 MONTH";
        break;
    case 'three_months_ago':
        $date_condition = "DATE(transactions.created_at) >= CURDATE() - INTERVAL 3 MONTH";
        break;
    case 'last_month':
        $date_condition = "YEAR(transactions.created_at) = YEAR(CURDATE()) AND MONTH(transactions.created_at) = MONTH(CURDATE()) - 1";
        break;
    case 'last_year':
        $date_condition = "YEAR(transactions.created_at) = YEAR(CURDATE()) - 1";
        break;
    default:
        $date_condition = "1"; // No date condition (all records)
        break;
}

// Query for All Sales based on the selected date range
$sql = "SELECT type, COUNT(*) AS sales_count, SUM(amount) AS total_sales 
        FROM transactions 
        WHERE status = 'success' AND $date_condition 
        GROUP BY type 
        ORDER BY total_sales DESC";
$sales_result = mysqli_query($conn, $sql);
?>

<div class="container py-2">
  <h2 class="text-center bg-primary text-light h5 mb-2 py-2">Sales Report</h2>
  <div class="row mb-3">
    <div class="col-lg-12">
      <button onclick="history.back()" class="btn btn-primary mb-3">Back</button>
      <!-- Date Range Filter -->
      <form method="GET">
        <input type="hidden" name="page" value="report_sales">
        <label for="date_range" class="mr-2">Filter by Date:</label>
        <select name="date_range" id="date_range" class="form-control d-inline w-auto" onchange="this.form.submit()">
          <option value="all" <?= $date_range === 'all' ? 'selected' : '' ?>>All Time</option>
          <option value="today" <?= $date_range === 'today' ? 'selected' : '' ?>>Today</option>
          <option value="one_week_ago" <?= $date_range === 'one_week_ago' ? 'selected' : '' ?>>One Week Ago</option>
          <option value="two_weeks_ago" <?= $date_range === 'two_weeks_ago' ? 'selected' : '' ?>>Two Weeks Ago</option>
          <option value="one_month_ago" <?= $date_range === 'one_month_ago' ? 'selected' : '' ?>>One Month Ago</option>
          <option value="two_months_ago" <?= $date_range === 'two_months_ago' ? 'selected' : '' ?>>Two Months Ago</option>
          <option value="three_months_ago" <?= $date_range === 'three_months_ago' ? 'selected' : '' ?>>Three Months Ago</option>
          <option value="last_month" <?= $date_range === 'last_month' ? 'selected' : '' ?>>Last Month</option>
          <option value="last_year" <?= $date_range === 'last_year' ? 'selected' : '' ?>>Last Year</option>
        </select>
      </form>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <table id="sales" class="table table-striped">
        <thead>
          <tr>
            <th>SN</th>
            <th>Product</th>
            <th>Sales Count</th>
            <th>Total Sales</th>
            <!-- <th>Date</th> -->
          </tr>
        </thead>
        <tbody>
          <?php $sn=0; while ($product = mysqli_fetch_assoc($sales_result)): $sn += 1; ?>
            <tr>
              <td><?php echo $sn; ?></td>
              <td><?php echo ucfirst($product['type']); ?></td>
              <td><?php echo $product['sales_count']; ?></td>
              <td>&#8358;<?php echo number_format($product['total_sales'], 2); ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Include DataTables and Buttons plugin -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>

<!-- Initialize DataTables with print and export options -->
<script>
  $(document).ready(function() {
    $('#sales').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "dom": 'Bfrtip',
      "buttons": [
        {
          extend: 'copyHtml5',
          text: 'Copy',
          title: 'Sales Report'
        },
        {
          extend: 'excelHtml5',
          text: 'Export to Excel',
          title: 'Sales Report'
        },
        {
          extend: 'csvHtml5',
          text: 'Export to CSV',
          title: 'Sales Report'
        },
        {
          extend: 'pdfHtml5',
          text: 'Export to PDF',
          title: 'Sales Report'
        },
        {
          extend: 'print',
          text: 'Print',
          title: 'Sales Report'
        }
      ]
    });
  });
</script>
