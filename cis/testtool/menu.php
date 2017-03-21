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
 */

require_once('../../config/cis.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/sprache.class.php');
require_once '../../include/phrasen.class.php';

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

require_once('../../include/gebiet.class.php');

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
$sprache = getSprache();

session_start();

?><!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>

<body>
<?php
if (isset($_SESSION['pruefling_id']))
{
	//content_id fuer Einfuehrung auslesen
	$qry = "SELECT content_id FROM testtool.tbl_ablauf_vorgaben WHERE studiengang_kz=".$db->db_add_param($_SESSION['studiengang_kz'])." LIMIT 1";
	$result = $db->db_query($qry);
	
	echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-right-width:1px;border-right-color:#BCBCBC;">';
	echo '<tr><td style="padding-left: 20px;" nowrap>
			<a href="login.php" target="content">'.$p->t('testtool/startseite').'</a>
		</td></tr>';
	if ($content_id = $db->db_fetch_object($result))
		if($content_id->content_id!='')
			echo '<tr><td style="padding-left: 20px;"><a href="../../cms/content.php?content_id='.$content_id->content_id.'&sprache='.$sprache.'" target="content">'.$p->t('testtool/einleitung').'</a></td></tr>';	
	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td style="padding-left: 20px;" nowrap>';
	
	$sprache_mehrsprachig = new sprache();
	$bezeichnung_mehrsprachig = $sprache_mehrsprachig->getSprachQuery('bezeichnung_mehrsprachig');
	$qry = "SELECT vw_ablauf.*, ".$bezeichnung_mehrsprachig." FROM testtool.vw_ablauf JOIN testtool.tbl_gebiet USING (gebiet_id) WHERE studiengang_kz=".$db->db_add_param($_SESSION['studiengang_kz'])." ORDER BY semester,reihung";

	$result = $db->db_query($qry);
	$lastsemester = '';
	
	while($row = $db->db_fetch_object($result))
	{
		//Jedes Semester in einer eigenen Tabelle anzeigen
		if($lastsemester!=$row->semester)
		{
			if($lastsemester!='')
			{
				//echo '<tr><td>&nbsp;</td></tr>';
				echo '</table>';
			}
			$lastsemester = $row->semester;
			
			echo '<table border="0" cellspacing="0" cellpadding="0" id="Gebiet" style="display: visible; border-collapse: separate; border-spacing: 0 6px;">';
			echo '<tr><td class="HeaderTesttool">'.$row->semester.'. '.$p->t('testtool/semester').' '.($row->semester!='1'?$p->t('testtool/quereinstieg'):'').'</td></tr>';
		}
		$gebiet = new gebiet();
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
						$class='ItemTesttoolAktiv';
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
							$class='ItemTesttoolBeendet';
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

			echo '<tr>
					<!--<td width="10" class="ItemTesttoolLeft" nowrap>&nbsp;</td>-->
						<td class="'.$class.'">
							<a class="'.$class.'" href="frage.php?gebiet_id='.$row->gebiet_id.'" onclick="document.location.reload()" target="content" style="'.$style.'">'.$sprache_mehrsprachig->parseSprachResult("bezeichnung_mehrsprachig", $row)[$sprache_user].'</a>
						</td>
					<!--<td width="10" class="ItemTesttoolRight" nowrap>&nbsp;</td>-->
					</tr>';
		}
		else 
		{
			echo '<tr>
						<td nowrap>
							<span class="error">&nbsp;'.$row->gebiet_bez.' (invalid)</span>
						</td>
					</tr>';
		}
	}
	echo '</table>';
	echo '</td></tr></table>';
}
else
{
	echo '</td></tr></table>';
}
?>
</body>
</html>
