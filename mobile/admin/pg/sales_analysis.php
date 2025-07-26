<?php
    include "../conn.php";
    // session_start();
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // If not logged in, redirect to the login page
        header("Location: ./?page=login");
        exit;
    }

    // Query to get sales summary
    $total_sales_query = "SELECT COUNT(*) as total_sales FROM transactions WHERE status = 'success'";
    $total_sales_result = mysqli_query($conn, $total_sales_query);
    $total_sales = mysqli_fetch_assoc($total_sales_result);

    $total_revenue_query = "SELECT SUM(amount) as total_revenue FROM transactions WHERE status = 'success' AND type != 'wallet funding'";
    $total_revenue_result = mysqli_query($conn, $total_revenue_query);
    $total_revenue = mysqli_fetch_assoc($total_revenue_result);
    
  // Query for top products
    $sql = " SELECT type, COUNT(*) AS sales_count, SUM(amount) AS total_sales FROM transactions WHERE status = 'success' GROUP BY type ORDER BY total_sales DESC";
    $top_products_result = mysqli_query($conn, $sql);

    // Query for top products
    $sql = " SELECT detail, COUNT(*) AS sales_count, SUM(amount) AS total_sales FROM transactions WHERE status = 'success' GROUP BY detail ORDER BY total_sales DESC";
    $products_detail_result = mysqli_query($conn, $sql);
    

    // Check for query error
    if (!$top_products_result) {
      die('Query failed: ' . mysqli_error($conn));
    }

    // Query to get sales trends
    $sales_trends_query = "
    SELECT DATE(created_at) as date, SUM(amount) as total_sales 
    FROM transactions 
    WHERE status = 'successful' 
    GROUP BY DATE(created_at) 
    ORDER BY DATE(created_at)
    ";
    $sales_trends_result = mysqli_query($conn, $sales_trends_query);

    // Arrays to hold the chart data
    $dates = [];
    $total_sales_data = [];

    // Fetch the data and prepare it for the chart
    while ($trend = mysqli_fetch_assoc($sales_trends_result)) {
      $dates[] = '"' . $trend['date'] . '"';
      $total_sales_data[] = $trend['total_sales'];
    }

    

?>


<div class="row">
  <div class="col-lg-2">
  <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
        <h2 class="text-center bg-primary text-light h5 mb-5 py-2">Sales Analysis</h2>
        <div class="row g-3">
          
          <!-- Sales Summary -->
          <div class="col-lg-3">
              <div class="card mb-3 shadow">
                <div class="card-body bg-light text-dark text-center">
                    <h2><span class="text-primary"><?php echo $total_sales['total_sales']; ?></span></h2>
                    <hr>
                    <h5>Total Sales</h5>
                </div>
              </div>
          </div>

          <div class="col-lg-3">
              <div class="card mb-3 shadow">
                <div class="card-body bg-light text-dark text-center">
                    <h2><span class="text-primary">&#8358;<?php echo number_format($total_revenue['total_revenue'], 2); ?></span></h2>
                    <hr>
                    <h5>Total Revenue</h5>
                </div>
              </div>
          </div>

          <div class="col-lg-3">
              <div class="card mb-3 shadow">
                <div class="card-body bg-light text-dark text-center">
                    <h2><span class="text-primary">&#8358;0.00</span></h2>
                    <hr>
                    <h5>Daily Profit</h5>
                </div>
              </div>
          </div>

          <div class="col-lg-3">
              <div class="card mb-3 shadow">
                <div class="card-body bg-light text-dark text-center">
                    <h2><span class="text-primary">&#8358;0.00</span></h2>
                    <hr>
                    <h5>Daily Discount</h5>
                </div>
              </div>
          </div>
          
          <!-- Top Products -->
          <div class="col-lg-12">
              <div class="card mb-3 shadow">
                <div class="card-body bg-light text-dark">
                    <h5 class="text-center">Top Products</h5>
                    <table id="topProducts" class="table table-striped">
                      <thead>
                        <tr>
                          <th>Product</th>
                          <th>Sales Count</th>
                          <th>Total Sales</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php while ($product = mysqli_fetch_assoc($top_products_result)): ?>
                          <tr>
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
          
          <!-- Sales Trends Chart -->
          <div class="card mb-3 shadow d-none">
            <div class="card-body bg-transparent text-dark">
              <h5 class="text-center">Sales Trends</h5>
              <canvas id="salesTrendsChart"></canvas>
            </div>
          </div>
          
          <!-- Product Detail -->
         <div class="col-lg-12">
              <div class="card mb-3 shadow">
                <div class="card-body bg-light text-dark">
                    <h5 class="text-center">Individual Products</h5>
                    <table id="topProducts" class="table table-striped">
                      <thead>
                        <tr>
                          <th>Product</th>
                          <th>Sales Count</th>
                          <th>Total Sales</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php while ($product = mysqli_fetch_assoc($products_detail_result)): ?>
                          <tr>
                            <td><?php echo ucfirst($product['detail']); ?></td>
                            <td><?php echo $product['sales_count']; ?></td>
                            <td>&#8358;<?php echo number_format($product['total_sales'], 2); ?></td>
                          </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                </div>
              </div>
          </div>

        </div>
    </div>
  </div>
</div>

<!-- Initialize DataTables and Charts -->
<script>
  $(document).ready(function() {
    $('#topProducts').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true
    });

    
    $(document).ready(function() {
      // Sales Trends Chart
      var salesTrendsCtx = document.getElementById('salesTrendsChart').getContext('2d');
      var salesTrendsChart = new Chart(salesTrendsCtx, {
        type: 'line',
        data: {
          labels: [<?php echo implode(',', $dates); ?>], // Dates for the X-axis
          datasets: [{
            label: 'Total Sales',
            data: [<?php echo implode(',', $total_sales_data); ?>], // Total sales for the Y-axis
            backgroundColor: 'rgba(54, 162, 235, 0.2)', // Light blue
            borderColor: 'rgba(54, 162, 235, 1)', // Dark blue
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'top'
            },
            tooltip: {
              callbacks: {
                label: function(tooltipItem) {
                  return tooltipItem.label + ': ' + tooltipItem.raw.toLocaleString('en-US', { style: 'currency', currency: 'NGN' });
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return '₦' + value.toLocaleString(); // Formatting as currency
                }
              }
            }
          }
        }
      });
    });

    // Sales by Category Chart
    var salesByCategoryCtx = document.getElementById('salesByCategoryChart').getContext('2d');
    var salesByCategoryChart = new Chart(salesByCategoryCtx, {
      type: 'pie',
      data: {
        labels: [<?php while($category = mysqli_fetch_assoc($sales_by_category_result)) { echo '"' . $category['category'] . '",'; } ?>],
        datasets: [{
          label: 'Total Sales',
          data: [<?php mysqli_data_seek($sales_by_category_result, 0); while($category = mysqli_fetch_assoc($sales_by_category_result)) { echo $category['total_sales'] . ','; } ?>],
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)'
          ],
          borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
          },
          tooltip: {
            callbacks: {
              label: function(tooltipItem) {
                return tooltipItem.label + ': ' + tooltipItem.raw.toLocaleString('en-US', { style: 'currency', currency: 'NGN' });
              }
            }
          }
        }
      }
    });

    // Sales by Region Chart
    var salesByRegionCtx = document.getElementById('salesByRegionChart').getContext('2d');
    var salesByRegionChart = new Chart(salesByRegionCtx, {
      type: 'bar',
      data: {
        labels: [<?php while($region = mysqli_fetch_assoc($sales_by_region_result)) { echo '"' . $region['region'] . '",'; } ?>],
        datasets: [{
          label: 'Total Sales',
          data: [<?php mysqli_data_seek($sales_by_region_result, 0); while($region = mysqli_fetch_assoc($sales_by_region_result)) { echo $region['total_sales'] . ','; } ?>],
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        }]
      }, 
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  });
</script>


