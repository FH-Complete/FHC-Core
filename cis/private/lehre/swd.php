<?php
$url = "http://xgnd.bsz-bw.de/";
$zielfeld = "kontrollschlagwoerter";
$url = $url."?zielfeld=".$zielfeld;
header('Content-Type: text/html; charset=utf-8');
$content = file_get_contents($url);
if($content)
	print $content;
else
	echo "Der Schlagwortdienst ist derzeit nicht erreichbar. Bitte füllen Sie die Schlagwörter manuell aus um den Upload abzuschließen";
?>
