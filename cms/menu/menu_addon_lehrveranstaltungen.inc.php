<?php
require_once(dirname(__FILE__).'/menu_addon.class.php');
require_once(dirname(__FILE__).'/../../include/studiengang.class.php');
require_once(dirname(__FILE__).'/../../include/lehrveranstaltung.class.php');
require_once(dirname(__FILE__).'/../../include/organisationsform.class.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');
require_once(dirname(__FILE__).'/../../include/phrasen.class.php');
require_once(dirname(__FILE__).'/../../include/student.class.php');

class menu_addon_lehrveranstaltungen extends menu_addon
{
	public function __construct()
	{
		$sprache = getSprache();
		$user = get_uid();
		$student = new student();
		if($student->load($user))
			$studiengang_kz=$student->studiengang_kz;
			
		$p = new phrasen($sprache);
		
		
		$this->block.='
			<script language="JavaScript" type="text/javascript">
			<!--
				function MM_jumpMenu(targ, selObj, restore)
				{
				  eval(targ + ".location=\'" + selObj.options[selObj.selectedIndex].value + "\'");
			
				  if(restore)
				  {
				  	selObj.selectedIndex = 0;
				  }
				}
			  //-->
			</script>';

		$this->block.='
		<table class="tabcontent">
			<tr>
				<td width="81" class="tdwrap">'.$p->t('global/studiengang').': </td>
				<td class="tdwrap">
					<select name="course" onChange="MM_jumpMenu(\'self\',this,0)">';

		$stg_obj = new studiengang();
		$stg_obj->getAll('typ, kurzbz');

		if(isset($_GET['studiengang_kz']) && is_numeric($_GET['studiengang_kz']))
			$studiengang_kz=$_GET['studiengang_kz'];
		
		if(isset($_GET['semester']) && is_numeric($_GET['semester']))
			$semester=$_GET['semester'];
		else
			$semester=1;
		
		$sel_kurzbzlang='';
		foreach($stg_obj->result as $row)
		{
			if($row->studiengang_kz!=0)
			{
				if(isset($studiengang_kz) AND $studiengang_kz == $row->studiengang_kz)
				{
					$this->block.= '<option value="?content_id='.$_GET['content_id'].'&studiengang_kz='.$row->studiengang_kz.'&semester='.$semester.'" selected>'.$row->kuerzel .' ('.$row->kurzbzlang.')</option>';
					$sel_kurzbzlang=$row->kurzbzlang;
				}
				else
				{
					$this->block.='<option value="?content_id='.$_GET['content_id'].'&studiengang_kz='.$row->studiengang_kz.'&semester='.$semester.'">'.$row->kuerzel .' ('.$row->kurzbzlang.')</option>';
				}
				if(!isset($studiengang_kz))
				{
					$studiengang_kz=$row->studiengang_kz;
				}
			}
		}

		$this->block.='
			  	</select>
			  	</td>
			  </tr>
			  <tr>
			  	<td class="tdwrap">&nbsp;</td>
			  </tr>
			  <tr>
			  	<td class="tdwrap">'.$p->t('global/semester').': </td>
			  	<td class="tdwrap">
			  	<select name="term" onChange="MM_jumpMenu(\'self\',this,0)">';
		
		$short = 'Fehler Stg.Kz '.$studiengang_kz;
		$max = 1;
		if ($stg_obj=new studiengang($studiengang_kz))
		{
			$short = $stg_obj->kuerzel;
		    $max = $stg_obj->max_semester;
		}
		if($semester>$max)
			$semester=1;
		for($i=0;$i<$max;$i++)
		{
			if(($i+1)==$semester)
				$this->block.= '<option value="?content_id='.$_GET['content_id'].'&studiengang_kz='.$studiengang_kz.'&semester='.($i+1).'" selected >'.($i+1).'. Semester</option>';
			else
				$this->block.= '<option value="?content_id='.$_GET['content_id'].'&studiengang_kz='.$studiengang_kz.'&semester='.($i+1).'">'.($i+1).'. Semester</option>';
		}
		
		$this->block.='
			  	</select>
			  	</td>
			  </tr>
			</table>
		<table>';
		$this->block.= '<script language="JavaScript" type="text/javascript">';
		$this->block.= '	parent.content.location.href="../cms/news.php?studiengang_kz='.$studiengang_kz.'&semester='.$semester.'"';
		$this->block.= '</script>';
		$this->block.='
		<tr>
		  <td class="tdwrap">&nbsp;</td>
		</tr>';

		
		if (!$lv_obj = new lehrveranstaltung())
			die('Fehler beim Oeffnen der Lehrveranstaltung'); 
				 
		$lv_obj->lehrveranstaltungen=array();
		if ($lv_obj->load_lva($studiengang_kz,$semester,null,TRUE,TRUE,'orgform_kurzbz DESC, bezeichnung'))
		{
			$lastform=null;
			foreach ($lv_obj->lehrveranstaltungen as $row)
			{		
				if($row->orgform_kurzbz!=$lastform)
				{
					$orgform = new organisationsform();
					$orgform->load($row->orgform_kurzbz);
					
					$this->block.= "<tr><td><b>$orgform->bezeichnung</b></td></tr>";			
					
					$lastform=$row->orgform_kurzbz;						
				}
				$this->block.= '<tr>';
				$this->block.= '	<td class="tdwrap"><ul style="margin: 0px; padding: 0px; padding-left: 20px;">';
				$this->block.= "<li><a class=\"Item2\" title=\"".$row->bezeichnung."\" href=\"private/lehre/lesson.php?lvid=$row->lehrveranstaltung_id\" target=\"content\">".$this->CutString($row->bezeichnung, 21).' '.$row->lehrform_kurzbz."</a></li>";
				$this->block.= '	</ul></td>';
				$this->block.= '</tr>';
			}
		}
		$this->block.='</table>';
		$this->outputBlock();
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

new menu_addon_lehrveranstaltungen();
?>