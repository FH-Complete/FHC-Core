<?php
require_once(dirname(__FILE__).'/menu_addon.class.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/phrasen.class.php');
require_once(dirname(__FILE__).'/../../include/studiensemester.class.php');

class menu_addon_meinelv extends menu_addon
{
	public function __construct()
	{
		$sprache = getSprache();
		$user = get_uid();
		
		$is_lector=check_lektor($user);
			
		$p = new phrasen($sprache);
		$cutlength=21;
		//Meine LVs Student
		if(!$is_lector)
		{
			if ($stsemobj = new studiensemester())
			{
				$stsem = $stsemobj->getAktorNext();
				$qry = "SELECT distinct lehrveranstaltung_id, bezeichnung, studiengang_kz, semester, lehre, lehreverzeichnis from campus.vw_student_lehrveranstaltung WHERE uid='$user' AND studiensemester_kurzbz='$stsem' AND lehre=true AND lehreverzeichnis<>'' ORDER BY studiengang_kz, semester, bezeichnung";
				if($result = $this->db_query($qry))
				{
					while($row = $this->db_fetch_object($result))
					{
						if($row->studiengang_kz==0 && $row->semester==0) // Freifach
						{
							$this->items[] = array('title'=>$row->bezeichnung,
							 'target'=>'content',
							 'link'=>'private/freifaecher/lesson.php?lvid='.$row->lehrveranstaltung_id,
							 'name'=>'FF '.$this->CutString($row->bezeichnung, $cutlength)
							);
						}
						else
						{
							$this->items[] = array('title'=>$row->bezeichnung,
							 'target'=>'content',
							 'link'=>'private/lehre/lesson.php?lvid='.$row->lehrveranstaltung_id,
							 'name'=>$stg[$row->studiengang_kz].$row->semester.' '.$this->CutString($row->bezeichnung, $cutlength)
							);
						}
					}
				}
				else
					echo "Fehler beim Auslesen der LV";
			}
			else
			{
				echo "Fehler Semester beim Auslesen der LV";
			}		
		}
		
		//Eigenen LV des eingeloggten Lektors anzeigen
		if($is_lector)
		{
			if ($stsemobj = new studiensemester())
			{
				$stsem = $stsemobj->getAktorNext();
				$qry = "SELECT distinct bezeichnung, studiengang_kz, semester, lehreverzeichnis, tbl_lehrveranstaltung.lehrveranstaltung_id  FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter
				        WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				        tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
				        mitarbeiter_uid='$user' AND tbl_lehreinheit.studiensemester_kurzbz='$stsem'";
				if($result = $this->db_query($qry))
				{
					while($row = $this->db_fetch_object($result))
					{
						if($row->studiengang_kz==0 AND $row->semester==0)
						{
							$this->items[] = array('title'=>$row->bezeichnung,
							 'target'=>'content',
							 'link'=>'private/freifaecher/lesson.php?lvid='.$row->lehrveranstaltung_id,
							 'name'=>'FF '.$this->CutString($row->lehreverzeichnis, $cutlength)
							);
						}	
						else
						{
							$stg_obj = new studiengang();
							$stg_obj->load($row->studiengang_kz);
							$kurzbz = $stg_obj->kuerzel.'-'.$row->semester;
							
							$this->items[] = array('title'=>$row->bezeichnung,
							 'target'=>'content',
							 'link'=>'private/lehre/lesson.php?lvid='.$row->lehrveranstaltung_id,
							 'name'=>$kurzbz.' '.$this->CutString($row->bezeichnung, $cutlength)
							);
						}	
					}
				}
				else
					echo "Fehler beim Auslesen des Lehrfaches";
			}
			else
			{
				echo "Fehler Semester beim Auslesen der LV";
			}
		}
		$this->outputItems();
	}
	
	private function CutString($strVal, $limit)
	{
		if(mb_strlen($strVal) > $limit+3)
		{
			return mb_substr($strVal, 0, $limit) . "...";
		}
		else
		{
			return $strVal;
		}
	}
}

new menu_addon_meinelv();
?>