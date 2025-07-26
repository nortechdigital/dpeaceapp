<?php
include "../conn.php";

if (!isset($_SESSION['user_id'])) {
        header("Location: ./?page=login");
        exit;
    }

// Helper function to get summary for a date range
function get_summary($conn, $start_date, $end_date) {
    $summary = [
        'total_sales' => 0,
        'total_revenue' => 0.00,
        'total_discount' => 0.00
    ];
    $sales_query = "SELECT COUNT(*) as total_sales FROM transactions WHERE status = 'success' AND type NOT IN ('wallet topup', 'wallet deduction') AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
    $sales_result = mysqli_query($conn, $sales_query);
    if ($sales_result) {
        $row = mysqli_fetch_assoc($sales_result);
        $summary['total_sales'] = $row['total_sales'] ?? 0;
    }
    $revenue_query = "SELECT SUM(amount) as total_revenue FROM transactions WHERE status = 'success' AND type NOT IN ('wallet topup', 'wallet deduction') AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
    $revenue_result = mysqli_query($conn, $revenue_query);
    if ($revenue_result) {
        $row = mysqli_fetch_assoc($revenue_result);
        $summary['total_revenue'] = $row['total_revenue'] ?? 0.00;
    }

	$wallet_profit_query = "SELECT SUM(wallet_profit) AS total_profit FROM transactions WHERE status = 'SUCCESS' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
    $wallet_profit_result = mysqli_query($conn, $wallet_profit_query);
	if ($wallet_profit_result) {
       $row = mysqli_fetch_assoc($wallet_profit_result);
       $summary['total_profit'] = $row['total_profit'] ?? 0.00;
    }
print_r($wallet_profit);
    $discount_query = "SELECT SUM(cust_discount) as total_discount FROM transactions WHERE status = 'success' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
    $discount_result = mysqli_query($conn, $discount_query);
    if ($discount_result) {
        $row = mysqli_fetch_assoc($discount_result);
        $summary['total_discount'] = $row['total_discount'] ?? 0.00;
    }
    return $summary;
}

$periods = [
    'Today' => [date('Y-m-d'), date('Y-m-d')],
    'Yesterday' => [date('Y-m-d', strtotime('-1 days')), date('Y-m-d')],
    'Last 7 Days' => [date('Y-m-d', strtotime('-6 days')), date('Y-m-d')],
    'Last 14 Days' => [date('Y-m-d', strtotime('-13 days')), date('Y-m-d')],
    'Last 21 Days' => [date('Y-m-d', strtotime('-20 days')), date('Y-m-d')],
    'Last 28 Days' => [date('Y-m-d', strtotime('-27 days')), date('Y-m-d')],
    'Last 2 Months' => [date('Y-m-d', strtotime('-59 days')), date('Y-m-d')],
    'Last 3 Months' => [date('Y-m-d', strtotime('-89 days')), date('Y-m-d')],
    'Last 4 Months' => [date('Y-m-d', strtotime('-119 days')), date('Y-m-d')],
    'Last 5 Months' => [date('Y-m-d', strtotime('-149 days')), date('Y-m-d')],
    'Last 6 Months' => [date('Y-m-d', strtotime('-179 days')), date('Y-m-d')],
    'Last 1 Year' => [date('Y-m-d', strtotime('-1 year')), date('Y-m-d')],
];
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<div class="row">
  <div class="col-lg-2">
  <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
        <h2 class="text-center bg-primary text-light h5 mb-5 py-2">Sales Summary</h2>
        <div class="row g-3">
          <div class="accordion" id="summaryAccordion">
            <?php $i = 0; foreach ($periods as $label => $range): $i++;
              $summary = get_summary($conn, $range[0], $range[1]);
            ?>
              <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?php echo $i; ?>">
                  <button class="accordion-button <?php if($i>1) echo 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $i; ?>" aria-expanded="<?php echo $i==1?'true':'false'; ?>" aria-controls="collapse<?php echo $i; ?>">
                    <?php echo $label; ?> 
                  </button>
                </h2>
                <div id="collapse<?php echo $i; ?>" class="accordion-collapse collapse <?php if($i==1) echo 'show'; ?>" aria-labelledby="heading<?php echo $i; ?>" data-bs-parent="#summaryAccordion">
                  <div class="accordion-body">
                      <table class="table table-bordered">
                      <thead>
                        <tr>
                        <th>Metric</th>
                        <th>Value</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                        <td><strong>Total Sales</strong></td>
                        <td><strong><?php echo $summary['total_sales']; ?></strong></td>
                        </tr>
                        <tr>
                        <td><strong>Total Revenue</strong></td>
                        <td><strong>₦<?php echo number_format($summary['total_revenue'], 2); ?></strong></td>
                        </tr>
                        <tr>
                        <td><strong>Total Discounts Issued</strong></td>
                        <td><strong>₦<?php echo number_format($summary['total_discount'], 2); ?></strong></td>
                        </tr>
                    	<tr>
                        <td><strong>Total Wallet Profit</strong></td>
                        <td><strong>₦<?php echo number_format($summary['total_profit'], 2); ?></strong></td>
                        </tr>
                      </tbody>
                      </table>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
    </div>
  </div>
</div>
            
            
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
