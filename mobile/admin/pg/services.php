<?php
  include "../conn.php";
  // session_start();
  // Check if the user is logged in
  if (!isset($_SESSION['user_id'])) {
      // If not logged in, redirect to the login page
      header("Location: ./?page=login");
      exit;
  }

  // Handle form submissions for adding, updating, and deleting services
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      if (isset($_POST['add_service'])) {
          $service_name = $_POST['service_name'];
          $description = $_POST['description'];
          // $price = $_POST['price'];
          $insert_query = "INSERT INTO services (service_name, description) VALUES ('$service_name', '$description')";
          mysqli_query($conn, $insert_query);
      } elseif (isset($_POST['update_service'])) {
          $id = $_POST['id'];
          $service_name = $_POST['service_name'];
          $description = $_POST['description'];
          // $price = $_POST['price'];
          $update_query = "UPDATE services SET service_name='$service_name', description='$description' WHERE id='$id'";
          mysqli_query($conn, $update_query);
      } elseif (isset($_POST['delete_service'])) {
          $id = $_POST['id'];
          $delete_query = "DELETE FROM services WHERE id='$id'";
          mysqli_query($conn, $delete_query);
      }
  }

  // Query to get all services
  $all_services_query = "SELECT * FROM services ORDER BY service_name ASC";
  $all_services_result = mysqli_query($conn, $all_services_query);
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<div class="row">
  <div class="col-lg-2">
    <?php include "./inc/sidebar.php" ?>
  </div>
  <div class="col-lg-10">
    <div class="container py-2">
        <h2 class="text-center bg-primary text-light h5 mb-2 py-2">Services Offered</h2>
        <div class="row g-3">
          <div class="row mt-3">
            <div class="col-lg-12">
              <button type="button" class="btn btn-primary float-right mb-3" data-toggle="modal" data-target="#AddServiceModal">Add Service</button>
              
              <table id="services" class="table table-striped">
                <thead>
                  <tr>
                    <th>SN</th>
                    <th>Service Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $sn = 0; 
                    while($service = mysqli_fetch_assoc($all_services_result)): 
                        $sn += 1;
                  ?>
                    <tr>
                      <td><?php echo $sn; ?></td>
                      <td><?php echo $service['service_name']; ?></td>
                      <td><?php echo $service['description']; ?></td>
                      <td>
                        <button class="btn btn-sm btn-warning" onclick="editService(<?php echo $service['id']; ?>, '<?php echo $service['service_name']; ?>', '<?php echo $service['description']; ?>', '<?php echo $service['price']; ?>')">Edit</button>
                        <form method="POST" action="" class="d-inline-block">
                          <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                          <button type="submit" name="delete_service" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this service?')">Delete</button>
                        </form>
                      </td>
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

<!-- Add Service Modal -->
<div class="modal fade" id="AddServiceModal" tabindex="-1" aria-labelledby="AddServiceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="AddServiceModalLabel">Add Service</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="POST" action="">
          <div class="mb-3">
            <label for="service_name" class="form-label">Service Name</label>
            <input type="text" class="form-control" id="service_name" name="service_name" required>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" required></textarea>
          </div>
          <button type="submit" name="add_service" class="btn btn-primary">Add Service</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editServiceModalLabel">Edit Service</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="POST" action="">
          <input type="hidden" id="edit_id" name="id">
          <div class="mb-3">
            <label for="edit_service_name" class="form-label">Service Name</label>
            <input type="text" class="form-control" id="edit_service_name" name="service_name" required>
          </div>
          <div class="mb-3">
            <label for="edit_description" class="form-label">Description</label>
            <textarea class="form-control" id="edit_description" name="description" required></textarea>
          </div>
          <button type="submit" name="update_service" class="btn btn-primary">Update Service</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Initialize DataTables -->
<script>
  $(document).ready(function() {
    $('#services').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true
    });
  });

  function editService(id, service_name, description, price) {
    $('#edit_id').val(id);
    $('#edit_service_name').val(service_name);
    $('#edit_description').val(description);
    $('#edit_price').val(price);
    $('#editServiceModal').modal('show');
  }
</script>