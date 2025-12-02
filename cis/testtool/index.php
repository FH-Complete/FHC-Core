<?php
$contentpage = 'login.php';

require_once './session_init.php';

if(isset($_GET['prestudent']) && is_numeric($_GET['prestudent']))
{
	$contentpage = 'login.php?prestudent='.$_GET['prestudent'];
}

if ((isset($_SESSION['externe_ueberwachung']) && $_SESSION['externe_ueberwachung'] === true) &&
	isset($_SESSION['externe_ueberwachung_verified']) && $_SESSION['externe_ueberwachung_verified'] === false)
{
	header("Location: resetconnection.php");
	exit;
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
	<title>TestTool - FH Technikum Wien</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
	<?php
	if (!empty($_SESSION['externe_ueberwachung'])) : ?>
	<script>
		function loadInContent(url)
		{
			if (url.includes('logout=true'))
			{
				return doLogout(url);
			}

			let frame = document.getElementById('content_testtool');
			if (frame)
			{
				frame.src = url;
			}
		}

		function doLogout(url)
		{
			fetch(url)
			let topbarFrame = window.frames['topbar'];
			let menuFrame = window.frames['menu'];
			let contentFrame = window.frames['content'];

			if (contentFrame)
				contentFrame.location.href = 'logout.html';

			if (menuFrame)
				menuFrame.location.href = menuFrame.location.pathname;

			if (topbarFrame)
				topbarFrame.location.href = topbarFrame.location.pathname;
			return false;
		}

		function changeSprache(content_params, sprache)
		{
			let topbarFrame = window.frames['topbar'];
			let menuFrame = window.frames['menu'];
			let contentFrame = window.frames['content'];

			if (topbarFrame)
				topbarFrame.location.href = topbarFrame.location.pathname + '?sprache_user=' + sprache;

			if (menuFrame)
				menuFrame.location.href = menuFrame.location.pathname + '?sprache_user=' + sprache;

			if (contentFrame)
				contentFrame.location.href = contentFrame.location.pathname + '?' + content_params;
		}

	</script>
	<?php endif; ?>
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

