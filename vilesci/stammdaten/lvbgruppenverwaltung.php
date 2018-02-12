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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *			Stefan Puraner		< puraner@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/lehrverband.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
?>
<html>
	<head>
		<title>Lehrverbandsgruppen Verwaltung</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<!-- JQuery Script-->
		<script type="text/javascript" src="../../include/js/jstree/_lib/jquery.js"></script>
		<!-- Script zum erstellen des Trees-->
		<script type="text/javascript" src="../../include/js/jstree/jquery.jstree.js"></script>
		<!-- Script zum speichern der geöffneten Tree-Nodes-->
		<script type="text/javascript" src="../../include/js/jstree/_lib/jquery.cookie.js"></script>
		<!-- Script zum Laden der Baumdaten per AJAX-Request-->
		<script type="text/javascript" src="lvbgruppenverwaltung.js"></script>

		<style type="text/css">
			/*CSS to remove Folder Icon*/
			.jstree li a ins { display:none !important; }

			#ajaxData {
				float: left;
			}
			#treeContainer {
				float: left;
				width: 30%;
			}

			#newDataDiv {
				float: left;
			}
			.detailsDiv {
				background-color: #E0E0E0;
				position: fixed;
				top: 5em;
				float: left;
			}

			li {
				margin-top: 0.2em !important;
				margin-bottom: 0.2em !important;
			}
		</style>

	</head>
	<body>
		<h2>Gruppen - Verwaltung</h2>

		<?php
		if (isset($_GET['studiengang_kz']) && is_numeric($_GET['studiengang_kz']))
			$studiengang_kz = $_GET['studiengang_kz'];
		else
			$studiengang_kz = '';

		$user = get_uid();
		$rechte = new benutzerberechtigung();
		$rechte->getBerechtigungen($user);

		//Studiengang Drop Down anzeigen
		$stud = new studiengang();
		if (!$stud->getAll('typ, kurzbz, kurzbzlang'))
			echo 'Fehler beim Laden der Studiengaenge:' . $stud->errormsg;

		echo '<form accept-charset="UTF-8" name="frm_studiengang" action="' . $_SERVER['PHP_SELF'] . '" method="GET">';
		echo 'Studiengang: <SELECT name="studiengang_kz"  onchange="document.frm_studiengang.submit()">';

		foreach ($stud->result as $row)
		{
			if ($rechte->isBerechtigt('admin', $row->studiengang_kz, 'suid')
				|| $rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid')
				|| $rechte->isBerechtigt('lehre/gruppe', $row->studiengang_kz, 'suid')
				)
			{
				if ($studiengang_kz == '')
					$studiengang_kz = $row->studiengang_kz;

				echo '<OPTION value="' . $row->studiengang_kz . '"' . ($studiengang_kz == $row->studiengang_kz ? 'selected' : '') . '>' . $row->kuerzel . ' - ' . $row->kurzbzlang . '</OPTION>';
			}
		}

		echo '</SELECT>';
		echo '</form>';

		if ($rechte->isBerechtigt('admin', $studiengang_kz, 'suid')
		 || $rechte->isBerechtigt('lehre/gruppe', $studiengang_kz, 'suid'))
			$admin = true;
		else
			$admin = false;

		if ($rechte->isBerechtigt('assistenz', $studiengang_kz, 'suid'))
			$assistenz = true;
		else
			$assistenz = false;

		if (!$admin && !$assistenz)
			die('Sie haben keine Berechtigung für diesen Studiengang');

		$studiengang = new studiengang();
		$studiengang->load($studiengang_kz);

		//Tree der Gruppen
		echo '<div>';
		if (!$admin)
		{
			$where = ' AND aktiv=true';
			echo "<script>var admin = false</script>";
		}
		else {
			$where = '';
			echo "<script>var admin = true</script>";
		}
		if (empty($studiengang_kz)) {
			$studiengang_kz = 0;
		}
		echo "<div id='treeContainer'></div>";
		echo "<div id='ajaxData'></div></div>";
		echo "<div id='newDataDiv'></div>";

		echo "<form style='visibility: hidden' id='newDataForm' method='POST' action='javascript:newSemesterForNewStudiengang(\"".$studiengang_kz."\");'>
			<input type='hidden' name='type' value='neu'>
			<input type='hidden' name='studiengang_kz' value='".$studiengang_kz."' />
			<input type='text' maxlength='2' size='2' name='semester_neu'/>
			<input type='submit' value='Semester anlegen'/>
		</form>";
		?>
	</body>
</html>
