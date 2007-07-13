<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>CIS - FH Technikum Wien</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
</head>

<body id="inhalt">
<?php
	if(isset($_GET['stylesheet']))
	{
		$style = $_GET['stylesheet'];
		setcookie('stylesheet', $style);
		?>
		<script type="text/javascript">
			top.location.href="cis/index.html";
		</script>
		<?php
	}
	else if (isset($_COOKIE['stylesheet']))
		$style = $_COOKIE['stylesheet'];
	else
		$style =null;

	//echo $_COOKIE['stylesheet'];
	$dir = "skin/styles/";
	$files = scandir($dir);

	$erg = count($files);
	$j = 0;

	for($i = 0; $i < $erg; $i++)
	{
		if(is_dir($dir.$files[$i]) && $files[$i] != "." && $files[$i] != ".." && $files[$i] != ".svn")
		{
			$files_erg[$j] = $files[$i];
			$j++;
		}
	}

	echo "<h3> Wählen Sie Ihr persönliches Layout! </h3>";
	echo "<table>";

	for($i = 0; $i <count($files_erg); $i++)
	{
		if($style == $files_erg[$i])
			echo '<tr bgcolor="#EEEEEE">';
		else
			echo "<tr>";
		echo "<td>";
		echo "<a class='Item' href='?stylesheet=".$files_erg[$i]."'><img id='layout' src='skin/styles/".$files_erg[$i]."/screenshot.jpg' width='300' /></a>";
		echo "</td><td>";
		readfile('skin/styles/'.$files_erg[$i].'/description.txt');
		echo "</td>";
	}
	echo "</tr>";
	echo "</table>";

?>
</body>
</html>