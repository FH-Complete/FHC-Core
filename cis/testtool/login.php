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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Manfred Kindl <manfred.kindl@technikum-wien.at>
 */

require_once('../../config/cis.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/pruefling.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/reihungstest.class.php');
require_once('../../include/sprache.class.php');
require_once '../../include/phrasen.class.php';
require_once '../../include/datum.class.php';

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

//if(isset($_GET['lang']))
//	setSprache($_GET['lang']);

$date = new datum(); 

function getSpracheUser()
{
	if(isset($_SESSION['sprache_user']))
	{
		$sprache_user=$_SESSION['sprache_user'];
	}
	else
	{
		if(isset($_COOKIE['sprache_user']))
		{
			$sprache_user=$_COOKIE['sprache_user'];
		}
		else
		{
			$sprache_user=DEFAULT_LANGUAGE;
		}
		setSpracheUser($sprache_user);
	}
	return $sprache_user;
}

function setSpracheUser($sprache)
{
	$_SESSION['sprache_user']=$sprache;
	setcookie('sprache_user',$sprache,time()+60*60*24*30,'/');
}

if(isset($_GET['sprache_user']))
{
	$sprache_user = new sprache();
	if($sprache_user->load($_GET['sprache_user']))
	{
		setSpracheUser($_GET['sprache_user']);
	}
	else
		setSpracheUser(DEFAULT_LANGUAGE);
}

$sprache_user = getSpracheUser(); 
$p = new phrasen($sprache_user);

$gebdatum='';

session_start();
$reload=false;
$reload_parent=false;

$sg_var = new studiengang();
$studiengang_typ_arr = $sg_var->studiengang_typ_arr;

if (isset($_GET['logout']))
{
	if(isset($_SESSION['prestudent_id']))
	{
		$reload = true;
		session_destroy();
	}
}

if(isset($_POST['gebdatum']) && $_POST['gebdatum']!='')
{
	$gebdatum = $date->formatDatum($_POST['gebdatum'],'Y-m-d');	
}
else
	$gebdatum='';

if (isset($_POST['prestudent']) && isset($gebdatum))
{
	$ps=new prestudent($_POST['prestudent']);
	//Geburtsdatum Pruefen
	if ($gebdatum==$ps->gebdatum)
	{
		//Freischaltung fuer zugeteilten Reihungstest pruefen
		$rt = new reihungstest();
		if($rt->load($ps->reihungstest_id))
		{
			if($rt->freigeschaltet)
			{		
				$pruefling = new pruefling();
				if($pruefling->getPruefling($ps->prestudent_id))
				{
					$studiengang = $pruefling->studiengang_kz;
					$semester = $pruefling->semester;
				}
				else 
				{
					$studiengang = $ps->studiengang_kz;
					$ps->getLastStatus($ps->prestudent_id);
					$semester = $ps->ausbildungssemester;
				}
				if($semester=='')
					$semester=1;
				
				$_SESSION['prestudent_id']=$_POST['prestudent'];
				$_SESSION['studiengang_kz']=$studiengang;
				$_SESSION['nachname']=$ps->nachname;
				$_SESSION['vorname']=$ps->vorname;
				$_SESSION['gebdatum']=$ps->gebdatum;
				$stg_obj = new studiengang($studiengang);
				$_SESSION['sprache']=$stg_obj->sprache;
						
				$_SESSION['semester']=$semester;
			}
			else
			{
				echo '<span class="error">'.$p->t('testtool/reihungstestNichtFreigeschalten').'</span>';
			}
		}
		else
		{
			echo '<span class="error">'.$p->t('testtool/reihungstestKannNichtGeladenWerden').'</span>';
		}
	}
	else 
	{
		echo '<span class="error">'.$p->t('testtool/geburtsdatumStimmtNichtUeberein').'</span>';
	}
}
	
if (isset($_SESSION['prestudent_id']))
	$prestudent_id=$_SESSION['prestudent_id'];
else
{
	//$prestudent_id=null;
	$ps=new prestudent();
	$datum=date('Y-m-d');
	$ps->getPrestudentRT($datum,true);
	if ($ps->num_rows==0)
		$ps->getPrestudentRT($datum);
}

if(isset($_GET['type']) && $_GET['type']=='sprachechange' && isset($_GET['sprache']))
{
	setSprache($_GET['sprache']);
}

if(isset($_SESSION['prestudent_id']) && !isset($_SESSION['pruefling_id']))
{
	$pruefling = new pruefling();
	
	if(!$pruefling->getPruefling($_SESSION['prestudent_id']))
	{
		$pruefling->new = true;
					
		$pruefling->studiengang_kz = $_SESSION['studiengang_kz'];
		$pruefling->semester = $_SESSION['semester'];
		
		$pruefling->idnachweis = '';
		$pruefling->registriert = date('Y-m-d H:i:s');
		$pruefling->prestudent_id = $_SESSION['prestudent_id'];
		if($pruefling->save())
		{
			$_SESSION['pruefling_id']=$pruefling->pruefling_id;
			$reload_parent=true;
		}
	}
}

if(isset($_POST['save']) && isset($_SESSION['prestudent_id']))
{
	$pruefling = new pruefling();
	if($_POST['pruefling_id']!='')
		if(!$pruefling->load($_POST['pruefling_id']))
			die('Pruefling wurde nicht gefunden');
		else 
			$pruefling->new=false;
	else 
		$pruefling->new=true;
			
	$pruefling->studiengang_kz = $_SESSION['studiengang_kz'];
	$pruefling->idnachweis = isset($_POST['idnachweis'])?$_POST['idnachweis']:'';
	$pruefling->registriert = date('Y-m-d H:i:s');
	$pruefling->prestudent_id = $_SESSION['prestudent_id'];
	$pruefling->semester = $_POST['semester'];
	if($pruefling->save())
	{
		$_SESSION['pruefling_id']=$pruefling->pruefling_id;
		$_SESSION['semester']=$pruefling->semester;
		$reload_parent=true;
	}
}
?><!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
	<script type="text/javascript" src="../../include/js/jquery1.9.min.js"></script>	
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>
	<script type="text/javascript">
	$(document).ready(function() 
	{		
		$.datepicker.setDefaults( $.datepicker.regional[ "" ] );
		<?php //Wenn Deutsch ausgewaehlt, dann Datepicker auch in Deutsch 
		if ($sprache_user=="German")
			echo '$.datepicker.setDefaults( $.datepicker.regional[ "de" ] );
			$( "#datepicker" ).datepicker(	
				{
					changeMonth: true,
					changeYear: true,
					defaultDate: "-6570",
					maxDate: -5110,
					yearRange: "-60:+00",
				}					
				);';
		else 
			echo '$( "#datepicker" ).datepicker({
				dateFormat: "dd.mm.yy",
				changeMonth: true,
				changeYear: true,
				defaultDate: "-6570",
				maxDate: -5110,
				yearRange: "-60:+00",	 
				});';
		?>
		
	});
	</script>
<?php 
	if($reload_parent)
		echo '<script language="Javascript">parent.menu.location.reload()</script>';
	if($reload)
		echo "<script language=\"Javascript\">parent.location.reload();</script>";
?>		
</head>

<body>
<?php	echo '<h1>'.$p->t('testtool/startseite').'</h1>';

	if (isset($prestudent_id))
	{	
		
		$prestudent = new prestudent($prestudent_id);
		$stg_obj = new studiengang($prestudent->studiengang_kz);
		$pruefling = new pruefling();
		$typ = new studiengang($prestudent->studiengang_kz);
		$typ->getStudiengangTyp($stg_obj->typ);	
		
		//Sprachwahl des Studiengangs
		$qry = "SELECT sprachwahl FROM testtool.tbl_ablauf_vorgaben WHERE studiengang_kz=".$db->db_add_param($prestudent->studiengang_kz)." LIMIT 1";
		$result = $db->db_query($qry);
		$sprachwahl = $db->db_fetch_object($result);
		$sprachwahl = $db->db_parse_bool($sprachwahl->sprachwahl);
		
		echo '<form method="GET">';	
		echo '<br>'.$p->t('testtool/begruessungstext').' <br/><br/>';
		echo '<b>'.$p->t('zeitaufzeichnung/id').'</b>: '.$_SESSION['prestudent_id'].'<br/>';
		echo '<b>'.$p->t('global/name').'</b>: '.$_SESSION['vorname'].' '.$_SESSION['nachname'].'<br/>';
		echo '<b>'.$p->t('global/geburtsdatum').'</b>: '.$date->formatDatum($_SESSION['gebdatum'],'d.m.Y').'<br/>';
		echo '<b>'.$p->t('global/studiengang').'</b>: '.$typ->bezeichnung.' '.($sprache_user=='English'?$stg_obj->english:$stg_obj->bezeichnung).'<br/><br/>';
		echo '<INPUT type="submit" value="Logout" name="logout" />';
		echo '</form>';
		echo '<br><br>';
		
		if($pruefling->getPruefling($prestudent_id))
		{
		
			echo '<FORM accept-charset="UTF-8"   action="'. $_SERVER['PHP_SELF'].'"  method="post" enctype="multipart/form-data">';
			echo '<input type="hidden" name="pruefling_id" value="'.$pruefling->pruefling_id.'">';
			echo '<table>';
			//echo '<tr><td>'.$p->t('global/semester').':</td><td><input type="text" name="semester" size="1" maxlength="1" value="'.$pruefling->semester.'">&nbsp;<input type="submit" name="save" value="Semester ändern"></td></tr>';
			//echo '<tr><td>ID Nachweis:</td><td><INPUT type="text" maxsize="50" name="idnachweis" value="'.$pruefling->idnachweis.'"></td></tr>';
			//echo '<tr><td></td><td><input type="submit" name="save" value="Semester ändern"></td>';
			echo '</table>';
			echo '</FORM>';
			
			//Wenn die Sprachwahl fuer diesen Studiengang aktiviert ist, dann die Sprachen anzeigen
			if($sprachwahl==true)
			{
				//Liste der Sprachen, die in den Gebieten vorkommen koennen
				$qry = "SELECT distinct sprache 
						FROM 
							testtool.tbl_pruefling 
							JOIN testtool.tbl_ablauf USING(studiengang_kz)
							JOIN testtool.tbl_frage USING(gebiet_id)
							JOIN testtool.tbl_frage_sprache USING(frage_id)						
						WHERE
							tbl_pruefling.pruefling_id=".$db->db_add_param($pruefling->pruefling_id)."
						ORDER BY sprache DESC";
				echo $p->t('testtool/spracheDerTestfragen').':';
				if($result = $db->db_query($qry))
				{
					while($row = $db->db_fetch_object($result))
					{
						if($_SESSION['sprache']==$row->sprache)
							$selected='style="border:1px solid black;"';
						else 
							$selected='';
						echo " <a href='".$_SERVER['PHP_SELF']."?type=sprachechange&sprache=$row->sprache' class='Item' $selected><img src='bild.php?src=flag&amp;sprache=$row->sprache' alt='$row->sprache' title='$row->sprache'/></a>";
					}
				}
			}
			echo '<br><br><br><b>'.$p->t('testtool/klickenSieAufEinTeilgebiet').'</b>';
			if($pruefling->pruefling_id!='')
			{
				$_SESSION['pruefling_id']=$pruefling->pruefling_id;
				echo '<script language="Javascript">parent.menu.location.reload()</script>';
			}
		}
		else 
		{
			echo '<span class="error">'.$p->t('testtool/keinPrueflingseintragVorhanden').'</span>';
		}
	}
	else
	{
		$prestudent_id_dummy_student = (defined('PRESTUDENT_ID_DUMMY_STUDENT')?PRESTUDENT_ID_DUMMY_STUDENT:'');
			
		echo '<form method="post">
				<SELECT name="prestudent">';
		echo '<OPTION value="'.$prestudent_id_dummy_student.'">'.$p->t('testtool/nameAuswaehlen').'</OPTION>\n';
		foreach($ps->result as $prestd)
		{
			$stg = new studiengang();
			$stg->load($prestd->studiengang_kz);
			if(isset($_POST['prestudent']) && $prestd->prestudent_id==$_POST['prestudent'])
				$selected = 'selected';	
			else
				$selected='';
			echo '<OPTION value="'.$prestd->prestudent_id.'" '.$selected.'>'.$prestd->nachname.' '.$prestd->vorname.' ('.(strtoupper($stg->typ.$stg->kurzbz)).')</OPTION>\n';
		}
		echo '</SELECT>';
		echo '&nbsp; '.$p->t('global/geburtsdatum').': ';
		echo '<input type="text" id="datepicker" size="12" name="gebdatum" value="'.$date->formatDatum($gebdatum,'d.m.Y').'">';
		echo '&nbsp; <INPUT type="submit" value="'.$p->t('testtool/login').'" />';
		echo '</form>';
		
		echo '<br /><br /><br />
		<center>
		<span style="font-size: 1.2em; font-style: italic;">'.$p->t('testtool/willkommenstext').'</span>
		</center>';
	}
?>

</body>
</html>
