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
 * Menu Addon fuer Urlaube
 *
 * Zeigt eine Liste der untergebenen Mitarbeiter mit deren Urlaube
 */
require_once(dirname(__FILE__).'/menu_addon.class.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/phrasen.class.php');
require_once(dirname(__FILE__).'/../../include/mitarbeiter.class.php');
require_once(dirname(__FILE__).'/../../include/benutzerberechtigung.class.php');

class menu_addon_urlaub extends menu_addon
{
	public function __construct()
	{
		parent::__construct();

		$sprache = getSprache();
		$user = get_uid();

		$p = new phrasen($sprache);

		//Untergebene holen
		$mitarbeiter = new mitarbeiter();
		$mitarbeiter->getUntergebene($user);
		$untergebene = '';

		foreach ($mitarbeiter->untergebene as $u_uid)
		{
			if($untergebene!='')
				$untergebene.=',';

			$untergebene.="'".$this->db_escape($u_uid)."'";
		}

		$rechte = new benutzerberechtigung();
		$rechte->getBerechtigungen($user);
		if($rechte->isBerechtigt('mitarbeiter/urlaube', null, 'suid'))
		{
			if(!$mitarbeiter->getPersonal('true', null, null, 'true', null, null))
				echo 'Fehler:'.$mitarbeiter->errormsg;
			foreach($mitarbeiter->result as $row)
			{
				if($untergebene!='')
					$untergebene.=',';
				$untergebene.="'".$this->db_escape($row->uid)."'";
			}
		}

		if($untergebene!='')
		{
			$qry = "SELECT * FROM campus.vw_mitarbeiter WHERE uid in($untergebene) AND aktiv AND fixangestellt ORDER BY nachname, vorname";

			$this->linkitem['link']='private/profile/urlaubsfreigabe.php';
			$this->linkitem['target']='content';

			if($result = $this->db_query($qry))
			{
				$this->items[] = array('title'=>$p->t('menu/urlaubAlle'),
					 'target'=>'content',
					 'link'=>'private/profile/urlaubsfreigabe.php',
					 'name'=>$p->t('menu/urlaubAlle')
					);

				while($row = $this->db_fetch_object($result))
				{

					$name = $row->nachname.' '.$row->vorname.' '.$row->titelpre.' '.$row->titelpost;
					$title = $row->nachname.' '.$row->vorname.' '.$row->titelpre.' '.$row->titelpost;

					if($row->fixangestellt=='f')
						$name =  '<span style="color: gray;">'.$name.'</span>';

					$this->items[] = array('title'=>$title,
					 'target'=>'content',
					 'link'=>'private/profile/urlaubsfreigabe.php?uid='.$row->uid,
					 'name'=>$name
					);

				}
			}
		}
		else
			$this->link=false;

		$this->output();
	}
}

new menu_addon_urlaub();
?>
