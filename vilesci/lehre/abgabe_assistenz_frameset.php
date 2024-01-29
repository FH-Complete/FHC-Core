<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN">
<html lang="de_AT">

<head>
	<title>Bachelor-/Diplomarbeitsabgabe - Assistenz</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css" />
</head>
<?php
if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
else
	$stg_kz='0';

if(!is_numeric($stg_kz) && $stg_kz!='')
	$stg_kz='0';
echo "
<frameset rows='400,*'>
  	<frame src='abgabe_assistenz.php?stg_kz=".$stg_kz."' id='uebersicht' name='uebersicht' frameborder='0' />
  	<frame src='abgabe_assistenz_details.php' id='al_detail' name='al_detail' frameborder='0' />
	<noframes>
		<body bgcolor='#FFFFFF'>
			This application works only with a frames-enabled browser.<br />
			<a href='main.php'>Use without frames</a>
		</body>
	</noframes>
</frameset>";
?>
</html>