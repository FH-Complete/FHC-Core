<?php
require_once(dirname(__FILE__).'/menu_addon.class.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/phrasen.class.php');
require_once(dirname(__FILE__).'/../../include/benutzerberechtigung.class.php');
require_once(dirname(__FILE__).'/../../include/funktion.class.php');

class menu_addon_zeitsperren extends menu_addon
{
	public function __construct()
	{
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
	    		
			if ($rechte->isBerechtigt('admin',0) || $rechte->isBerechtigt('mitarbeiter'))
			{
				$this->items[] = array('title' => $p->t('menu/resturlaub'),
								   'target'=> 'content',
								   'link'  => 'private/profile/resturlaub.php',
								   'name'  => $p->t('menu/resturlaub')
								  );
			}
					
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
		$this->outputItems();
	}
}

new menu_addon_zeitsperren();
?>