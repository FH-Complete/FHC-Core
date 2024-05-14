<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Diese Datei fügt zusätzliche Links im CIS Profil ein
 * Dazu muss die Datei umbenannt werden in profil_array.php
 *
 * Dazu wird ein Array aufgebaut welches die Links enthaelt.
 * 
 * $menu = array('MeinLink'=>array('name'=>'Name des Links','link'=>'../path/to/file.php','target'='content'));
 */


// Unterschiedliche Links für Studierende und Mitarbeiter
if($type=='student')
{
	if(!$ansicht)
		$menu['Notenliste']=array('name'=>$p->t('profil/leistungsbeurteilung'), 'link'=>'../lehre/notenliste.php', 'target'=>'content');
	$menu['LVPlan']=array('name'=>$p->t('profil/lvplanVon').' '.$user->nachname, 'link'=>'../lvplan/stpl_week.php?pers_uid='.$user->uid.'&type=student', 'target'=>'content');	
}
else
{
	if(!$ansicht)
	{
		$menu['Zeitwunsch']=array('name'=>$p->t('profil/zeitwuensche'), 'link'=>'zeitwunsch.php?uid='.$user->uid, 'target'=>'content');
		$menu['Lehrveranstaltungen']=array('name'=>$p->t('lvaliste/lehrveranstaltungen'), 'link'=>'lva_liste.php?uid='.$user->uid, 'target'=>'content');
	}

	if(check_lektor(get_uid()))
	{
		$menu['Zeitsperren']=array('name'=>$p->t('profil/zeitsperrenVon').' '.$user->nachname, 'link'=>'zeitsperre_days.php?days=30&lektor='.$user->uid, 'target'=>'content');
	}

	if($uid!=get_uid())
	{
		$menu['LVPlan']=array('name'=>$p->t('profil/lvplanVon').' '.$user->nachname, 'link'=>'../lvplan/stpl_week.php?pers_uid='.$user->uid.'&type=lektor', 'target'=>'content');
	}
}
?>
