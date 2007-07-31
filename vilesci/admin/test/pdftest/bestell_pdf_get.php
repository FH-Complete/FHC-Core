<?php
	$fp = fopen("./bestell.pdf", "r");
	header("Content-type: application/pdf");
	fpassthru($fp);
	fclose($fp);
?>
