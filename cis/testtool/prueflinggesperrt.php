<?php

require_once('../../config/cis.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/sprache.class.php');
require_once '../../include/phrasen.class.php';

  if (!$db = new basis_db())
      die('Fehler beim Oeffnen der Datenbankverbindung');

// Start session
session_start();

// If language is changed by language select menu, reset language variables
if (isset($_GET['sprache_user']) && !empty($_GET['sprache_user']))
{
    $_SESSION['sprache_user'] = $_GET['sprache_user'];
    $sprache_user = $_GET['sprache_user'];
}

// Set language variable
$sprache_user = (isset($_SESSION['sprache_user']) && !empty($_SESSION['sprache_user'])) ? $_SESSION['sprache_user'] : DEFAULT_LANGUAGE;
$p = new phrasen($sprache_user);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../../vendor/components/jquery/jquery.min.js"></script>
</head>

<script type="text/javascript">
	$(document).on("keydown", function (e)
	{
		if (((e.ctrlKey || e.metaKey) && e.keyCode === 85) || e.keyCode === 123) {
			e.preventDefault();
		}
	});

	$(document).on("contextmenu", function (e)
	{
		e.preventDefault();
	});
</script>

<body>
<br><br><br><br><br>
<center><h2><?php echo $p->t('testtool/prueflingGesperrt');?></h2>
</center>
</body>
</html>
