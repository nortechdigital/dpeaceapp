<?php
$cron = fopen("cron.txt", "w") or die("Unable to open file!");
$txt = date("Y-m-d H:i:s A") . "\n";
fwrite($cron, $txt);
fclose($cron);
?>