<?php
/* Copyright (C) 2016 FH Technikum-Wien
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
 * Authors: Andreas Moik <moik@technikum-wien.at>
 */

require_once(dirname(__FILE__)."/../../include/meta/php_utils.php");
require_once(dirname(__FILE__).'/../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../../include/globals.inc.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/benutzerberechtigung.class.php');
require_once(dirname(__FILE__).'/../../include/berechtigung.class.php');
require_once(dirname(__FILE__).'/../../include/studiengang.class.php');
require_once(dirname(__FILE__).'/../../include/studiensemester.class.php');
require_once(dirname(__FILE__).'/../../include/prestudent.class.php');
require_once(dirname(__FILE__).'/../../include/studienplatz.class.php');


$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!isset($_REQUEST["action"]))
	die("keine Aktion erhalten");
$action = $_REQUEST["action"];

if(!isset($_REQUEST["studiengang_kz"]))
	die("keine studiengang_kz erhalten");
$studiengang_kz = $_REQUEST["studiengang_kz"];


$studiengang = new studiengang($studiengang_kz);

if(!$rechte->isBerechtigt('student/stammdaten', $studiengang->oe_kurzbz, 'suid'))
	die('Sie haben keine Berechtigung');


switch($action)
{
	case "getStudenten":
		if(!isset($_REQUEST["studienplan_id"]))
			die("keine studienplan_id erhalten");
		$studienplan_id = $_REQUEST["studienplan_id"];

		if(!isset($_REQUEST["studiensemester_kurzbz"]))
			die("keine studiensemester_kurzbz erhalten");
		$studiensemester_kurzbz = $_REQUEST["studiensemester_kurzbz"];

		$prestudent = new prestudent();
		$return = $prestudent->getAllStudentenFromStudienplanAndStudsem($studienplan_id, $studiensemester_kurzbz, $studiengang_kz);

		returnAJAX(true,json_encode($return));
		break;


	case "getStudiengaenge":
		$studiengang = new studiengang();
		$studiengang->getAll();
		returnAJAX(true, json_encode($studiengang->result));
		break;


	case "getStudiensemester":
		$studiensemester = new studiensemester();
		$studiensemester->getAll();
		returnAJAX(true, json_encode($studiensemester->studiensemester));
		break;


	case "getStudienplaetze":
		if(!isset($_REQUEST["studiensemester_kurzbz"]))
			die("keine studiensemester_kurzbz erhalten");
		$studiensemester_kurzbz = $_REQUEST["studiensemester_kurzbz"];

		$studienplatz = new studienplatz();
		$studienplatz->load_studiengang_studiensemester($studiengang_kz, $studiensemester_kurzbz, true);
		returnAJAX(true, json_encode($studienplatz->result));
		break;

	case "setAufgenommene":
		if(!isset($_REQUEST["prestudent_ids"]))
			die("keine Studenten erhalten");

		$prestudent_ids = json_decode($_REQUEST["prestudent_ids"]);
		foreach($prestudent_ids as $i)
		{
			$prestudent = new prestudent($i);
			$prestudent->getLastStatus($i);
			$prestudent->status_kurzbz = "Aufgenommener";
			$prestudent->new = true;
			$prestudent->datum = date("Y-m-d H:i:s");
			$prestudent->updatevon = $user;
			$prestudent->updateamum = date("Y-m-d H:i:s");
			$prestudent->save_rolle();
		}
		returnAJAX(true, "");
		break;

	default:
		returnAJAX(false,"eine Aktion erhalten");
}

?>
