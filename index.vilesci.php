<?php
if($_SERVER['HTTP_HOST']=='rwawi.technikum-wien.at')
	header('Location: https://vilesci.technikum-wien.at/wawi/index.php');
else
	header('Location: https://vilesci.technikum-wien.at/vilesci/index.php');
?>