<?php
$contentpage = 'login.php';

if(isset($_GET['prestudent']) && is_numeric($_GET['prestudent']))
{
	$contentpage = 'login.php?prestudent='.$_GET['prestudent'];
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
	<title>TestTool - FH Technikum Wien</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>

<frameset rows="13%,*" cols="*" frameborder="NO" border="0" framespacing="0">
	<frame src="topbar.php" name="topbar" scrolling="NO" noresize>
	<frameset rows="*" cols="230,*" framespacing="0" frameborder="NO" border="0">
		<frame id="menu_testtool" src="menu.php" name="menu" scrolling="AUTO" noresize>
    	<frame id="content_testtool" style="padding-top: 24px; overflow: hidden; padding-left: 10px" src="<?php echo $contentpage;?>" name="content">
  	</frameset>
	<noframes>
		<body>
		<p>Diese Seite verwendet Frames. Frames werden von Ihrem Browser aber nicht	unterstützt.</p>
		</body>
	</noframes>
</frameset>
</html>
