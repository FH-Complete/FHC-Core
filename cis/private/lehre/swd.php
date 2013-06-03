<?php
$url = "http://xgnd.bsz-bw.de/";
$zielfeld = "kontrollschlagwoerter";
$url = $url."?zielfeld=".$zielfeld;
header('Content-Type: text/html; charset=utf-8');
print file_get_contents($url);
?>

