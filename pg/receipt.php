<?php
$id = isset($_GET['id']) ? test_input($_GET['id']) : null;
$transaction = []; $elec = 0;

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $transaction = $result->fetch_assoc();
        $fullname = $transaction['fullname'];
        $phone_number = $transaction['phone_number'];
        $product_description = $transaction['product_description'];
        $amount = $transaction['amount'];
        $created_at = $transaction['created_at'];
        $request_id = $transaction['transaction_ref'];
        $status = $transaction['status'];
        $elec = $transaction['type'] == 'Electricity Subscription' ? 1 : 0;
    } else {
        $fullname = '--Customer Name--';
        $phone_number = '--Customer Phone--';
        $product_description = '--Product Description--';
        $amount = '0.00';
        $created_at = date('d-m-y');
        $request_id = '--Request ID--';
        $status = 'Transaction Not Found!';
    }
    $stmt->close();
} else {
    $fullname = '--Customer Name--';
    $phone_number = '--Customer Phone--';
    $product_description = '--Product Description--';
    $amount = '0.00';
    $created_at = date('d-m-y');
    $request_id = '--Request ID--';
    $status = 'Invalid Request ID!';
}
?>

<!-- Include Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<div class="row">
  <div class="col-lg">
    <div class="container">
      <div class="col-lg-6 mx-auto">
        <div class="card mb-3">
          <div class="card-body text-center" id="receiptContent">
            <img src="https://dpeaceapp.com/img/dpeace-app.png" alt="DPeace Logo" style="width: 100px; margin-bottom: 20px;">
            <h5 class="text-center">Transaction Receipt</h5>
            <?php
              $statusClass = 'text-secondary';
              if (strtolower($status) === 'success') {
                $statusClass = 'text-success';
              } elseif (strtolower($status) === 'pending') {
                $statusClass = 'text-warning'; 
              } elseif (strtolower($status) === 'failed') {
                $statusClass = 'text-danger';
              }
            ?>
            <h4 class="text-center <?php echo $statusClass; ?>"><?php echo ucfirst($status) ?></h4>
            <p class="small text-center fst-italic"><small><strong>Request ID:</strong> <?= $request_id ?></small></p>
            <hr>
            <table class="table text-start">
             <tr>
        		<th>Date</th>
        		<td><?= $created_at ?></td>
    		</tr>
    		<tr>
       		 	<th>Description</th>
        	 	<td><?= $product_description ?></td>
    		</tr>
    		<tr>
        		<th>Customer Name</th>
       		 	<td><?= $elec == 1 ? $transaction['customer_name'] : $fullname ?></td>
    		</tr>
            <?php if ($elec == 1): ?>
            <?php if (!empty($transaction['customer_address'])): ?>
            <tr>
            	<th>Customer Address</th>
        	 	<td><?= $transaction['customer_address'] ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($transaction['token'])): ?>
            <tr>
              <th>Token</th>
              <td><?= $transaction['token'] ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($transaction['unit_purchased'])): ?>
            <tr>
              <th>Unit Purchased</th>
              <td><?= $transaction['unit_purchased'] ?></td>
            </tr>
            <?php endif; ?>
            <tr>
            	<th>Meter/Account Number</th>
        	 	<td><?= $transaction['smartcard_number'] ?></td>
            </tr>
            <?php endif; ?>
    		<tr>
       			<th>Phone Number</th>
        		<td><?= $phone_number ?></td>
    		</tr>
    		<tr>
        		<th>Amount</th>
        		<td>&#8358;<?= number_format($amount, 2) ?></td>
    		</tr>
         
              </table>
            &nbsp;
         
              <p class="text-center text-primary h5">Thank you for choosing DPeace App!</p>
              <hr>
            </div>
            

          <!-- Buttons -->
          <div class="form-group my-3 text-center d-print-none">
            <button onclick="downloadAsPDF()" class="btn btn-secondary mt-2">Download</button>
            <a href="?page=dashboard" class="btn btn-primary mt-2">Close</a>
              
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript Functions -->
<script>
  function downloadAsPDF() {
    const { jsPDF } = window.jspdf;
    const receipt = document.getElementById('receiptContent');

    html2canvas(receipt, { scale: 2 }).then(canvas => {
      const imgData = canvas.toDataURL('image/png');
      const pdf = new jsPDF('p', 'mm', 'a4');

      const pageWidth = pdf.internal.pageSize.getWidth();   // 210mm
      const pageHeight = pdf.internal.pageSize.getHeight(); // 297mm

      const imgProps = pdf.getImageProperties(imgData);
      const imgWidth = imgProps.width;
      const imgHeight = imgProps.height;
      const imgRatio = imgWidth / imgHeight;
      const pageRatio = pageWidth / pageHeight;

      let finalWidth, finalHeight;

      if (imgRatio > pageRatio) {
        // Image is wider, fit by width
        finalWidth = pageWidth;
        finalHeight = finalWidth / imgRatio;
      } else {
        // Image is taller, fit by height
        finalHeight = pageHeight;
        finalWidth = finalHeight * imgRatio;
      }

      const x = (pageWidth - finalWidth) / 2;
      const y = (pageHeight - finalHeight) / 2;

      pdf.addImage(imgData, 'PNG', x, y, finalWidth, finalHeight);
      pdf.save('transaction_receipt.pdf');
    });
  }
</script>
