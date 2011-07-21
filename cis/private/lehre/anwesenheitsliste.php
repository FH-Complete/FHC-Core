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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

/**
 * Zeigt alle Gruppen an in denen sich Studenten befinden und verlinkt
 * auf die Seiten zum erstellen der Anwesenheitslisten(pdf) und Notenlisten(xls)
 *
 * Aufruf:
 * anwesenheitsliste.php?stg_kz=222&sem=1&lvid=1234
 */
	require_once('../../../config/cis.config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/studiengang.class.php');
	require_once('../../../include/lehrveranstaltung.class.php');
	require_once('../../../include/phrasen.class.php');
	
	
	$sprache = getSprache(); 
	$p=new phrasen($sprache); 
	
	if (!$db = new basis_db())
			die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));
			
  	$error=0;	
    if(isset($_GET['stg_kz']) && is_numeric($_GET['stg_kz']))
    	$stg_kz=$_GET['stg_kz'];
    else
    	$error=2;

    if(isset($_GET['sem']) && is_numeric($_GET['sem']))
    	$sem = $_GET['sem'];
    else
    	$error=2;

    if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
    	$lvid=$_GET['lvid'];
    else
    	$error=2;
    	
    if(isset($_GET['stsem']) && check_stsem($_GET['stsem']))
    	$stsem = $_GET['stsem'];
    else 
    	die($p->t('anwesenheitsliste/studiensemesterIstUngueltig'));

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>

<body>
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td><table class="tabcontent">
      <tr>
        <td class="ContentHeader" width="70%"><font class="ContentHeader">&nbsp;<?php echo $p->t('lehre/anwesenheitsUndNotenlisten');?></font></td>
      </tr>
	  <tr>
	  	<td>
	  	<br />

	  	<?php
	if($error==0)
	{
	  	$aw_content='';
	  	$awbild_content='';
	  	$nt_content='';

	  	//Content fuer Anwesenheitslisten erstellen
	  	$stg_arr = array();
	  	$stg_obj = new studiengang();
	  	$stg_obj->getAll();
	  	
	  	foreach ($stg_obj->result as $row)
	  		$stg_arr[$row->studiengang_kz]=$row->kuerzel;
	  	
	  	$lv = new lehrveranstaltung($lvid);
	  	  	
	  	$aw_content .= "<tr><td><a class='Item' href='anwesenheitsliste.pdf.php?stg=$stg_kz&sem=$sem&lvid=$lvid&stsem=$stsem'>".$p->t('anwesenheitsliste/gesamtliste')." $lv->bezeichnung</a></td></tr>";
	  	$awbild_content .= "<tr><td><a class='Item' href='anwesenheitsliste_bilder.pdf.php?stg=$stg_kz&sem=$sem&lvid=$lvid&stsem=$stsem'>".$p->t('anwesenheitsliste/gesamtliste')." $lv->bezeichnung</a></td></tr>";
	  	$nt_content .= "<tr><td><a class='Item' href='notenliste.xls.php?stg=$stg_kz&sem=$sem&lvid=$lvid&stsem=$stsem'>".$p->t('anwesenheitsliste/gesamtliste')." $lv->bezeichnung</a></td></tr>";
	  	

	  	echo "</table>";

	  	$qry = "SELECT *, tbl_lehreinheitgruppe.studiengang_kz, tbl_lehreinheitgruppe.semester FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehreinheitgruppe USING(lehreinheit_id) JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) 
	  			WHERE lehrveranstaltung_id='$lvid' AND studiensemester_kurzbz='".addslashes($stsem)."'";

	  	$qry = "SELECT *, tbl_lehreinheitgruppe.studiengang_kz, tbl_lehreinheitgruppe.semester ,tbl_lehreinheit.lehrform_kurzbz
				 FROM lehre.tbl_lehreinheit 
				JOIN lehre.tbl_lehreinheitgruppe USING(lehreinheit_id) 
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) 
	  			WHERE lehrveranstaltung_id='$lvid' AND studiensemester_kurzbz='".addslashes($stsem)."'";
					  		  	
	  	if($result = $db->db_query($qry))
	  	{
	  		if($db->db_num_rows($result)>0)
	  		{
		  		$lastlehreinheit='';
		  		$gruppen = '';
		  		while($row = $db->db_fetch_object($result))
		  		{
		  			if($lastlehreinheit!=$row->lehreinheit_id)
		  			{
		  				if($lastlehreinheit!='')
		  				{
			  				$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid)
			  						WHERE lehreinheit_id='$lastlehreinheit'";
			  				$lektoren = '';
			  				
			  				if($result_lkt = $db->db_query($qry))
			  				{
			  					while($row_lkt = $db->db_fetch_object($result_lkt))
			  					{
			  						if($lektoren!='')
			  							$lektoren.=', ';
			  						$lektoren .= $row_lkt->kurzbz;
			  					}
			  				}
			  				
			  				$aw_content .= "<tr><td><a class='Item' href='anwesenheitsliste.pdf.php?stg=$stg_kz&sem=$sem&lvid=$lvid&lehreinheit_id=$lastlehreinheit&stsem=$stsem'>&nbsp;&nbsp;&nbsp;<img src='../../../skin/images/haken.gif' />$kurzbz - $lehrform - $gruppen ($lektoren)</a></td></tr>";
			  				$awbild_content .= "<tr><td><a class='Item' href='anwesenheitsliste_bilder.pdf.php?stg=$stg_kz&sem=$sem&lvid=$lvid&lehreinheit_id=$lastlehreinheit&stsem=$stsem'>&nbsp;&nbsp;&nbsp;<img src='../../../skin/images/haken.gif' />$kurzbz - $lehrform - $gruppen ($lektoren)</a></td></tr>";
			  				$nt_content .= "<tr><td><a class='Item' href='notenliste.xls.php?stg=$stg_kz&sem=$sem&lvid=$lvid&lehreinheit_id=$lastlehreinheit&stsem=$stsem'>&nbsp;&nbsp;&nbsp;<img src='../../../skin/images/haken.gif' />$kurzbz - $lehrform - $gruppen ($lektoren)</a></td></tr>";
			  
			  				$lastlehreinheit = $row->lehreinheit_id;
			  				$gruppen='';
		  				}
		  				else 
		  					$lastlehreinheit = $row->lehreinheit_id;
		  			}
		  			
		  			if($gruppen!='')
		  				$gruppen.= ', ';
		  				  			
		  			if($row->gruppe_kurzbz!='')
		  				$gruppen .= $row->gruppe_kurzbz;
		  			else 
		  				$gruppen .= trim($stg_arr[$row->studiengang_kz].'-'.$row->semester.$row->verband.$row->gruppe);
		  				
		  			$lehrform = $row->lehrform_kurzbz;
		  			$kurzbz = $row->kurzbz;
		  		}
		  		$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid)
						WHERE lehreinheit_id='$lastlehreinheit'";
				$lektoren = '';
				if($result_lkt = $db->db_query($qry))
				{
					while($row_lkt = $db->db_fetch_object($result_lkt))
					{
						if($lektoren!='')
							$lektoren.=', ';
						$lektoren .= $row_lkt->kurzbz;
					}
				}
				
				$aw_content .= "<tr><td><a class='Item' href='anwesenheitsliste.pdf.php?stg=$stg_kz&sem=$sem&lvid=$lvid&lehreinheit_id=$lastlehreinheit&stsem=$stsem'>&nbsp;&nbsp;&nbsp;<img src='../../../skin/images/haken.gif' />$kurzbz - $lehrform - $gruppen ($lektoren)</a></td></tr>";
				$awbild_content .= "<tr><td><a class='Item' href='anwesenheitsliste_bilder.pdf.php?stg=$stg_kz&sem=$sem&lvid=$lvid&lehreinheit_id=$lastlehreinheit&stsem=$stsem'>&nbsp;&nbsp;&nbsp;<img src='../../../skin/images/haken.gif' />$kurzbz - $lehrform - $gruppen ($lektoren)</a></td></tr>";
				$nt_content .= "<tr><td><a class='Item' href='notenliste.xls.php?stg=$stg_kz&sem=$sem&lvid=$lvid&lehreinheit_id=$lastlehreinheit&stsem=$stsem'>&nbsp;&nbsp;&nbsp;<img src='../../../skin/images/haken.gif' />$kurzbz - $lehrform - $gruppen ($lektoren)</a></td></tr>";
	  		}
	  	}

	  	if($nt_content=='' && $aw_content=='')
	  	{
	  		echo $p->t('anwesenheitsliste/keineStudentenVorhanden');
	  	}
	  	else
	  	{
		  	if($aw_content!='')
				$aw_content = "<table border='0'><tr class='liste'><td><b>".$p->t('anwesenheitsliste/anwesenheitslisten')."</b></td></tr>".$aw_content."</table>";
		  	else
		  		$aw_content = $p->t('anwesenheitsliste/keineStudentenVorhanden');
		  	
		  	if($awbild_content!='')
				$awbild_content = "<table border='0'><tr class='liste'><td><b>".$p->t('anwesenheitsliste/anwesenheitslistenMitBildern')."</b></td></tr>".$awbild_content."</table>";
		  	else
		  		$awbild_content = $p->t('anwesenheitsliste/keineStudentenVorhanden');

		  	if($nt_content!='')
				$nt_content = "<table border='0'><tr class='liste'><td><b>".$p->t('anwesenheitsliste/notenlisten')."</b></td></tr>".$nt_content."</table>";
		  	else
		  		$nt_content = $p->t('anwesenheitsliste/keineStudentenVorhanden');
		  	echo $p->t('anwesenheitsliste/erstellenDerListeKlicken');
		  	echo "<br /><br/>";
		  	echo "<table>
		  		
		  		<tr>
		  		   <td>$aw_content</td>
		  		   <td>$nt_content</td>
		  		</tr>
		  		<tr>
		  			<td>&nbsp;</td>
		  			<td></td>
		  		</tr>
		  		<tr>
		  			<td>$awbild_content</td>
		  			<td></td>
		  		</tr>
		  		</table>";
	  	}
	}
	else
	{
		if($error==1)
			echo $p->t('global/fehlerBeimOeffnenDerDatenbankverbindung');
		elseif($error=2)
			echo $p->t('anwesenheitsliste/fehlerhafteParameteruebergabe');
		else
			echo $p->t('global/unbekannterFehleraufgetreten');
	}
	  	?>
	  	</td>
	  </tr>
    </table></td>
	<td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
</body>
</html>