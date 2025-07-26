<?php
// filepath: /c:/xampp/htdocs/dpeace/admin/pg/providers.php
include "../conn.php";
// session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./?page=login");
    exit;
}

// Handle form submission for adding a provider
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_provider'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];

    // Handle file upload
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($_FILES["logo"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["logo"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $error_message = "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        $error_message = "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["logo"]["size"] > 500000) {
        $error_message = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $error_message = "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            $logo = $target_file;
            $query = "INSERT INTO providers (name, logo, description) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $name, $logo, $description);
            if ($stmt->execute()) {
                $success_message = "Provider details submitted successfully.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Sorry, there was an error uploading your file.";
        }
    }
}

// Handle form submission for editing a provider
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_provider'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $logo = $_POST['existing_logo'];

    // Handle file upload if a new file is provided
    if (!empty($_FILES["logo"]["name"])) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES["logo"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["logo"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $error_message = "File is not an image.";
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            $error_message = "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["logo"]["size"] > 500000) {
            $error_message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $error_message = "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
                $logo = $target_file;
            } else {
                $error_message = "Sorry, there was an error uploading your file.";
            }
        }
    }

    // Update provider details in the database
    $query = "UPDATE providers SET name = ?, logo = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $name, $logo, $description, $id);
    if ($stmt->execute()) {
        $success_message = "Provider details updated successfully.";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle form submission for deleting a provider
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_provider'])) {
    $id = $_POST['id'];

    // Delete provider from the database
    $query = "DELETE FROM providers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success_message = "Provider deleted successfully.";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch providers from the database
$query = "SELECT * FROM providers";
$result = $conn->query($query);

if (!$result) {
    die("Error executing query: " . $conn->error);
}

$providers = $result->fetch_all(MYSQLI_ASSOC);
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" defer></script>
  <script>
    $(document).ready(function() {
        $('#providersTable').DataTable();
    });
  </script>
</head>
<body>
<div class="row">
    <div class="col-lg-2">
        <?php include "./inc/sidebar.php" ?>
    </div>
    <div class="col-lg-10">
    <div class="container py-2">
            <h2 class="text-center bg-primary text-light h5 mb-5">Network Providers List</h2>
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <div class="card mb-3 shadow">
                <div class="card-body bg-light text-dark">
                    <table id="providersTable" class="table table-striped">
                      <thead>
                        <tr>
                          <th>SN</th>
                          <th>Provider</th>
                          <th>Logo</th>
                          <th>Description</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($providers as $index => $provider): ?>
                          <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($provider['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><img src="<?php echo htmlspecialchars($provider['logo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($provider['name'], ENT_QUOTES, 'UTF-8'); ?> Logo" width="50"></td>
                            <td><?php echo htmlspecialchars($provider['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                              <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editProviderModal<?php echo $provider['id']; ?>">Edit</button>
                              <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteProviderModal<?php echo $provider['id']; ?>">Delete</button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="container py-2">
            <h2 class="text-center bg-primary text-light h5 mb-5">Add Provider</h2>
            
            <form action="" method="post" enctype="multipart/form-data" class="card p-4 mb-5">
                <input type="hidden" name="add_provider" value="1">
                <div class="mb-3">
                    <label for="name" class="form-label">Provider Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="logo" class="form-label">Logo</label>
                    <input type="file" name="logo" id="logo" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" required></textarea>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
        
    </div>
</div>

<!-- Edit Provider Modal -->
<div class="modal fade" id="editProviderModal<?php echo $provider['id']; ?>" tabindex="-1" aria-labelledby="editProviderModalLabel<?php echo $provider['id']; ?>" aria-hidden="true">
    <div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" id="editProviderModalLabel<?php echo $provider['id']; ?>">Edit Provider</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="modal-body">
        <input type="hidden" name="edit_provider" value="1">
        <input type="hidden" name="id" value="<?php echo $provider['id']; ?>">
        <input type="hidden" name="existing_logo" value="<?php echo $provider['logo']; ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Provider Name</label>
            <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($provider['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="mb-3">
            <label for="logo" class="form-label">Logo</label>
            <input type="file" name="logo" id="logo" class="form-control">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" required><?php echo htmlspecialchars($provider['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
    </div>
    </div>
</div>

<!-- Delete Provider Modal -->
<div class="modal fade" id="deleteProviderModal<?php echo $provider['id']; ?>" tabindex="-1" aria-labelledby="deleteProviderModalLabel<?php echo $provider['id']; ?>" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title" id="deleteProviderModalLabel<?php echo $provider['id']; ?>">Delete Provider</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form action="" method="post">
    <div class="modal-body">
    <input type="hidden" name="delete_provider" value="1">
    <input type="hidden" name="id" value="<?php echo $provider['id']; ?>">
    <p>Are you sure you want to delete this provider?</p>
    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-danger">Delete</button>
    </div>
</form>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>