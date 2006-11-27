<?php
header("Content-type: application/pdf");
$fp = fopen($filename, "r");
fpassthru($fp);
fclose($fp);
?>