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
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>.
 *          Cristina Hainberger <hainberg@technikum-wien.at>
 */

require_once('../../config/cis.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/sprache.class.php');
require_once '../../include/phrasen.class.php';
require_once('../../include/gebiet.class.php');

  if (!$db = new basis_db())
      die('Fehler beim Oeffnen der Datenbankverbindung');

// Start session
session_start();

// If language is changed by language select menu, reset session- and language variable
if (isset($_GET['sprache_user']) && !empty($_GET['sprache_user']))
{
    $_SESSION['sprache_user'] = $_GET['sprache_user'];
    $sprache_user = $_GET['sprache_user'];
}

// Set language variable, which impacts the language displayed in the language select menu
$sprache_user = (isset($_SESSION['sprache_user']) && !empty($_SESSION['sprache_user'])) ? $_SESSION['sprache_user'] : DEFAULT_LANGUAGE;

// The language select menu is only displayed if RT-Ablauf of STG allows to switch language
$display = (isset($_SESSION['sprache_auswahl']) && $_SESSION['sprache_auswahl'] == true) ? '' : 'hidden';

$p = new phrasen($sprache_user);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../../vendor/twbs/bootstrap3/dist/css/bootstrap.min.css" type="text/css"/>
    <script type="text/javascript" src="../../vendor/components/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="../../vendor/twbs/bootstrap3/dist/js/bootstrap.min.js"></script>
</head>
<script type="text/javascript">
function changeSprache(sprache)
{
    // Add or replace param 'sprache_user' to the contents URL
    var content_url = new URL(parent.content.location); // url of contents' frame page (login.php or frage.php)
    var content_params = new URLSearchParams(content_url.search.slice(1));  // retrieve the querystring params
    content_params.set('sprache_user', sprache); // add or replace sprache_user

    // Pass GET-param sprache_user to topbar.php, menu.php and content (login.php or frage.php) and refresh the frames.
    location.href = location.pathname + '?sprache_user=' + sprache; // refreshes topbar.php
    parent.menu.location.href = parent.menu.location.pathname + '?sprache_user=' + sprache; // refreshes menu.php
    parent.content.location.href = parent.content.location.pathname + '?' + content_params; // refreshes login.php or frage.php
}
</script>
<body>
<?php
echo '	<table style="background-image: url(../../skin/images/header_testtool.png); background-repeat: repeat-x;" width="100%" height="100%" cellspacing="0" cellpadding="0">
		<tr>
    		<td valign="top" align="left">
    			<a href="index.html" target="_top"><img class="header_logo" style="min-height:65%; left: 16px; top: 10%;" src="../../skin/styles/'.DEFAULT_STYLE.'/logo_250x130.png" alt="logo"></a>
    		</td>

        <!--The language select menu is hidden by default. 
            Only displayed if RT-Ablauf of STG allows to switch language.-->
    		<td id="select_sprache" '. $display. '>
    		<div class="form-group form-inline pull-right">
                <select id="select-sprache" class="form-control" style="width: 170px; margin-right: 50px;" onchange="if (typeof(this.value) != \'undefined\') changeSprache(this.value)">';
                $sprache = new sprache();
                $sprache->getAll(true);
                foreach($sprache->result as $row)
                {
                    echo ' <option value="'. $row->sprache. '" '.($row->sprache == $sprache_user ? 'selected' : '').'>'.($row->bezeichnung_arr[$sprache_user]).'&nbsp;&nbsp;</option>';
                }
                echo '
                </select>
            </div>
            </td>
  		</tr>
		</table>';
?>
</body>
</html>

















