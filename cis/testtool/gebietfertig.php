<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> ,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Manfred Kindl <manfred.kindl@technikum-wien.at>
 *          Cristina Hainberger <hainberg@technikum-wien.at>
 */

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

	$(document).on("keydown", function (e) {
		if (((e.ctrlKey || e.metaKey) && e.keyCode === 85) || e.keyCode === 123)
		{
			e.preventDefault();
		}
	});

	$(document).on("contextmenu", function (e) {
		e.preventDefault();
	});
</script>
<body>
<br><br><br><br><br>
<center><h2><?php echo $p->t('testtool/zeitAbgelaufen');?></h2>
</center>
</body>
</html>
