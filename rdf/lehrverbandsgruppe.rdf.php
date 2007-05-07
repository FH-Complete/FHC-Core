<?php
header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';
include('../vilesci/config.inc.php');
include('../include/benutzerberechtigung.class.php');
include('../include/studiensemester.class.php');

$rdf_url='http://www.technikum-wien.at/lehrverbandsgruppe/';

if (!isset($REMOTE_USER))
	$REMOTE_USER='pam';
$uid=$REMOTE_USER;

if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

// Berechtigungen ermitteln
$berechtigung=new benutzerberechtigung($conn);
$berechtigung->getBerechtigungen($uid);
$berechtigt_studiengang=$berechtigung->getStgKz();
//var_dump($berechtigung);
$stg_kz_query='';
if (count($berechtigt_studiengang)>0)
	if ($berechtigt_studiengang[0]!=0)
	{
		foreach ($berechtigt_studiengang as $b_stg)
			$stg_kz_query.=' OR tbl_lehrverband.studiengang_kz='.$b_stg;
		$stg_kz_query='AND ('.substr($stg_kz_query,3).')';
	}

$sql_query="SET search_path TO public;
			SELECT tbl_lehrverband.studiengang_kz, tbl_studiengang.bezeichnung, kurzbz, typ, tbl_lehrverband.semester, verband, gruppe, gruppe_kurzbz, tbl_lehrverband.bezeichnung AS lvb_bezeichnung, tbl_gruppe.bezeichnung AS grp_bezeichnung
			FROM (tbl_studiengang JOIN tbl_lehrverband USING (studiengang_kz))
				LEFT OUTER JOIN tbl_gruppe  ON (tbl_lehrverband.studiengang_kz=tbl_gruppe.studiengang_kz AND tbl_lehrverband.semester=tbl_gruppe.semester AND (tbl_lehrverband.verband=''))
			WHERE tbl_lehrverband.aktiv $stg_kz_query
			ORDER BY erhalter_kz,typ, kurzbz, semester,verband,gruppe, gruppe_kurzbz;";
//echo $sql_query;
if(!$result=pg_query($conn, $sql_query))
	$error_msg.=pg_errormessage($conn);
else
	$num_rows=pg_numrows($result);
	
$stsem_obj = new studiensemester($conn);
$stsem_obj->getAll();
?>

<RDF:RDF	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
		xmlns:VERBAND="<?php echo $rdf_url; ?>rdf#" >

<?php
$stg_kz=null;
$sem=null;
while ($row=pg_fetch_object($result))
{
	if ($stg_kz!=$row->studiengang_kz)
	{
		$stg_kz=$row->studiengang_kz;
		$stg_kurzbz=strtoupper($row->typ.$row->kurzbz);
		?>
		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz; ?>" >
			<VERBAND:name><?php echo $stg_kurzbz.' - '.$row->bezeichnung; ?></VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
		</RDF:Description>
		
		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/interessenten'; ?>" >
			<VERBAND:name>Interessenten</VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
			<VERBAND:typ>interessenten</VERBAND:typ>
		</RDF:Description>
		<?php
		foreach ($stsem_obj->studiensemester as $stsem)
		{
		?>
		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/interessenten/'.$stsem->studiensemester_kurzbz; ?>" >
			<VERBAND:name><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
			<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
			<VERBAND:typ>interessenten</VERBAND:typ>
		</RDF:Description>
		
		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/interessenten/'.$stsem->studiensemester_kurzbz.'/zgv'; ?>" >
			<VERBAND:name>ZGV erfüllt</VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
			<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
			<VERBAND:typ>zgv</VERBAND:typ>
		</RDF:Description>
		
		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/interessenten/'.$stsem->studiensemester_kurzbz.'/reihungstestangemeldet'; ?>" >
			<VERBAND:name>Reihungstest angemeldet</VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
			<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
			<VERBAND:typ>reihungstestangemeldet</VERBAND:typ>
		</RDF:Description>
		
		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/interessenten/'.$stsem->studiensemester_kurzbz.'/reihungstestnichtangemeldet'; ?>" >
			<VERBAND:name>Nicht zum Reihungstest angemeldet</VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
			<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
			<VERBAND:typ>reihungstestnichtangemeldet</VERBAND:typ>
		</RDF:Description>
		<?php 
		} 
		?>
		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/bewerber'; ?>" >
			<VERBAND:name>Bewerber</VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
			<VERBAND:typ>bewerber</VERBAND:typ>
		</RDF:Description>
				<?php
		foreach ($stsem_obj->studiensemester as $stsem)
		{
		?>
		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/bewerber/'.$stsem->studiensemester_kurzbz; ?>" >
			<VERBAND:name><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
			<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
			<VERBAND:typ>bewerber</VERBAND:typ>
		</RDF:Description>
		
		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/bewerber/'.$stsem->studiensemester_kurzbz.'/aufgenommen'; ?>" >
			<VERBAND:name>Aufgenommen</VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
			<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
			<VERBAND:typ>aufgenommen</VERBAND:typ>
		</RDF:Description>
		
		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/bewerber/'.$stsem->studiensemester_kurzbz.'/warteliste'; ?>" >
			<VERBAND:name>Warteliste</VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
			<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
			<VERBAND:typ>warteliste</VERBAND:typ>
		</RDF:Description>
		
		<RDF:Description RDF:about="<?php echo $rdf_url.$stg_kurzbz.'/bewerber/'.$stsem->studiensemester_kurzbz.'/absage'; ?>" >
			<VERBAND:name>Absage</VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
			<VERBAND:stsem><?php echo $stsem->studiensemester_kurzbz; ?></VERBAND:stsem>
			<VERBAND:typ>absage</VERBAND:typ>
		</RDF:Description>
		<?php
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
			<VERBAND:name><?php echo $row->gruppe_kurzbz.'-'.$row->grp_bezeichnung; ?></VERBAND:name>
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
			<VERBAND:name><?php echo $stg_kurzbz.'-'.$row->semester.$row->verband; ?></VERBAND:name>
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
			<VERBAND:name><?php echo $stg_kurzbz.'-'.$row->semester.$row->verband.$row->gruppe; ?></VERBAND:name>
			<VERBAND:stg><?php echo $stg_kurzbz; ?></VERBAND:stg>
			<VERBAND:stg_kz><?php echo $row->studiengang_kz; ?></VERBAND:stg_kz>
			<VERBAND:sem><?php echo $row->semester; ?></VERBAND:sem>
			<VERBAND:ver><?php echo $row->verband; ?></VERBAND:ver>
			<VERBAND:grp><?php echo $row->gruppe; ?></VERBAND:grp>
		</RDF:Description>
		<?php
	}
}
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
		$row=pg_fetch_object($result,$i);
		if ($stg_kz!=$row->studiengang_kz)
		{
  			if ($ver!=null)
				echo "\t\t\t\t\t\t</RDF:Seq>\n\t\t\t\t\t</RDF:li>\n";
			$ver=null;
			if ($sem!=null)
				echo "\t\t\t\t</RDF:Seq>\n\t\t\t</RDF:li>\n";
			$sem=null;
			if ($stg_kz!=null)
				echo "\t\t</RDF:Seq>\n\t</RDF:li>\n";
			$stg_kz=$row->studiengang_kz;
			$stg_kurzbz=strtoupper($row->typ.$row->kurzbz);
			echo "\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz\" />\n";
			echo "\t<RDF:li>\n\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz\">\n";
			//Interessenten
			echo "\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/interessenten\" />\n";
			echo "\t\t\t<RDF:li>\n\t\t\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz/interessenten\">\n";
			foreach ($stsem_obj->studiensemester as $stsem) 
			{
				echo "\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/interessenten/$stsem->studiensemester_kurzbz\" />\n";
				echo "\t\t\t<RDF:li>\n\t\t\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz/interessenten/$stsem->studiensemester_kurzbz\">\n";
				echo "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/interessenten/$stsem->studiensemester_kurzbz/zgv\" />\n";
				echo "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/interessenten/$stsem->studiensemester_kurzbz/reihungstestangemeldet\" />\n";
				echo "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/interessenten/$stsem->studiensemester_kurzbz/reihungstestnichtangemeldet\" />\n";
				echo "\t\t\t\t</RDF:Seq>\n\t\t\t</RDF:li>\n";
			}
			echo "\t\t\t</RDF:Seq>\n\t\t\t</RDF:li>\n";
			
			//Bewerber
			echo "\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/bewerber\" />\n";
			echo "\t\t\t<RDF:li>\n\t\t\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz/bewerber\">\n";
			foreach ($stsem_obj->studiensemester as $stsem) 
			{
				echo "\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/bewerber/$stsem->studiensemester_kurzbz\" />\n";
				echo "\t\t\t<RDF:li>\n\t\t\t\t<RDF:Seq RDF:about=\"$rdf_url$stg_kurzbz/bewerber/$stsem->studiensemester_kurzbz\">\n";
				echo "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/bewerber/$stsem->studiensemester_kurzbz/aufgenommen\" />\n";
				echo "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/bewerber/$stsem->studiensemester_kurzbz/warteliste\" />\n";
				echo "\t\t\t\t<RDF:li RDF:resource=\"$rdf_url$stg_kurzbz/bewerber/$stsem->studiensemester_kurzbz/absage\" />\n";
				echo "\t\t\t\t</RDF:Seq>\n\t\t\t</RDF:li>\n";
			}
			echo "\t\t\t\t</RDF:Seq>\n\t\t\t</RDF:li>\n";
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
		echo "\t\t\t\t\t\t</RDF:Seq>\n\t\t\t\t\t</RDF:li>\n";
		echo "\t\t\t\t</RDF:Seq>\n\t\t\t</RDF:li>\n";
		echo "\t\t</RDF:Seq>\n\t</RDF:li>\n";
	}
?>

</RDF:Seq>

</RDF:RDF>
