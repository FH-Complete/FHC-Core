<?php
/*
 * Created on 01-06-2006
 *
 * RDF fuer die Verbaende
 */
// header fuer no cache
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
include('../../vilesci/config.inc.php');
include('../../include/functions.inc.php');
include('../../include/fas/functions.inc.php');
include('../../include/fas/benutzer.class.php');
include('../../include/berechtigung.class.php');

error_reporting(E_ALL);
ini_set('display_errors','1');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

if (!$conn_fas = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$sem = isset($_GET['sem'])?$_GET['sem']:'';
$stg = isset($_GET['stg'])?$_GET['stg']:'';

$user = get_uid();

$benutzer = new benutzer($conn);
if(!$benutzer->loadVariables($user))
	die("error:".$benutzer->errormsg);
$stsem = getStudiensemesterIdFromName($conn_fas, $benutzer->variable->semester_aktuell);

$rechte = new berechtigung($conn);
$rechte->getBerechtigungen($user);
$rdf_url='http://www.technikum-wien.at/gruppen';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:GRP="<?php echo $rdf_url; ?>/rdf#"
>

<?php
	$ref = '';
	$descr='';
	$grps= array();
	$stgs= array();
	$hier = '';

	$qry = "SELECT
				CASE studiengang.studiengangsart
					WHEN 1 THEN 'B'
					WHEN 2 THEN 'M'
					WHEN 3 THEN 'D'
				END as art_bez,
				studiengang.kuerzel as kuerzel,
				studiengang.kennzahl as kennzahl,
				studiengang.studiengangsart as art,
				gruppe.studiengang_fk,
				gruppe.gruppe_pk as gruppe_id,
				gruppe.obergruppe_fk as obergruppe_id,
				gruppe.*,
				fas_function_get_fullname_from_gruppe(gruppe_pk) as grpname
			FROM
				gruppe,
				studiengang
			WHERE
				gruppe.studiensemester_fk='$stsem' AND
				gruppe.studiengang_fk=studiengang.studiengang_pk";
	if($stg!='')
		$qry .= " AND studiengang.studiengang_pk='$stg'";
	$qry .="
			ORDER BY
				studiengang_fk,
				ausbildungssemester_fk,
				obergruppe_fk";
	if(!$result=pg_query($conn_fas,$qry))
		die("Failed");
	$i=0;
	$laststg=0;
	while($row=pg_fetch_object($result))
	{
		if($rechte->isBerechtigt('admin', $row->kennzahl) || $rechte->isBerechtigt('lva-verwaltung',$row->kennzahl))
		{
			if($laststg!=$row->studiengang_fk)
			{
				$laststg=$row->studiengang_fk;
				$descr.="
		<RDF:Description about=\"".$rdf_url.'/'.$row->studiengang_fk."\" >
			<GRP:studiengang_id>$row->studiengang_fk</GRP:studiengang_id>
			<GRP:gruppe_id>0</GRP:gruppe_id>
			<GRP:studiengang_bezeichnung>(".$row->art_bez.") ".$row->kuerzel."</GRP:studiengang_bezeichnung>
			<GRP:name>($row->art_bez) $row->kuerzel</GRP:name>
			<GRP:ausbildungssemester_id>0</GRP:ausbildungssemester_id>
		</RDF:Description>";
			}
			$descr.="
		<RDF:Description about=\"".$rdf_url.'/'.$row->studiengang_fk.'/'.$row->gruppe_id."\" >
			<GRP:studiengang_id>$row->studiengang_fk</GRP:studiengang_id>
			<GRP:gruppe_id>$row->gruppe_id</GRP:gruppe_id>
			<GRP:studiengang_bezeichnung>(".$row->art_bez.") ".$row->kuerzel."</GRP:studiengang_bezeichnung>
			<GRP:name>(".$row->art_bez.") ".$row->kuerzel." - $row->grpname</GRP:name>
			<GRP:ausbildungssemester_id>$row->ausbildungssemester_fk</GRP:ausbildungssemester_id>
		</RDF:Description>
	   		";
			if($row->obergruppe_id==0)
			{
				array_push($grps,$row->gruppe_id);
				array_push($stgs,$row->studiengang_fk);
			}
			$i++;
		}
	}

	function myfkt($gid,$conn_fas,$rdf_url,$einr,$stg)
	{
		$qry = "Select * from gruppe where obergruppe_fk=$gid";
		if($result=pg_query($conn_fas,$qry))
		{
			if(pg_num_rows($result)>1)
			{
				$row=pg_fetch_object($result,0);
				//echo "\n$einr   <li resource=\"".$rdf_url.'/'.$row->studiengang_fk.'/'.$gid."\" />";
				echo "\n$einr<RDF:li>\n$einr   <RDF:Seq about=\"".$rdf_url.'/'.$row->studiengang_fk.'/'.$gid."\" >";
				while($row=pg_fetch_object($result))
				{
					myfkt($row->gruppe_pk,$conn_fas,$rdf_url,$einr.'   ',$row->studiengang_fk);
				}
				echo "\n$einr   </RDF:Seq>\n$einr</RDF:li>";
			}
			else
			{
				if(pg_num_rows($result)>0)
				{
					$row=pg_fetch_object($result);
					$qry = "Select count(*) as anz from gruppe where obergruppe_fk=$row->gruppe_pk";

					if($result1=pg_query($conn_fas,$qry))
					{
						if($row1=pg_fetch_object($result1))
						{
							if($row1->anz>0)
							{
								//echo "\n$einr   <li resource=\"".$rdf_url.'/'.$row->studiengang_fk.'/'.$gid."\" />";
								echo "\n$einr<RDF:li>\n$einr   <RDF:Seq about=\"".$rdf_url.'/'.$row->studiengang_fk.'/'.$gid."\" >";
								myfkt($row->gruppe_pk, $conn_fas,$rdf_url,$einr.'   ',$row->studiengang_fk);
								echo "\n$einr   </RDF:Seq>\n$einr</RDF:li>";
							}
							else
								echo "\n$einr   <RDF:li resource=\"".$rdf_url.'/'.$row->studiengang_fk.'/'.$row->gruppe_pk."\" />";
						}
						else
							echo "\nFAIL2\n";
					}
					else
						echo "\nFAIL1\n";
				}
				else
				{
					echo "\n$einr   <RDF:li resource=\"".$rdf_url.'/'.$stg.'/'.$gid."\" />";
				}
			}
		}
	}

	echo $descr;
	echo "\n<RDF:Seq about=\"".$rdf_url."/liste\">";
	$laststg=0;
	for ($i=0;$i<count($grps);$i++)
	{
		$grp=$grps[$i];
		if($stgs[$i]!=$laststg)
		{
			if($laststg!=0)
			{
				echo "\n      </RDF:Seq>";
				echo "\n   </RDF:li>";
			}
			$laststg=$stgs[$i];
			echo "\n   <RDF:li>\n      <RDF:Seq about=\"".$rdf_url.'/'.$laststg."\" >";

		}
		myfkt($grp,$conn_fas,$rdf_url,'      ',0);
	}
	echo "\n      </RDF:Seq>";
	echo "\n   </RDF:li>";
	echo "\n</RDF:Seq>";

?>


</RDF:RDF>
