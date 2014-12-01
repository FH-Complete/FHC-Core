<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
header("Content-type: application/xhtml+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/studiengang.class.php');

$rdf_url='http://www.technikum-wien.at/lehrverbandsgruppe/';

$berechtigt_studiengang=array();
$uid='';
$berechtigung=new benutzerberechtigung();
$dbo = new basis_db();
$show_inout_block=false;

// Berechtigungen ermitteln
if(!isset($_SERVER['REMOTE_USER']))
{
	if(!isset($_GET['studiengang_kz']))
	{
		die('Wenn keine Authentifizierung stattfindet, muss eine studiengang_kz uebergeben werden');
	}
	else 
	{
		$berechtigt_studiengang=array($_GET['studiengang_kz']);
	}
}
else 
{
	$uid=get_uid();
	$berechtigung->getBerechtigungen($uid);
	$berechtigt_studiengang=$berechtigung->getStgKz('admin');
	if($berechtigung->isBerechtigt('inout/uebersicht'))
		$show_inout_block=true;

	if(isset($_GET['studiengang_kz']))
		$berechtigt_studiengang=array_merge($berechtigt_studiengang,array($_GET['studiengang_kz']));
}
$orgform_sequence=array();

if(isset($_GET['prestudent']) && $_GET['prestudent']=='false')
	$berechtigt_studiengang = array_merge($berechtigt_studiengang, $berechtigung->getStgKz('lv-plan'));
else 
	$berechtigt_studiengang = array_merge($berechtigt_studiengang, $berechtigung->getStgKz('assistenz'));	

//var_dump($berechtigung);
array_unique($berechtigt_studiengang);
$stg_kz_query='';
if (count($berechtigt_studiengang)>0)
{
	if ($berechtigt_studiengang[0]!='')
	{
		$stg_kz_query='AND tbl_studiengang.studiengang_kz IN ('.$dbo->implode4SQL($berechtigt_studiengang).')';
	}

	if (isset($_GET['studiengang_kz']))
		$stg_kz_query='AND tbl_lehrverband.studiengang_kz='.$dbo->db_add_param($_GET['studiengang_kz'], FHC_INTEGER);
	
	$sql_query="SELECT tbl_lehrverband.studiengang_kz, tbl_studiengang.bezeichnung, kurzbz,kurzbzlang, typ, tbl_lehrverband.semester, verband, gruppe, gruppe_kurzbz, tbl_lehrverband.bezeichnung AS lvb_bezeichnung, tbl_gruppe.bezeichnung AS grp_bezeichnung
				FROM (public.tbl_studiengang JOIN public.tbl_lehrverband USING (studiengang_kz))
					LEFT OUTER JOIN public.tbl_gruppe  ON (tbl_lehrverband.studiengang_kz=tbl_gruppe.studiengang_kz AND tbl_lehrverband.semester=tbl_gruppe.semester AND (tbl_lehrverband.verband='') AND tbl_gruppe.lehre AND tbl_gruppe.aktiv)
				WHERE tbl_lehrverband.aktiv $stg_kz_query
				ORDER BY erhalter_kz,typ, kurzbz, semester,verband,gruppe, gruppe_kurzbz;";
}
else 
{
	die('Keine Berechtigung');
}
//die($sql_query);
if(!$dbo->db_query($sql_query))
	$error_msg.=$dbo->db_last_error();
else
	$num_rows=$dbo->db_num_rows();

$stsem_obj = new studiensemester();
$stsem_obj->getAll();

//Bei Mischformen werden die Organisationsformen
//getrennt aufgelistet
function draw_orgformpart($stg_kz)
{
	global $orgform_sequence;
	$stg_obj = new studiengang($stg_kz);
	
	//Zusatzfilterung nur bei Mischformen anzeigen
	if(!$stg_obj->mischform)
		return true;
	
	$orgform_sequence[$stg_kz]='';
	
	$qry = "SELECT * FROM bis.tbl_orgform WHERE orgform_kurzbz not in('VBB','ZGS')";
	if($stg_obj->db_query($qry))
	{
		while($row = $stg_obj->db_fetch_object())
		{
			draw_orgformsubmenu($stg_kz, $row->orgform_kurzbz);
		}
	}
}

function draw_orgformsubmenu($stg_kz, $orgform)
{
	global $stsem_obj, $rdf_url, $orgform_sequence;
	
	$stg_obj = new studiengang($stg_kz);
	$stg_kurzbz = $stg_obj->kuerzel;
	
	echo '
	<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'" >
		<VERBAND:name>'.$orgform.'</VERBAND:name>
		<VERBAND:stg>'.$stg_kz.'</VERBAND:stg>
		<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
		<VERBAND:sem></VERBAND:sem>
		<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
	</RDF:Description>
		';
	
	$orgform_sequence[$stg_kz].='
	<RDF:li>
		<RDF:Seq RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'">
	';
	if(!(isset($_GET['prestudent']) && $_GET['prestudent']=='false'))
	{
		echo '
		<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/prestudent" >
			<VERBAND:name>PreStudent</VERBAND:name>
			<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
			<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
			<VERBAND:typ>prestudent</VERBAND:typ>
			<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
		</RDF:Description>';
		$orgform_sequence[$stg_kz].='
		<RDF:li>
			<RDF:Seq RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/prestudent">
		';
		foreach ($stsem_obj->studiensemester as $stsem)
		{
			echo '
					<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/'.$stsem->studiensemester_kurzbz.'" >
						<VERBAND:name>'.$stsem->studiensemester_kurzbz.'</VERBAND:name>
						<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
						<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
						<VERBAND:stsem>'.$stsem->studiensemester_kurzbz.'</VERBAND:stsem>
						<VERBAND:typ>prestudent</VERBAND:typ>
						<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
					</RDF:Description>

					<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/'.$stsem->studiensemester_kurzbz.'/interessenten" >
						<VERBAND:name>Interessenten</VERBAND:name>
						<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
						<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
						<VERBAND:stsem>'.$stsem->studiensemester_kurzbz.'</VERBAND:stsem>
						<VERBAND:typ>interessenten</VERBAND:typ>
						<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
					</RDF:Description>

					<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/'.$stsem->studiensemester_kurzbz.'/interessenten/zgv" >
						<VERBAND:name>ZGV erfüllt</VERBAND:name>
						<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
						<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
						<VERBAND:stsem>'.$stsem->studiensemester_kurzbz.'</VERBAND:stsem>
						<VERBAND:typ>zgv</VERBAND:typ>
						<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
					</RDF:Description>

					<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/'.$stsem->studiensemester_kurzbz.'/interessenten/reihungstestangemeldet" >
						<VERBAND:name>Reihungstest angemeldet</VERBAND:name>
						<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
						<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
						<VERBAND:stsem>'.$stsem->studiensemester_kurzbz.'</VERBAND:stsem>
						<VERBAND:typ>reihungstestangemeldet</VERBAND:typ>
						<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
					</RDF:Description>

					<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/'.$stsem->studiensemester_kurzbz.'/interessenten/reihungstestnichtangemeldet" >
						<VERBAND:name>Nicht zum Reihungstest angemeldet</VERBAND:name>
						<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
						<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
						<VERBAND:stsem>'.$stsem->studiensemester_kurzbz.'</VERBAND:stsem>
						<VERBAND:typ>reihungstestnichtangemeldet</VERBAND:typ>
						<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
					</RDF:Description>

					<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/'.$stsem->studiensemester_kurzbz.'/bewerber" >
						<VERBAND:name>Bewerber</VERBAND:name>
						<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
						<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
						<VERBAND:stsem>'.$stsem->studiensemester_kurzbz.'</VERBAND:stsem>
						<VERBAND:typ>bewerber</VERBAND:typ>
						<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
					</RDF:Description>

					<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/'.$stsem->studiensemester_kurzbz.'/aufgenommen" >
						<VERBAND:name>Aufgenommen</VERBAND:name>
						<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
						<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
						<VERBAND:stsem>'.$stsem->studiensemester_kurzbz.'</VERBAND:stsem>
						<VERBAND:typ>aufgenommen</VERBAND:typ>
						<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
					</RDF:Description>

					<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/'.$stsem->studiensemester_kurzbz.'/warteliste" >
						<VERBAND:name>Warteliste</VERBAND:name>
						<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
						<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
						<VERBAND:stsem>'.$stsem->studiensemester_kurzbz.'</VERBAND:stsem>
						<VERBAND:typ>warteliste</VERBAND:typ>
						<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
					</RDF:Description>

					<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/'.$stsem->studiensemester_kurzbz.'/absage" >
						<VERBAND:name>Absage</VERBAND:name>
						<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
						<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
						<VERBAND:stsem>'.$stsem->studiensemester_kurzbz.'</VERBAND:stsem>
						<VERBAND:typ>absage</VERBAND:typ>
						<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
					</RDF:Description>
					
					<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/'.$stsem->studiensemester_kurzbz.'/incoming" >
						<VERBAND:name>Incoming</VERBAND:name>
						<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
						<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
						<VERBAND:stsem>'.$stsem->studiensemester_kurzbz.'</VERBAND:stsem>
						<VERBAND:typ>incoming</VERBAND:typ>
						<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
					</RDF:Description>
					';
			$orgform_sequence[$stg_kz].= "\t\t\t<RDF:li>\n\t\t\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz/$orgform/$stsem->studiensemester_kurzbz\">\n";
	
			$orgform_sequence[$stg_kz].= "\t\t\t<RDF:li>";
			$orgform_sequence[$stg_kz].= "\n\t\t\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz/$orgform/$stsem->studiensemester_kurzbz/interessenten\">\n";
			$orgform_sequence[$stg_kz].= "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$orgform/$stsem->studiensemester_kurzbz/interessenten/zgv\" />\n";
			$orgform_sequence[$stg_kz].= "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$orgform/$stsem->studiensemester_kurzbz/interessenten/reihungstestangemeldet\" />\n";
			$orgform_sequence[$stg_kz].= "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$orgform/$stsem->studiensemester_kurzbz/interessenten/reihungstestnichtangemeldet\" />\n";
			$orgform_sequence[$stg_kz].= "\t\t\t\t</RDF:Seq>";
			$orgform_sequence[$stg_kz].= "\n\t\t\t</RDF:li>\n";

			$orgform_sequence[$stg_kz].= "\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$orgform/$stsem->studiensemester_kurzbz/bewerber\" />\n";
			$orgform_sequence[$stg_kz].= "\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$orgform/$stsem->studiensemester_kurzbz/aufgenommen\" />\n";
			$orgform_sequence[$stg_kz].= "\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$orgform/$stsem->studiensemester_kurzbz/warteliste\" />\n";
			$orgform_sequence[$stg_kz].= "\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$orgform/$stsem->studiensemester_kurzbz/absage\" />\n";
			$orgform_sequence[$stg_kz].= "\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$orgform/$stsem->studiensemester_kurzbz/incoming\" />\n";

			$orgform_sequence[$stg_kz].= "\t\t\t</RDF:Seq> <!-- $stsem->studiensemester_kurzbz -->\n\t\t\t</RDF:li>\n";
		}
		$orgform_sequence[$stg_kz].='
			</RDF:Seq><!-- Prestudent -->
		</RDF:li>
		';
	}
	
	$data = array();
	$qry = "SELECT semester, verband, gruppe,'' as gruppe_kurzbz, bezeichnung, null as sort FROM public.tbl_lehrverband WHERE orgform_kurzbz=".$stg_obj->db_add_param($orgform)." AND studiengang_kz=".$stg_obj->db_add_param($stg_kz)." AND aktiv
			UNION
			SELECT semester, '' as verband, '' as gruppe, gruppe_kurzbz, bezeichnung, sort FROM public.tbl_gruppe WHERE studiengang_kz=".$stg_obj->db_add_param($stg_kz)." AND orgform_kurzbz=".$stg_obj->db_add_param($orgform)." AND lehre AND sichtbar
			UNION
			SELECT semester, verband, gruppe,'' as gruppe_kurzbz, bezeichnung, null as sort FROM public.tbl_lehrverband WHERE studiengang_kz=".$stg_obj->db_add_param($stg_kz)." AND semester=0 AND aktiv
			ORDER BY semester, verband, gruppe, sort, gruppe_kurzbz";
	$sem='';
	$ver='';
	//echo $qry;
	if($result = $stg_obj->db_query($qry))
	{
		while($row = $stg_obj->db_fetch_object($result))
		{
			if ($sem!=$row->semester)
		   	{
		   		if($ver!='')
				{
					//vorhergehenden Verband schliessen
		   			$orgform_sequence[$stg_kz].='
						</RDF:Seq><!--VerbandOben-->
					</RDF:li>
					';
		   			$ver='';
				}
		   		if($sem!='')
		   		{
		   			//vorhergehendes Semester schliessen
		   			$orgform_sequence[$stg_kz].='
						</RDF:Seq> <!--SemesterOben-->
					</RDF:li>
					';
		   		}
		   		
		   		$sem=$row->semester;
		   		
		   		$orgform_sequence[$stg_kz].= "\t\t\t<RDF:li>";
				$orgform_sequence[$stg_kz].= "\n\t\t\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz/$orgform/$sem\">\n";
				$qry_bez = "SELECT bezeichnung FROM public.tbl_lehrverband WHERE studiengang_kz=".$stg_obj->db_add_param($stg_kz)." AND semester=".$stg_obj->db_add_param($sem)." AND trim(verband)='' AND trim(gruppe)=''";
				$bezeichnung = '';
				if($result_bez = $stg_obj->db_query($qry_bez))
					if($row_bez = $stg_obj->db_fetch_object($result_bez))
						$bezeichnung = ($row_bez->bezeichnung!=''?'('.$row_bez->bezeichnung.')':'');
				
				echo '		
					<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/'.$sem.'">
						<VERBAND:name>'.$stg_kurzbz.'-'.$sem.' '.$bezeichnung.'</VERBAND:name>
						<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
						<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
						<VERBAND:sem>'.$sem.'</VERBAND:sem>
						<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
					</RDF:Description>
					';
			}
		
			if($row->gruppe_kurzbz!='')
			{
				$orgform_sequence[$stg_kz].= "\t\t\t\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$orgform/$row->semester/$row->gruppe_kurzbz\" />\n";
				echo '
					<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/'.$row->semester.'/'.$row->gruppe_kurzbz.'">
						<VERBAND:name>'.$row->gruppe_kurzbz.' ('.$row->bezeichnung.')</VERBAND:name>
						<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
						<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
						<VERBAND:sem>'.$row->semester.'</VERBAND:sem>
						<VERBAND:gruppe>'.$row->gruppe_kurzbz.'</VERBAND:gruppe>
						<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
					</RDF:Description>
					';		
			}
			else 
			{
				//Wenn dieser Eintrag noch nicht geschrieben wurde
				if($row->verband!='' && $row->verband!=' ' && trim($row->gruppe)=='')
				{
					if($ver!='')
					{
						//vorhergehenden Verband schliessen
			   			$orgform_sequence[$stg_kz].='
							</RDF:Seq> <!-- Verband mitte-->
						</RDF:li>
						';
					}
					$ver=$row->verband;
					
					$orgform_sequence[$stg_kz].= "\t\t\t<RDF:li>";
					$orgform_sequence[$stg_kz].= "\n\t\t\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz/$orgform/$row->semester/$row->verband\">\n";

					echo '				
						<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/'.$row->semester.'/'.$row->verband.'">
							<VERBAND:name>'.$stg_kurzbz.'-'.$row->semester.$row->verband.($row->bezeichnung!=''?'  ('.$row->bezeichnung.')':'').'</VERBAND:name>
							<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
							<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
							<VERBAND:sem>'.$row->semester.'</VERBAND:sem>
							<VERBAND:ver>'.$row->verband.'</VERBAND:ver>
							<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
						</RDF:Description>
						';
				}
				else if  ($row->gruppe!='' && $row->gruppe!=' ')
				{
					$orgform_sequence[$stg_kz].= "\t\t\t\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$orgform/$row->semester/$row->verband/$row->gruppe\" />\n";
					echo '			
							<RDF:Description RDF:about="'.$rdf_url.$stg_kurzbz.'/'.$orgform.'/'.$row->semester.'/'.$row->verband.'/'.$row->gruppe.'">
								<VERBAND:name>'.$stg_kurzbz.'-'.$row->semester.$row->verband.$row->gruppe.($row->bezeichnung!=''?'  ('.$row->bezeichnung.')':'').'</VERBAND:name>
								<VERBAND:stg>'.$stg_kurzbz.'</VERBAND:stg>
								<VERBAND:stg_kz>'.$stg_kz.'</VERBAND:stg_kz>
								<VERBAND:sem>'.$row->semester.'</VERBAND:sem>
								<VERBAND:ver>'.$row->verband.'</VERBAND:ver>
								<VERBAND:grp>'.$row->gruppe.'</VERBAND:grp>
								<VERBAND:orgform>'.$orgform.'</VERBAND:orgform>
							</RDF:Description>
							';
				}
			}
		}
		if($ver!='')
		{
		//Verband schliessen
		$orgform_sequence[$stg_kz].='
						</RDF:Seq><!--VerbandUnten-->
					</RDF:li>
					';
		}
		if($sem!='')
		{
			//Semester schliessen
			$orgform_sequence[$stg_kz].='
							</RDF:Seq><!--SemesterUnten-->
						</RDF:li>
						';
		}
	}
	//Orgform schliessen
	$orgform_sequence[$stg_kz].='
		</RDF:Seq> <!--Orgform-->
	</RDF:li>
	';
}
?>

<RDF:RDF	
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:VERBAND="<?php echo $rdf_url; ?>rdf#" 
	xmlns:NC="http://home.netscape.com/NC-rdf#">

<?php
$stg_kz=null;
$sem=null;
while ($row=$dbo->db_fetch_object())
{
	if ($stg_kz!=$row->studiengang_kz)
	{
		draw_orgformpart($stg_kz);
		$sem=null;
		$stg_kz=$row->studiengang_kz;
		$stg_kurzbz=strtoupper($row->typ.$row->kurzbz);
		?>
		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz; ?>" >
			<VERBAND:name><?php echo $row->kurzbzlang.' ('.$stg_kurzbz.') - '.htmlspecialchars($row->bezeichnung); ?></VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz NC:parseType="Integer"><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
		</RDF:Description>

		<?php 
		if(!(isset($_GET['prestudent']) && $_GET['prestudent']=='false'))
		{
			?>
			<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/prestudent'; ?>" >
				<VERBAND:name>PreStudent</VERBAND:name>
				<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
				<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
				<VERBAND:typ>prestudent</VERBAND:typ>
			</RDF:Description>
			<?php
			foreach ($stsem_obj->studiensemester as $stsem)
			{
			?>
			<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/'.$stsem->studiensemester_kurzbz; ?>" >
				<VERBAND:name><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:name>
				<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
				<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
				<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
				<VERBAND:typ>prestudent</VERBAND:typ>
			</RDF:Description>
	
			<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/'.$stsem->studiensemester_kurzbz.'/interessenten'; ?>" >
				<VERBAND:name>Interessenten</VERBAND:name>
				<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
				<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
				<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
				<VERBAND:typ>interessenten</VERBAND:typ>
			</RDF:Description>
	
			<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/'.$stsem->studiensemester_kurzbz.'/interessenten/zgv'; ?>" >
				<VERBAND:name>ZGV erfüllt</VERBAND:name>
				<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
				<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
				<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
				<VERBAND:typ>zgv</VERBAND:typ>
			</RDF:Description>
	
			<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/'.$stsem->studiensemester_kurzbz.'/interessenten/reihungstestangemeldet'; ?>" >
				<VERBAND:name>Reihungstest angemeldet</VERBAND:name>
				<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
				<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
				<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
				<VERBAND:typ>reihungstestangemeldet</VERBAND:typ>
			</RDF:Description>
	
			<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/'.$stsem->studiensemester_kurzbz.'/interessenten/reihungstestnichtangemeldet'; ?>" >
				<VERBAND:name>Nicht zum Reihungstest angemeldet</VERBAND:name>
				<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
				<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
				<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
				<VERBAND:typ>reihungstestnichtangemeldet</VERBAND:typ>
			</RDF:Description>
	
			<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/'.$stsem->studiensemester_kurzbz.'/bewerber'; ?>" >
				<VERBAND:name>Bewerber</VERBAND:name>
				<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
				<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
				<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
				<VERBAND:typ>bewerber</VERBAND:typ>
			</RDF:Description>
	
			<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/'.$stsem->studiensemester_kurzbz.'/aufgenommen'; ?>" >
				<VERBAND:name>Aufgenommen</VERBAND:name>
				<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
				<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
				<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
				<VERBAND:typ>aufgenommen</VERBAND:typ>
			</RDF:Description>
	
			<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/'.$stsem->studiensemester_kurzbz.'/warteliste'; ?>" >
				<VERBAND:name>Warteliste</VERBAND:name>
				<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
				<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
				<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
				<VERBAND:typ>warteliste</VERBAND:typ>
			</RDF:Description>
	
			<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/'.$stsem->studiensemester_kurzbz.'/absage'; ?>" >
				<VERBAND:name>Absage</VERBAND:name>
				<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
				<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
				<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
				<VERBAND:typ>absage</VERBAND:typ>
			</RDF:Description>
			<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/'.$stsem->studiensemester_kurzbz.'/incoming'; ?>" >
				<VERBAND:name>Incoming</VERBAND:name>
				<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
				<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
				<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
				<VERBAND:typ>incoming</VERBAND:typ>
			</RDF:Description>
			<?php
			}
		}
   	}
   	
   	if ($sem!=$row->semester && ($row->verband!='' || $row->verband!=' '))
   	{
   		$sem=$row->semester;
		?>

		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/'.$sem; ?>">
			<VERBAND:name><?php echo $stg_kurzbz.'-'.$sem;
								if ($row->lvb_bezeichnung!='' && $row->lvb_bezeichnung!=null)
									echo '  ('.$row->lvb_bezeichnung.')';
							?>
			</VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $stg_kz; ?></VERBAND:stg_kz>
			<VERBAND:sem><?php echo $sem; ?></VERBAND:sem>
		</RDF:Description>
		<?php
	}
	if ($row->gruppe_kurzbz!=null)
	{
		?>

		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/'.$row->semester.'/'.$row->gruppe_kurzbz; ?>">
			<VERBAND:name><?php echo $row->gruppe_kurzbz.' ('.$row->grp_bezeichnung.')'; ?></VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
			<VERBAND:sem><?php echo $row->semester; ?></VERBAND:sem>
			<VERBAND:gruppe><?php echo $row->gruppe_kurzbz; ?></VERBAND:gruppe>
		</RDF:Description>
		<?php
	}
	else if ($row->verband!='' && $row->verband!=' ' && ($row->gruppe=='' || $row->gruppe==' '))
	{
		?>

		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/'.$row->semester.'/'.$row->verband; ?>">
			<VERBAND:name>
				<?php
					echo $stg_kurzbz.'-'.$row->semester.$row->verband;
					if ($row->lvb_bezeichnung!='' && $row->lvb_bezeichnung!=null)
						echo '  ('.$row->lvb_bezeichnung.')';
				?>
			</VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
			<VERBAND:sem><?php echo $row->semester; ?></VERBAND:sem>
			<VERBAND:ver><?php echo $row->verband; ?></VERBAND:ver>
		</RDF:Description>
		<?php
   	}
   	else if  ($row->gruppe!='' && $row->gruppe!=' ')
	{
		?>

		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/'.$row->semester.'/'.$row->verband.'/'.$row->gruppe; ?>">
			<VERBAND:name>
				<?php
					echo $stg_kurzbz.'-'.$row->semester.$row->verband.$row->gruppe;
					if ($row->lvb_bezeichnung!='' && $row->lvb_bezeichnung!=null)
						echo '  ('.$row->lvb_bezeichnung.')';
				?>
				</VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
			<VERBAND:sem><?php echo $row->semester; ?></VERBAND:sem>
			<VERBAND:ver><?php echo $row->verband; ?></VERBAND:ver>
			<VERBAND:grp><?php echo $row->gruppe; ?></VERBAND:grp>
		</RDF:Description>
		<?php
	}
}

//Incoming/Outgoing
if($show_inout_block)
{
	echo '
	<RDF:Description RDF:about="'.$rdf_url.'inout" >
		<VERBAND:name>International</VERBAND:name>
		<VERBAND:stg>IO</VERBAND:stg>
		<VERBAND:stg_kz NC:parseType="Integer"></VERBAND:stg_kz>
	</RDF:Description>
	<RDF:Description RDF:about="'.$rdf_url.'inout/incoming">
		<VERBAND:name>Incoming</VERBAND:name>
		<VERBAND:stg></VERBAND:stg>
		<VERBAND:stg_kz></VERBAND:stg_kz>
		<VERBAND:sem></VERBAND:sem>
		<VERBAND:ver></VERBAND:ver>
		<VERBAND:grp></VERBAND:grp>
		<VERBAND:orgform></VERBAND:orgform>
		<VERBAND:typ>incoming</VERBAND:typ>
	</RDF:Description>
	<RDF:Description RDF:about="'.$rdf_url.'inout/outgoing">
		<VERBAND:name>Outgoing</VERBAND:name>
		<VERBAND:stg></VERBAND:stg>
		<VERBAND:stg_kz></VERBAND:stg_kz>
		<VERBAND:sem></VERBAND:sem>
		<VERBAND:ver></VERBAND:ver>
		<VERBAND:grp></VERBAND:grp>
		<VERBAND:orgform></VERBAND:orgform>
		<VERBAND:typ>outgoing</VERBAND:typ>
	</RDF:Description>
	';
}

draw_orgformpart($stg_kz);
?>

<!-- Sequences -->

<RDF:Seq RDF:about="<?php echo $rdf_url.'alle-verbaende'; ?>">

<?php
	$lastout='';
	$stg_kz=null;
	$sem=null;
	$ver=null;
	for ($i=0;$i<$num_rows;$i++)
	{
		$row=$dbo->db_fetch_object(null,$i);
		if ($stg_kz!=$row->studiengang_kz)
		{
			//Verband schliessen
  			if ($ver!=null)
				echo "\t\t\t\t\t\t</RDF:Seq>\n\t\t\t\t\t</RDF:li>\n";
			$ver=null;
			//Semester schliessen
			if ($sem!=null)
				echo "\t\t\t\t</RDF:Seq>\n\t\t\t</RDF:li>\n";
			$sem=null;
			//Orgform_Sequence schreiben falls vorhanden
			if(isset($orgform_sequence[$stg_kz]))
				echo $orgform_sequence[$stg_kz];
			//Studiengang schliesssen
			if ($stg_kz!=null)
				echo "\t\t</RDF:Seq>\n\t</RDF:li>\n";
			$stg_kz=$row->studiengang_kz;
			$stg_kurzbz=strtoupper($row->typ.$row->kurzbz);
			//echo "\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz\" />\n";
			echo "\t<RDF:li>\n\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz\">\n";
			
			if(!(isset($_GET['prestudent']) && $_GET['prestudent']=='false'))
			{
				//Prestudent
				echo "\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/prestudent\" />\n";
				echo "\t\t\t<RDF:li>\n\t\t\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz/prestudent\">\n";
				foreach ($stsem_obj->studiensemester as $stsem)
				{
					echo "\t\t\t<RDF:li>\n\t\t\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz/$stsem->studiensemester_kurzbz\">\n";
	
					echo "\t\t\t<RDF:li>";
					echo "\n\t\t\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz/$stsem->studiensemester_kurzbz/interessenten\">\n";
					echo "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$stsem->studiensemester_kurzbz/interessenten/zgv\" />\n";
					echo "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$stsem->studiensemester_kurzbz/interessenten/reihungstestangemeldet\" />\n";
					echo "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$stsem->studiensemester_kurzbz/interessenten/reihungstestnichtangemeldet\" />\n";
					echo "\t\t\t\t</RDF:Seq>";
					echo "\n\t\t\t</RDF:li>\n";
	
					echo "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$stsem->studiensemester_kurzbz/bewerber\" />\n";
					echo "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$stsem->studiensemester_kurzbz/aufgenommen\" />\n";
					echo "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$stsem->studiensemester_kurzbz/warteliste\" />\n";
					echo "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$stsem->studiensemester_kurzbz/absage\" />\n";
					echo "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$stsem->studiensemester_kurzbz/incoming\" />\n";
	
					echo "\t\t\t</RDF:Seq>\n\t\t\t</RDF:li>\n";
				}
				echo "\t\t\t</RDF:Seq>\n\t\t\t</RDF:li>\n";
			}

			$lastout='stg_kz';
		}

	   	if ($sem!=$row->semester && ($row->verband!='' || $row->verband!=' '))
	   	{
   			if ($ver!=null)
				echo "\t\t\t\t\t\t</RDF:Seq>\n\t\t\t\t\t</RDF:li>\n";
			$ver=null;
			if ($sem!=null)
				echo "\t\t\t\t</RDF:Seq>\n\t\t\t</RDF:li>\n";
			$sem=$row->semester;
			echo "\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$row->semester\" />\n";
			echo "\t\t\t<RDF:li>\n\t\t\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz/$row->semester\">\n";
			$lastout='semester';
		}
		if ($row->gruppe_kurzbz!=null)
		{
			echo "\t\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$row->semester/$row->gruppe_kurzbz\" />\n";
			$lastout='gruppe_kurzbz';
		}
		else if ($row->verband!='' && $row->verband!=' ' && ($row->gruppe=='' || $row->gruppe==' '))
		{
			if ($ver!=null)
				echo "\t\t\t\t\t\t</RDF:Seq>\n\t\t\t\t\t</RDF:li>\n";
			$ver=$row->verband;
			echo "\t\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$row->semester/$row->verband\" />\n";
			echo "\t\t\t\t\t<RDF:li>\n\t\t\t\t\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz/$row->semester/$row->verband\">\n";
			$lastout='verband';
		}
	   	else if  ($row->gruppe!='' && $row->gruppe!=' ')
	   	{
			echo "\t\t\t\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/$row->semester/$row->verband/$row->gruppe\" />\n";
	   		$lastout='gruppe';
		}
	}

	if ($num_rows>0)
	{
		if($ver!=null)
		{
			//Verband
			echo "\t\t\t\t\t\t</RDF:Seq><!-- verband gesamt -->\n\t\t\t\t\t</RDF:li>\n";
		}
		//Semester
		echo "\t\t\t\t</RDF:Seq><!-- Semester gesamt -->\n\t\t\t</RDF:li>\n";
		//Orgform_Sequence schreiben falls vorhanden
		if(isset($orgform_sequence[$stg_kz]))
			echo $orgform_sequence[$stg_kz];
		//Studiengang
		echo "\t\t</RDF:Seq><!-- Studiengang -->\n\t</RDF:li>\n";
	}

	//Incoming/Outgoing
	if($show_inout_block)
	{
		echo '
		<RDF:li>
			<RDF:Seq RDF:about="http://www.technikum-wien.at/lehrverbandsgruppe/inout">
				<RDF:li RDF:resource="http://www.technikum-wien.at/lehrverbandsgruppe/inout/incoming"/>
				<RDF:li RDF:resource="http://www.technikum-wien.at/lehrverbandsgruppe/inout/outgoing"/>
			</RDF:Seq>
		</RDF:li>';
	}

?>

</RDF:Seq>

</RDF:RDF>
