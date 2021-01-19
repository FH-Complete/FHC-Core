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
 *			Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *			Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *			Manfred Kindl <manfred.kindl@technikum-wien.at>
 *          Cristina Hainberger <hainberg@technikum-wien.at>
 */

require_once('../../config/cis.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/sprache.class.php');
require_once '../../include/phrasen.class.php';
require_once '../../include/studiengang.class.php';
require_once('../../include/gebiet.class.php');

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

// Start session
session_start();

// If language is changed by language select menu, reset language and session variables
if(isset($_GET['sprache_user']) && !empty($_GET['sprache_user']))
{
    $sprache_user = $_GET['sprache_user'];
    $_SESSION['sprache_user'] = $_GET['sprache_user'];
}

// Set language variable, which impacts the navigation menu
$sprache_user = (isset($_SESSION['sprache_user']) && !empty($_SESSION['sprache_user'])) ? $_SESSION['sprache_user'] : DEFAULT_LANGUAGE;
$p = new phrasen($sprache_user);

?><!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" type="text/css"/>
    <script type="text/javascript" src="../../vendor/components/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="../../vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
</head>

<body scroll="no">
<?php
$gebiet_hasMathML = false; // true, wenn irgendein Gebiet eine/n Frage/Vorschlag im MathML-Format enthält
$invalid_gebiete = false;

if (isset($_SESSION['pruefling_id']))
{
	//content_id fuer Einfuehrung auslesen
	$qry = "SELECT content_id FROM testtool.tbl_ablauf_vorgaben WHERE studiengang_kz=".$db->db_add_param($_SESSION['studiengang_kz'])." LIMIT 1";
	$result = $db->db_query($qry);

	echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-right-width:1px;border-right-color:#BCBCBC; border-collapse: separate;
	border-spacing: 0 3px;">';

// Link zur Startseite
	echo '<tr><td class="ItemTesttool" style="margin-left: 20px;" nowrap>
			<a class="ItemTesttool navButton" href="login.php" target="content">'.$p->t('testtool/startseite').'</a>
		</td></tr>';

// Link zur Einleitung
	if ($content_id = $db->db_fetch_object($result))
	{
		if($content_id->content_id!='')
		{
			echo '
				<tr id="tr-einleitung"><td class="ItemTesttool" style="margin-left: 20px;" nowrap>
					<a class="ItemTesttool navButton" href="../../cms/content.php?content_id='.$content_id->content_id.'&sprache='.$sprache_user.'" target="content">'.$p->t('testtool/einleitung').'</a>
				</td></tr>
			';
		}
	}
	echo '<tr><td style="padding-left: 20px;" nowrap>';

	$studiengang_kz = (isset($_SESSION['studiengang_kz'])) ? $_SESSION['studiengang_kz'] : '';
	$stg = new Studiengang($studiengang_kz);

	$sprache_mehrsprachig = new sprache();
	$bezeichnung_mehrsprachig = $sprache_mehrsprachig->getSprachQuery('bezeichnung_mehrsprachig');

	/**
	 * Spaltennamen-Aliase extrahieren um sie im Outer-Select verwenden zu können
	 * $bezeichnung_mehrsprachig liefert: bezeichnung_mehrsprachig[1] as bezeichnung_mehrsprachig_1,...
	 * $bezeichnung_mehrsprachig_sel liefert: bezeichnung_mehrsprachig_1, bezeichnung_mehrsprachig_2,...
	 */
	$bezeichnung_mehrsprachig_sel = explode(",", $bezeichnung_mehrsprachig);
	foreach ($bezeichnung_mehrsprachig_sel as &$bm)
	{
		$bm = strrchr($bm, ' as ');
	}
	$bezeichnung_mehrsprachig_sel = implode(', ', $bezeichnung_mehrsprachig_sel);

	/**
	 * Reihungstestgebiete der Person ermitteln; Zusammenfassen, falls RT für mehrere Studien
	 * 1. Aktuelle Prestudenten zur Person über den Prüfling ermitteln,
	 * 2. Einstiegssemester (Erstsemester/Quereinsteiger) und Studienplan pro Prestudent ermitteln,
	 * 3. RT-Gebiete falls vorhanden über Studienplan, sonst über STG ermitteln
	 * 4. Für Quereinsteiger zusätzlich auch Erstsemestrigen-Gebiete
	 */
	$qry = "
		WITH prestudent_data AS
		(
		SELECT DISTINCT ON (prestudent_id)
			prestudent_id,
			studienplan_id,
			studiengang_kz,
			typ,
			tbl_studiengangstyp.bezeichnung AS typ_bz,
			ausbildungssemester AS semester
		FROM
			public.tbl_prestudentstatus AS ps_status
		JOIN
			public.tbl_prestudent USING (prestudent_id)
		JOIN
			public.tbl_studiengang USING (studiengang_kz)
		JOIN
			public.tbl_studiengangstyp USING (typ)
		WHERE
			tbl_prestudent.person_id = (
				SELECT
					person_id
				FROM
					public.tbl_prestudent
				WHERE
					prestudent_id = ".$db->db_add_param($_SESSION['prestudent_id'])."
			)

		/* Filter only future studiensemester (incl. actual one) */
		AND
			studiensemester_kurzbz IN (
				SELECT
					studiensemester_kurzbz
				FROM
					public.tbl_studiensemester
				WHERE
					ende > now()
			)

		/* Filter out all Abgewiesene */
		AND NOT EXISTS (
			SELECT
				1
			FROM
				tbl_prestudentstatus
			WHERE
				status_kurzbz = 'Abgewiesener'
			AND
				prestudent_id = ps_status.prestudent_id
		)

		AND
			status_kurzbz = 'Interessent'";

			/*  If the logged-in prestudents study is a Bachelor-study, filter only Bachelor-studies */
			if ($stg->typ == 'b')
			{
				$qry .= "
				 	AND tbl_studiengang.typ = 'b'";
			}
			/* If the logged-in prestudents study is NOT a Bachelor-study, get only the specific study */
			else
			{
				$qry .= "
				 	AND tbl_studiengang.studiengang_kz = ". $studiengang_kz;
			}

			$qry .= "

		/* Order to get last semester when using distinct on */
		ORDER BY
			prestudent_id,
			datum DESC,
			ps_status.insertamum DESC,
			ps_status.ext_id DESC
		)


		SELECT DISTINCT ON
			(gebiet_id, semester, reihung)
			semester,
			gebiet_id,
			STRING_AGG(studiengang_kz::TEXT, ', ' ORDER BY studiengang_kz) AS studiengang_kz_list,
			bezeichnung,
			MIN(reihung) AS reihung,
			". $bezeichnung_mehrsprachig_sel. "
		FROM (
			SELECT
				*
			FROM (
				(SELECT
					prestudent_data.semester AS ps_sem,
					gebiet_id,
					bezeichnung,
					tbl_ablauf.studienplan_id,
					tbl_ablauf.studiengang_kz,
					tbl_ablauf.semester,
					tbl_ablauf.reihung,
					".$bezeichnung_mehrsprachig. "
				FROM
					prestudent_data
				JOIN
					testtool.tbl_ablauf USING (studiengang_kz)
				JOIN
					testtool.tbl_gebiet USING (gebiet_id)
				WHERE
					(
						(prestudent_data.semester= 1 AND tbl_ablauf.semester = 1)
						OR
						(prestudent_data.semester= 2 AND tbl_ablauf.semester = 2)
						OR
						(prestudent_data.semester= 3 AND tbl_ablauf.semester IN (1,3))
					)
				AND (   
						prestudent_data.studienplan_id = tbl_ablauf.studienplan_id
						OR
						tbl_ablauf.studienplan_id IS NULL
					)
				)

				UNION

				(
				SELECT
					prestudent_data.semester AS ps_sem,
					gebiet_id,
					bezeichnung,
					tbl_ablauf.studienplan_id,
					tbl_ablauf.studiengang_kz,
					tbl_ablauf.semester,
					tbl_ablauf.reihung,
					". $bezeichnung_mehrsprachig. "
				FROM
					prestudent_data
				JOIN
					testtool.tbl_ablauf USING (studienplan_id)
				JOIN
					testtool.tbl_gebiet USING (gebiet_id)
				WHERE
					(prestudent_data.semester= 1 AND tbl_ablauf.semester = 1)
				OR
					(prestudent_data.semester= 3 AND tbl_ablauf.semester IN (1,3))
				)
			) temp
		) temp2

		GROUP BY
			semester,
			 gebiet_id,
			 bezeichnung,
			". $bezeichnung_mehrsprachig_sel ." 

		ORDER BY
			semester,
			reihung,
			gebiet_id
		";

	$result = $db->db_query($qry);
	$anzahlGebiete = $db->db_num_rows($result);
	$lastsemester = '';
	$quereinsteiger_stg = '';
	while($row = $db->db_fetch_object($result))
	{
		//Jedes Semester in einer eigenen Tabelle anzeigen
		if($lastsemester!=$row->semester)
		{
			if($lastsemester!='')
			{
				echo '</table>';
			}
			$lastsemester = $row->semester;

			echo '<table border="0" cellspacing="0" cellpadding="0" id="Gebiet" style="display: visible; border-collapse: separate; border-spacing: 0 3px;">';
			echo '<tr><td class="HeaderTesttool">'. ($row->semester == '1' ? $p->t('testtool/basisgebiete') : $p->t('testtool/quereinstiegsgebiete')).'</td></tr>';
		}

		// Bei Quereinstiegsgebieten nach STG clustern und die STG anzeigen
		if($row->semester != '1')
		{
			if($quereinsteiger_stg != $row->studiengang_kz_list)
			{
			    //echo "<br>"; // Abstand zwischen Erstsemester- und Quereinstiegs-Gebietsblock
				$quereinsteiger_stg = $row->studiengang_kz_list;
				$quereinsteiger_stg_arr = explode(',', $row->studiengang_kz_list);
				$quereinsteiger_stg_string = '';
				$cnt = 0;
				foreach ($quereinsteiger_stg_arr as $qe_stg)
                {
                    $stg = new Studiengang($qe_stg);
					$quereinsteiger_stg_string .= ($cnt > 0) ? ",<br>" : '';
                    $quereinsteiger_stg_string .= $stg->bezeichnung;
                    $cnt++;
                }
                echo '<tr><td bgcolor="#73a9d6" class="HeaderTesttoolSTG">'. $quereinsteiger_stg_string. '</td></tr>';
			}
        }
		$gebiet = new gebiet();

		// Prüfen, ob das Gebiet eine/n Frage/Vorschlag im MathML-Format enthält
        if (!$gebiet_hasMathML) // sobald nur ein MathML Format gefunden, variable nicht mehr überschreiben
        {
            $gebiet_hasMathML = $gebiet->hasMathML($row->gebiet_id);
        }

		if($gebiet->check_gebiet($row->gebiet_id))
		{
			//Status der Gebiete Pruefen
			$gebiet->load($row->gebiet_id);

			$qry = "SELECT extract('epoch' from '".$gebiet->zeit."'-(now()-min(begintime))) as time
					FROM testtool.tbl_pruefling_frage JOIN testtool.tbl_frage USING(frage_id)
					WHERE gebiet_id=".$db->db_add_param($row->gebiet_id)." AND pruefling_id=".$db->db_add_param($_SESSION['pruefling_id']);
			if($result_time = $db->db_query($qry))
			{
				if($row_time = $db->db_fetch_object($result_time))
				{
					if($row_time->time>0)
					{
						//Gebiet gestartet aber noch nicht zu ende
						$style='';
						$class='ItemTesttool ItemTesttoolAktiv';
					}
					else
					{
						if($row_time->time=='')
						{
							//Gebiet noch nicht gestartet
							$style='';
							$class='ItemTesttool';
						}
						else
						{
							//Gebiet ist zu Ende
							$style='';
							$class='ItemTesttool ItemTesttoolBeendet';
						}
					}
				}
				else
				{
					$style='';
					$class='ItemTesttool';
				}
			}
			else
			{
				$style='';
				$class='ItemTesttool';
			}

			// Fallback für Gebietbezeichnung, falls nicht in gewählter Sprache vorhanden
			$gebietbezeichnung = $sprache_mehrsprachig->parseSprachResult("bezeichnung_mehrsprachig", $row)[$sprache_user];
			if ($gebietbezeichnung == '')
			{
				$gebietbezeichnung = $sprache_mehrsprachig->parseSprachResult("bezeichnung_mehrsprachig", $row)[DEFAULT_LANGUAGE];

				if ($gebietbezeichnung == '')
				{
					$gebietbezeichnung = $row->bezeichnung;
				}
			}

			echo '<tr>
					<!--<td width="10" class="ItemTesttoolLeft" nowrap>&nbsp;</td>-->
						<td class="'.$class.'">
							<a class="'.$class.'" href="frage.php?gebiet_id='.$row->gebiet_id.'" onclick="document.location.reload()" target="content" style="'.$style.'">'.$gebietbezeichnung.'</a>
						</td>
					<!--<td width="10" class="ItemTesttoolRight" nowrap>&nbsp;</td>-->
					</tr>';
		}
		else
		{
			$invalid_gebiete = true;
		}
	}
	if ($anzahlGebiete > 0)
	{
		echo '</table>';
	}

	// Link zum Logout

	echo '<tr><td class="ItemTesttool" style="margin-left: 20px;" nowrap>
			<a class="ItemTesttool navButton" href="login.php?logout=true" target="content">Logout</a>
		</td></tr>';

	echo '</td></tr></table>';
}
else
{
	echo '</td></tr></table>';
}
?>
</body>

<script type="text/javascript">

    // Get users Browser
    var ua = navigator.userAgent;

    // If Browser is any other than Mozilla Firefox and the test includes any MathML,
    // show message to use Mozilla Firefox
    if ((ua.indexOf("Firefox") > -1) == false)
    {
        let hasMathML = "<?php echo (isset($gebiet_hasMathML)?$gebiet_hasMathML:''); ?>";
        let userLang = "<?php echo $sprache_user; ?>";
        if (hasMathML == true)
        {
            if (userLang == 'German')
            {
                alert('BITTE VERWENDEN SIE DEN MOZILLA FIREFOX BROWSER!\n(Manche Prüfungsfragen werden sonst nicht korrekt dargestellt.)');
            }
            else if(userLang == 'English')
            {
                alert('PLEASE USE MOZILLA FIREFOX BROWSER!\n(Ohterwise some exam items will not be displayed correctly.)');
            }
        }
    }

    // Error massage if check_gebiet function returns false
    $(function() {
        var invalid_gebiete = "<?php echo (isset($invalid_gebiete)?$invalid_gebiete:''); ?>";
        if(invalid_gebiete == true)
        {
            $('#tr-einleitung').append('' +
                '<tr><td>' +
                '<div class="alert alert-danger small" style="margin-left: 20px; margin-bottom:-3px; width: 170px; margin-top: 3px;" role="alert">' +
                '<span class="text-uppercase"><strong><?php echo $p->t("errors/achtung"); ?></strong></span><br>' +
                '<?php echo $p->t("testtool/invalideGebiete"); ?>' +
                '</div>' +
                '</td></tr>');
        }
    });
</script>
</html>
