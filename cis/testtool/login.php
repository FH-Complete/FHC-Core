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
 *          Cristina Hainberger <hainberg@technikum-wien.at>
 */

require_once('../../config/cis.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/pruefling.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studienplan.class.php');
require_once('../../include/ablauf.class.php');
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
		$reihungstest_id='';
		//Freischaltung fuer zugeteilten Reihungstest pruefen
		$rt = new reihungstest();

		// Wenns der Dummy ist dann extra laden
		$prestudent_id_dummy_student = (defined('PRESTUDENT_ID_DUMMY_STUDENT')?PRESTUDENT_ID_DUMMY_STUDENT:'');
		if($prestudent_id_dummy_student==$ps->prestudent_id)
		{
			$rt->getReihungstestPerson($ps->person_id);
			if(isset($rt->result[0]))
				$reihungstest_id = $rt->result[0]->reihungstest_id;
			else
			{
				echo '<span class="error">'.$p->t('testtool/reihungstestKannNichtGeladenWerden').'</span>';
			}
		}
		else
		{
			if($rt->getReihungstestPersonDatum($ps->prestudent_id, date('Y-m-d')))
			{
				// TODO Was ist wenn da mehrere Zurueckkommen?!
				if(isset($rt->result[0]))
					$reihungstest_id = $rt->result[0]->reihungstest_id;
				else
				{
					echo '<span class="error">'.$p->t('testtool/reihungstestKannNichtGeladenWerden').'</span>';
				}
			}
			else
			{
				echo 'Failed:'.$rt->errormsg;
			}
		}
		//echo "Reihungstest $reihungstest_id";
		if($reihungstest_id != '' && $rt->load($reihungstest_id))
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

				// STG und Studienplan mit der höchsten Prio ermitteln
				$firstPrio_studienplan_id = '';
				$firstPrio_studiengang_kz = '';

				$ps->getActualInteressenten($_POST['prestudent'], true);
				foreach($ps->result as $row)
				{
					if(isset($row->studiengang_kz))
					{
						$firstPrio_studienplan_id = $row->studienplan_id;
						break;
					}
				}
				foreach($ps->result as $row)
				{
					if(isset($row->studiengang_kz))
					{
						$firstPrio_studiengang_kz = $row->studiengang_kz;
						break;
					}
				}

				// Sprachvorgaben zu STG mit höchster Prio ermitteln

                // * 1. Sprache über Ablauf Vorgaben ermitteln
				$ablauf = new Ablauf();
				$ablauf->getAblaufVorgabeStudiengang($firstPrio_studiengang_kz);
				$rt_sprache = '';

				if(!empty($ablauf->result[0]))
				{
					$rt_sprache = $ablauf->result[0]->sprache;
				}

				// * 2. falls keine Sprache vorhanden -> Sprache über Studienplan ermitteln
				if (empty($rt_sprache))
                {
                    $stpl = new Studienplan();
                    $stpl->loadStudienplan($firstPrio_studienplan_id);
                    $rt_sprache = $stpl->sprache;
                }

				// * 3. falls keine Sprache vorhanden -> Sprache über Studiengang ermitteln
				if (empty($rt_sprache))
				{
					$stg = new Studiengang($firstPrio_studiengang_kz);
					$rt_sprache = $stg->sprache;
				}

				// * 4. Sprache setzen. Falls keine Sprache vorhanden -> DEFAULT language verwenden
				if (empty($rt_sprache))
				{
					$_SESSION['sprache'] = DEFAULT_LANGUAGE;
				}
				else
                {
					$_SESSION['sprache'] = $rt_sprache;
                }
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
	$ps->getPrestudentRT($datum);
}

if(isset($_GET['type']) && $_GET['type']=='sprachechange' && isset($_GET['sprache']))
{
	setSprache($_GET['sprache']);
}

if(isset($_SESSION['prestudent_id']) && !isset($_SESSION['pruefling_id']))
{
	$pruefling = new pruefling();

        //wenn kein Prüfling geladen werden kann
	if(!$pruefling->getPruefling($_SESSION['prestudent_id']))
		$pruefling->new = true;
        else
            $pruefling->new = false;

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
    <link rel="stylesheet" href="../../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" type="text/css"/>
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
    <script type="text/javascript" src="../../include/js/jquery1.9.min.js"></script>
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>
    <script type="text/javascript" src="../../vendor/components/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="../../vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
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
		echo '<script language="Javascript">parent.menu.location.reload();</script>';

	if($reload)
		echo "<script language=\"Javascript\">parent.location.reload();</script>";
?>
</head>

<body scroll="no" class='testtool-content'>
<?php

//REIHUNGSTEST STARTSEITE (nach Login)
if (isset($prestudent_id))
{

    $prestudent = new prestudent($prestudent_id);
    $stg_obj = new studiengang($prestudent->studiengang_kz);
    $pruefling = new pruefling();
    $typ = new studiengang($prestudent->studiengang_kz);
    $typ->getStudiengangTyp($stg_obj->typ);

    // STG mit der höchsten Prio ermitteln
    $ps = new Prestudent();

    //  * prinzipiell STG der session übernehmem
    $firstPrio_studiengang_kz = $prestudent->studiengang_kz;;

    //  * wenn STG des eingeloggten Prestudenten vom Typ Bachelor ist, dann höchste Prio aller
    //  Bachelor-STG ermitteln, an denen die Person noch interessiert ist
    if ($typ->typ == 'b')
    {
        $ps->getActualInteressenten($prestudent_id, true, 'b');
        foreach($ps->result as $row_prio)
        {
            if(isset($row_prio->studiengang_kz))
            {
                $firstPrio_studiengang_kz = $row_prio->studiengang_kz;
                break;
            }
        }
    }

    // Sprachwahl zu STG mit höchster Prio ermitteln
    $ablauf = new Ablauf();
    $sprachwahl = false;
    if ($ablauf->getAblaufVorgabeStudiengang($firstPrio_studiengang_kz) && is_bool($ablauf->result[0]->sprachwahl))
    {
       $sprachwahl = $ablauf->result[0]->sprachwahl;
    }

    //Prestudent Informationen
    echo '
        <h1 style="margin-top: -20px;">'. $p->t('testtool/begruessungstext'). '</h1><br/>
        <p>'. $p->t('testtool/anmeldedaten'). '</p><br/>   
    ';

    echo '
      <table class="table table-bordered">
            <tr>
                <td style="width: 50%;"><strong>'.$p->t('zeitaufzeichnung/id').'</strong></td>
                <td>'.$_SESSION['prestudent_id'].'</td>
            </tr>
            <tr>
                <td><strong>'.$p->t('global/name').'</strong></td>
                <td>'.$_SESSION['vorname'].' '.$_SESSION['nachname'].'</td>
            </tr>
            <tr>
                <td><strong>'.$p->t('global/geburtsdatum').'</strong></td>
                <td>'.$date->formatDatum($_SESSION["gebdatum"],"d.m.Y").'</td>
            </tr>
      </table>
    ';
	echo '<br>';
    echo '
         <p>'. $p->t('testtool/fuerFolgendeStgAngemeldet'). '</p><br>
         
         <table class="table table-bordered">        
            <thead>
                <tr>
                    <th>'. $p->t('global/studiengang'). '</th>
                    <th>Status</th>
                    <th>'. $p->t('testtool/reihungstest'). '</th>
                 </tr>
            </thead>    
            <tbody>  
         ';

    //  * wenn Prestudent an 1 - n Bachelor-Studiengängen interessiert ist, dann STG anführen
    if ($typ->typ == 'b')
    {
		$ps_arr = new Prestudent();
		$ps_arr->getActualInteressenten($prestudent_id, false, 'b');

        if (count($ps_arr->result) > 0)
        {
            // Jeweils letzten Status ermitteln (ob Interessent oder Abgewiesener)
            foreach ($ps_arr->result as $ps_obj)
            {
                $ps_tmp = new Prestudent();
                $ps_tmp->getLastStatus($ps_obj->prestudent_id);

                $ps_obj->lastStatus = $ps_tmp->status_kurzbz; // letzten Status dem result array hinzufügen
            }

            // Falls Status 'Abgewiesene' vorhanden, nach hinten reihen
            usort($ps_arr->result, function($a, $b){
                return strcmp($b->lastStatus, $a->lastStatus); // Order by DESC
            });

            foreach ($ps_arr->result as $ps_obj)
            {
                echo '<tr>';
                $stg = new Studiengang($ps_obj->studiengang_kz);

                if($ps_obj->lastStatus == "Interessent")
                {
                    echo '<td style="width: 50%;">'. $ps_obj->typ_bz .' '. ($sprache_user == 'English' ? $stg->english : $stg->bezeichnung). '</td>';
                    if($ps_obj->ausbildungssemester == '1')
                    {
                        echo '<td>'. $p->t('testtool/regulaererEinstieg'). ' (1. Semester)</td>';
						echo '<td>Basic</td>';
                    }
                    elseif($ps_obj->ausbildungssemester == '3')
                    {
                        echo '<td>'. $p->t('testtool/Quereinsteiger'). ' (3.Semester)</td>';
						echo '<td>Basic + '. $p->t('testtool/Quereinsteiger'). '</td>';
                    }
                }
                // wenn letzter Status \'Abgewiesener\' ist, dann als solchen kennzeichnen
                elseif($ps_obj->lastStatus == "Abgewiesener")
                {
                    echo '
                        <td class="text-muted">'. $ps_obj->typ_bz .' '. ($sprache_user == 'English' ? $stg->english : $stg->bezeichnung). '</td>
                        <td class="text-muted">'. $ps_obj->lastStatus. '</td>
                        <td class="text-muted">-</td>
                    ';
                }
                echo '</tr>';
            }
        }
    }
    //  * wenn Prestudent an einem Master-Studiengang interessiert ist, dann nur den einen STG anführen
    else
    {
        // Letzten Status für des Prestudenten einholen
        $ps_master = new Prestudent();
		$ps_master->getLastStatus($prestudent_id);
        echo '<td>'. $typ->bezeichnung.' '.($sprache_user=='English'?$stg_obj->english:$stg_obj->bezeichnung).'</td>';
		echo '<td>'. $ps_master->status_kurzbz.'</td>';
		echo '<td>Basic</td>';
    }

    echo ' 
        </tbody>				 
     </table>
    ';

    echo '<br>';

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

        //Wenn die Sprachwahl fuer den priorisierten Studiengang aktiviert ist, dann die Sprachen anzeigen
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

            if($result = $db->db_query($qry))
            {
				echo '
                    <p>'. $p->t('testtool/spracheDerTestfragen').':</p><br>
                    <div class="btn-group btn-group-justified" role="group" style="width: 50%">          
                ';

				while($row = $db->db_fetch_object($result))
				{
					$selected = ($_SESSION['sprache'] == $row->sprache) ? 'active' : '';
					$row_sprache = $row->sprache;
					if ($sprache_user == 'German')
                    {
                        if($row->sprache == 'English')
                        {
							$row_sprache = 'Englisch';
                        }
                        elseif ($row->sprache == 'German')
                        {
							$row_sprache = 'Deutsch';
                        }
                    }
					echo "
                        <div class='btn-group' role='group'> 
                            <a role='button' class='btn btn-default $selected' href='". $_SERVER['PHP_SELF']. "?type=sprachechange&sprache=". $row->sprache. "'>$row_sprache</a>
                        </div>
                    ";
				}
				echo '</div>';
            }
        }

        echo '<br><br>';
        echo '
            <div class="well well-lg text-center">
                <strong>'.$p->t('testtool/klickenSieAufEinTeilgebiet').'</strong>
            </div>
       ';

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
        //LOGIN FORM (Startseite vor Login)
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
    echo '<INPUT type="submit" value="'.$p->t('testtool/login').'" />';
    echo '</form>';

    echo '<br /><br /><br />
    <center>
    <span style="font-size: 1.2em; font-style: italic;">'.$p->t('testtool/willkommenstextTitel').'</span><br><br>
    <span style="font-size: 1.2em; font-style: italic;">'.$p->t('testtool/willkommenstext').'</span>
    </center>';
}
?>

</body>
</html>
