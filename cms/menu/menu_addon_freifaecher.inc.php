<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Menue Addon fuer die Darstellung der Freifaecher
 *
 * Es wird eine Link-Liste mit allen aktuellen Freifaechern erstellt
 */
require_once(dirname(__FILE__).'/menu_addon.class.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/lehrveranstaltung.class.php');
require_once(dirname(__FILE__).'/../../include/studiensemester.class.php');

class menu_addon_freifaecher extends menu_addon
{
	public function __construct()
	{
		parent::__construct();

		$this->link=false;

		$sprache = getSprache();
		$stsem = new studiensemester();
		$stsem = $stsem->getAktOrNext();

		$lv_obj = new lehrveranstaltung();
		if(!$lv_obj->load_lva('0',null, null,true,false,'bezeichnung'))
			echo "<tr><td>$lv_obj->errormsg</td></tr>";

		foreach($lv_obj->lehrveranstaltungen AS $row)
		{
			$this->items[] = array('title'=>$row->bezeichnung_arr[$sprache],
						 'target'=>'content',
						 'link'=>'private/lehre/lesson.php?lvid='.$row->lehrveranstaltung_id.'&studiensemester_kurzbz='.$stsem,
						 'name'=>'<span '.(!$row->aktiv?' style="" ':' style=""').'>'.(!$row->aktiv?' <img src="../skin/images/ampel_rot.png" height="8px" height="8px"> ':' <img src="../skin/images/ampel_gruen.png" height="8px"> ').' '.CutString($row->bezeichnung_arr[$sprache], 21, '...').'</span>'
						);
		}

		$this->block.= '<script language="JavaScript" type="text/javascript">';
		$this->block.= '	parent.content.location.href="../cms/news.php?studiengang_kz=0&semester=0"';
		$this->block.= '</script>';

		$this->output();
	}
}

new menu_addon_freifaecher();
?>
