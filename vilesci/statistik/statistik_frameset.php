<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN">
<html lang="de_AT">

<head>
	<title>VileSci</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css" />
</head>

<frameset rows="35%,*">
  	<frame src="statistik.php?statistik_kurzbz=<?php echo $_GET['statistik_kurzbz'];?>" name="uebersicht_statistik" frameborder="0" />
  	<frame src="#empty" name="detail_statistik" frameborder="0" />
	<noframes>
		<body bgcolor="#FFFFFF">
			This application works only with a frames-enabled browser.<br />
		</body>
	</noframes>
</frameset>

</html>