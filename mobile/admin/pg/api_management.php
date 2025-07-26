<?php
require_once '../_/conn.php';
$users = [];
$result = $conn->query("SELECT id, username FROM users");
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $users[] = $row;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_api'])) {
    $user_id = intval($_POST['user_id']);
    // Check if user already has an API key
    $check = $conn->prepare("SELECT id FROM api_keys WHERE user_id = ?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo '<script>alert("This user already has an API key.")</script>';
    } else {
        // Generate a random API key
        $api_key = bin2hex(random_bytes(16));
        $stmt = $conn->prepare("INSERT INTO api_keys (user_id, api_key, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $user_id, $api_key);
        if ($stmt->execute()) {
            echo '<script>alert("API key created successfully!")</script>';
        } else {
            echo '<script>alert("Error creating API key.")</script>';
        }
        $stmt->close();
    }
    $check->close();
}
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-lg-2">
      <?php include_once './inc/sidebar.php'; ?>
    </div>
    <div class="col-lg-10">
      <div class="d-flex align-items-center bg-primary text-light px-3 py-2 mb-4">
        <h2 class="h5">API Management Console</h2>
        <div class="ms-auto">
          <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#addApiModal">Add New API</button>
        </div>
      </div>
        <div class="table-responsive">
          <table class="table table-all" id="apiKeysTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Username</th>
                <th>API Key</th>
                <th>Created At</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $query = "SELECT ak.id, u.username, ak.api_key, ak.created_at FROM api_keys ak JOIN users u ON ak.user_id = u.id ORDER BY ak.created_at DESC";
              $result = $conn->query($query);
              $i = 1;
              if ($result) {
                while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>{$i}</td>";
                  echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                  echo "<td>
                    <span class='api-key-text' id='api-key-{$row['id']}'>" . htmlspecialchars($row['api_key']) . "</span>
                    <button class='btn btn-sm btn-outline-secondary copy-btn' data-key='api-key-{$row['id']}'>Copy</button>
                  </td>";
                  echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                  echo "</tr>";
                  $i++;
                }
              }
              ?>
            </tbody>
          </table>
        </div>
        <hr>
        <h3 class="h6">Endpoints</h3>
        <ul class="list-group">
          <li class="list-group-item">Airtime: <span class="">https://dpeaceapp.com/api/airtime/</span></li>
          <li class="list-group-item">Data: <span class="">https://dpeaceapp.com/api/data/</span></li>
        </ul>
    </div>
  </div>
</div>

<!-- Add New API Modal -->
<div class="modal fade" id="addApiModal" tabindex="-1" aria-labelledby="addApiModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addApiModalLabel">Add New API</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="">
        <div class="modal-body">
          <div class="mb-3">
            <label for="user_id" class="form-label">User</label>
            <select class="form-select" id="user_id" name="user_id" required>
              <option value="">Select User</option>
              <?php foreach($users as $user): ?>
                <option value="<?= $user['id'] ?>"><?= $user['username'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="add_api" class="btn btn-primary">Add API</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.copy-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var keyId = this.getAttribute('data-key');
        var apiKey = document.getElementById(keyId).innerText;
        navigator.clipboard.writeText(apiKey).then(() => {
          this.innerText = 'Copied!';
          setTimeout(() => { this.innerText = 'Copy'; }, 1200);
        });
      });
    });
  });
</script>