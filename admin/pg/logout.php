<?php
session_start();
session_unset();
session_destroy();
header("Location: ../?page=admin_login");
exit();
?>