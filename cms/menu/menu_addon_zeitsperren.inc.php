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
 * Menu Addon fuer Zeitsperren
 */
require_once(dirname(__FILE__).'/menu_addon.class.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/phrasen.class.php');
require_once(dirname(__FILE__).'/../../include/benutzerberechtigung.class.php');
require_once(dirname(__FILE__).'/../../include/funktion.class.php');

class menu_addon_zeitsperren extends menu_addon
{
	public function __construct()
	{
		parent::__construct();

		$sprache = getSprache();
		$user = get_uid();

		$rechte = new benutzerberechtigung();
		$rechte->getBerechtigungen($user);

		$p = new phrasen($sprache);

		$fkt=new funktion();
		$fkt->getAll($user);

		if ($rechte->isFix() || $rechte->isBerechtigt('mitarbeiter/zeitsperre'))
		{
			$this->items[] = array('title' => $p->t('menu/zeitsperren'),
								'target'=> 'content',
								'link'  => 'private/profile/zeitsperre_days.php?days=12',
								'name'  => $p->t('menu/zeitsperren')
								);

			if ($rechte->isBerechtigt('admin',0) || $rechte->isBerechtigt('mitarbeiter')
			    || $fkt->checkFunktion('stvLtg')|| $fkt->checkFunktion('gLtg')
			    || $fkt->checkFunktion('Leitung') || $fkt->checkFunktion('ass'))
			{
				$this->items[] = array('title' => $p->t('menu/fixangestellte'),
								   'target'=> 'content',
								   'link'  => 'private/profile/zeitsperre.php?fix=true',
								   'name'  => $p->t('menu/fixangestellte')
								  );
				$this->items[] = array('title' => $p->t('menu/fixelektoren'),
								   'target'=> 'content',
								   'link'  => 'private/profile/zeitsperre.php?fix=true&lektor=true',
								   'name'  => $p->t('menu/fixelektoren')
								  );
				$this->items[] = array('title' => $p->t('menu/organisationseinheit'),
								   'target'=> 'content',
								   'link'  => 'private/profile/zeitsperre.php?organisationseinheit=',
								   'name'  => $p->t('menu/organisationseinheit')
								  );
				$this->items[] = array('title' => $p->t('menu/assistenz'),
								   'target'=> 'content',
								   'link'  => 'private/profile/zeitsperre.php?funktion=ass&stg_kz=',
								   'name'  => $p->t('menu/assistenz')
								  );
			}

			$stg_obj = new studiengang();
			$stg_obj->loadArray($rechte->getStgKz('admin'), 'typ, kurzbz', true);
			foreach($stg_obj->result as $row)
			{
				$this->items[] = array('title' => 'Lektoren '.$row->kurzbzlang,
								   'target'=> 'content',
								   'link'  => 'private/profile/zeitsperre.php?funktion=lkt&stg_kz='.$row->studiengang_kz,
								   'name'  => $p->t('menu/lektoren').' '.$row->kurzbzlang
								  );
			}
		}
		$this->output();
	}
}

new menu_addon_zeitsperren();
?>
