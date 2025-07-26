<?php
require_once('../../_/conn.php');
$data = [];
if(isset($_GET['api_key'])){
  $api_key = $_GET['api_key'];
  $sql = "SELECT * FROM api_keys WHERE api_key='$api_key'";
  $rs = $conn->query($sql);
  if ($rs && $rs->num_rows > 0) {
    while ($row = $rs->fetch_assoc()) {
      $data = $row;
    }
  }
}
echo json_encode($data);