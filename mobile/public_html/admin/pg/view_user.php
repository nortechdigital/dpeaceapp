<?php
include "../conn.php";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: ./?page=login");
    exit;
}

// Check if user ID is provided
if (!isset($_GET['id'])) {
    echo "User ID not provided.";
    exit;
}

$user_id = $_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $category = $_POST['category'];

    $update_query = "UPDATE users SET firstname = ?, lastname = ?, email = ?, phone = ?, role = ?, category = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("ssssssi", $firstname, $lastname, $email, $phone, $role, $category, $user_id);
    if (!$stmt->execute()) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    // Redirect to the same page to see the changes
    header("Location: ?page=view_user&id=" . $user_id);
    exit;
}

// Query to get user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows == 0) {
    echo "User not found.";
    exit;
}

$user = $user_result->fetch_assoc();
?>

<!-- Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Changes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to save these changes?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSave">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(event) {
    event.preventDefault();
    var confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    confirmModal.show();
});

document.getElementById('confirmSave').addEventListener('click', function() {
    document.querySelector('form').submit();
});
</script>

<div class="container py-2">
    <h2 class="text-center bg-primary text-light h5 mb-2 py-2">User Details</h2>
    <div class="row g-3">
        <div class="col-lg-12">
            <form method="POST">
                <table class="table table-striped">
                    <tr>
                        <th>First Name</th>
                        <td><input type="text" name="firstname" value="<?php echo $user['firstname']; ?>" class="form-control"></td>
                    </tr>
                    <tr>
                        <th>Last Name</th>
                        <td><input type="text" name="lastname" value="<?php echo $user['lastname']; ?>" class="form-control"></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><input type="email" name="email" value="<?php echo $user['email']; ?>" class="form-control"></td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td><input type="text" name="phone" value="<?php echo $user['phone']; ?>" class="form-control"></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>
                            <select name="role" class="form-control">
                                <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                                <option value="customer_care" <?php echo ($user['role'] == 'customer_care') ? 'selected' : ''; ?>>Customer Care</option>
                                <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td>
                            <select name="category" class="form-control">
                                <option value="subscriber" <?php echo ($user['category'] == 'subscriber') ? 'selected' : ''; ?>>Subscriber</option>
                                <option value="agent" <?php echo ($user['category'] == 'agent') ? 'selected' : ''; ?>>Agent</option>
                                <option value="vendor" <?php echo ($user['category'] == 'vendor') ? 'selected' : ''; ?>>Vendor</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Date Created</th>
                        <td><?php echo $user['created_at']; ?></td>
                    </tr>
                </table>
                <button type="submit" class="btn btn-success">Save Changes</button>
                <a href="index.php?page=users" class="btn btn-primary">Back to Users</a>
            </form>
        </div>
    </div>
</div>
