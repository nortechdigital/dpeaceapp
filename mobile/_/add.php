<?php
require 'conn.php';

if ( isset( $_POST['unique'] ) ) {
  $uniques = explode(',', test_input( $_POST['unique'] ));
  foreach ( $uniques as $unique ) {
    $y = test_input( $_POST[$unique] );
    $sql = "SELECT * FROM $tbl WHERE $unique = '$y'";
    $rs = $conn->query( $sql );
    if ( $rs->num_rows > 0 ) die( header('location: ' . $_SERVER['HTTP_REFERER']) );
  }
}

foreach($_POST as $x => $y) {
  if (!in_array($x, $key_var)) {
    if ($x === 'password') $y = password_hash($y, PASSWORD_BCRYPT);
    if (is_array($y)) {
      $y = json_encode($y);
    } else { if ($x !== 'password') $y = test_input($y); }
    $col[] = $x; $val[] = "'$y'";
    $_SESSION["last_$x"] = $y;
  }
}

$dir = '../img/';
foreach ($_FILES as $x => $arr) {
  $fl_name = mt_rand(1000, 9999) . '_' . basename($arr['name']);
  $fl_ex = pathinfo($fl_name, PATHINFO_EXTENSION);
  $fl_size = $arr['size'];
  $fl_des = $dir . $fl_name;
  if (move_uploaded_file($arr['tmp_name'], $fl_des)) {
    $col[] = $x; $val[] = "'$fl_name'";
  }
}

$sql = "INSERT INTO $tbl (" . implode(', ', $col) . ") VALUES (" . implode(', ', $val) . ")";

if ($conn->query($sql) === TRUE) {
  $_SESSION['last_id'] = $conn->insert_id;
  $msg = ($msg) ? $msg : "New record created successfully";
} else {
  $msg = "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();

if ($return == true) die(header('location: ' . $_SERVER['HTTP_REFERER']));
header('location: ../?page=' . $pg . '&msg=' . $msg);