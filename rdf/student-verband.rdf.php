<?php
header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';
include('../vilesci/config.inc.php');
include('../include/berechtigung.class.php');

$rdf_url='http://www.technikum-wien.at/student-verband/';

if (!isset($REMOTE_USER))
	$REMOTE_USER='pam';
$uid=$REMOTE_USER;

if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

// Berechtigungen ermitteln
$berechtigung=new berechtigung($conn);
$berechtigung->getBerechtigungen($uid);
$berechtigt_studiengang=$berechtigung->getStgKz();
$stg_kz_query='';
if (count($berechtigt_studiengang)>0)
	if ($berechtigt_studiengang[0]!=0)
	{
		foreach ($berechtigt_studiengang as $b_stg)
			$stg_kz_query.=' OR studiengang_kz='.$b_stg;
		$stg_kz_query='AND ('.substr($stg_kz_query,3).')';
	}

$sql_query="SELECT studiengang_kz, bezeichnung, kurzbz FROM tbl_studiengang WHERE studiengang_kz>=0 $stg_kz_query ORDER BY bezeichnung";
//echo $sql_query;
if(!$result_stg=pg_query($conn, $sql_query))
	$error_msg.=pg_errormessage($conn);
else
	$num_rows_stg=@pg_numrows($result_stg);
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:VERBAND="<?php echo $rdf_url; ?>rdf#"
>

<?php
for ($i=0;$i<$num_rows_stg;$i++)
{
	$row_stg=@pg_fetch_object($result_stg, $i);
	?>
	<RDF:Description RDF:about="<?php echo $rdf_url.$row_stg->kurzbz; ?>" >
		<VERBAND:name><?php echo $row_stg->kurzbz.' - '.$row_stg->bezeichnung; ?></VERBAND:name>
    	<VERBAND:stg><?php echo $row_stg->kurzbz; ?></VERBAND:stg>
    	<VERBAND:stg_kz><?php echo $row_stg->studiengang_kz; ?></VERBAND:stg_kz>
   </RDF:Description>
   <?php
	$sql_query="SELECT DISTINCT semester FROM tbl_student WHERE studiengang_kz=$row_stg->studiengang_kz ORDER BY semester";
	if(!($result_sem=pg_query($conn, $sql_query)))
		die(pg_errormessage($conn));
	$num_rows_sem=pg_numrows($result_sem);
	for  ($j=0; $j<$num_rows_sem; $j++)
	{
		$row_sem=pg_fetch_object($result_sem, $j);
		?>
	   	<RDF:Description RDF:about="<?php echo $rdf_url.$row_stg->kurzbz.'/'.$row_sem->semester; ?>">
		   	<VERBAND:name><?php echo $row_stg->kurzbz.'-'.$row_sem->semester; ?></VERBAND:name>
    		<VERBAND:stg><?php echo $row_stg->kurzbz; ?></VERBAND:stg>
    		<VERBAND:stg_kz><?php echo $row_stg->studiengang_kz; ?></VERBAND:stg_kz>
		   	<VERBAND:sem><?php echo $row_sem->semester; ?></VERBAND:sem>
   		</RDF:Description>
		<?php
		$sql_query="SELECT DISTINCT verband FROM tbl_student WHERE verband!=' ' AND studiengang_kz=$row_stg->studiengang_kz AND semester=$row_sem->semester ORDER BY verband";
		if(!($result_ver=pg_exec($conn, $sql_query)))
			die(pg_errormessage($conn));
		$num_rows_ver=pg_numrows($result_ver);
		for  ($k=0; $k<$num_rows_ver; $k++)
		{
			$row_ver=pg_fetch_object($result_ver, $k);
			?>
			<RDF:Description RDF:about="<?php echo $rdf_url.$row_stg->kurzbz.'/'.$row_sem->semester.'/'.$row_ver->verband; ?>">
		   		<VERBAND:name><?php echo $row_stg->kurzbz.'-'.$row_sem->semester.$row_ver->verband; ?></VERBAND:name>
    			<VERBAND:stg><?php echo $row_stg->kurzbz; ?></VERBAND:stg>
    			<VERBAND:stg_kz><?php echo $row_stg->studiengang_kz; ?></VERBAND:stg_kz>
		   		<VERBAND:sem><?php echo $row_sem->semester; ?></VERBAND:sem>
		   		<VERBAND:ver><?php echo $row_ver->verband; ?></VERBAND:ver>
   			</RDF:Description>
   			<?php
			$sql_query="SELECT DISTINCT gruppe FROM tbl_student WHERE studiengang_kz=$row_stg->studiengang_kz AND semester=$row_sem->semester AND verband='$row_ver->verband' ORDER BY gruppe";
			if(!($result_grp=pg_exec($conn, $sql_query))) die(pg_errormessage($conn));
			$num_rows_grp=pg_numrows($result_grp);
			for  ($l=0; $l<$num_rows_grp; $l++)
			{
				$row_grp=pg_fetch_object($result_grp, $l);
				?>
				<RDF:Description RDF:about="<?php echo $rdf_url.$row_stg->kurzbz.'/'.$row_sem->semester.'/'.$row_ver->verband.'/'.$row_grp->gruppe; ?>">
					<VERBAND:name><?php echo $row_stg->kurzbz.'-'.$row_sem->semester.$row_ver->verband.$row_grp->gruppe; ?></VERBAND:name>
					<VERBAND:stg><?php echo $row_stg->kurzbz; ?></VERBAND:stg>
					<VERBAND:stg_kz><?php echo $row_stg->studiengang_kz; ?></VERBAND:stg_kz>
					<VERBAND:sem><?php echo $row_sem->semester; ?></VERBAND:sem>
					<VERBAND:ver><?php echo $row_ver->verband; ?></VERBAND:ver>
					<VERBAND:grp><?php echo $row_grp->gruppe; ?></VERBAND:grp>
   				</RDF:Description>
				<?php
			}
		}
		$sql_query="SELECT bezeichnung, gruppe_kurzbz FROM tbl_gruppe WHERE studiengang_kz=$row_stg->studiengang_kz AND semester=$row_sem->semester ORDER BY bezeichnung";
		//echo $sql_query;
		if(!($result_einh=pg_exec($conn, $sql_query)))
			die(pg_errormessage($conn));
		$num_rows_einh=pg_numrows($result_einh);
		for  ($m=0; $m<$num_rows_einh; $m++)
		{
			$row_einh=pg_fetch_object($result_einh, $m);
			?>
			<RDF:Description RDF:about="<?php echo $rdf_url.$row_stg->kurzbz.'/'.$row_sem->semester.'/'.$row_einh->gruppe_kurzbz; ?>">
		   		<VERBAND:name><?php echo $row_einh->gruppe_kurzbz.'-'.$row_einh->bezeichnung; ?></VERBAND:name>
    			<VERBAND:stg><?php echo $row_stg->kurzbz; ?></VERBAND:stg>
    			<VERBAND:stg_kz><?php echo $row_stg->studiengang_kz; ?></VERBAND:stg_kz>
		   		<VERBAND:sem><?php echo $row_sem->semester; ?></VERBAND:sem>
		   		<VERBAND:einheit><?php echo $row_einh->gruppe_kurzbz; ?></VERBAND:einheit>
   			</RDF:Description>
			<?php
		}
	}
}
?>

<RDF:Seq RDF:about="<?php echo $rdf_url.'alle-verbaende'; ?>">
<?php
for ($i=0;$i<$num_rows_stg;$i++)
{
	$row_stg=@pg_fetch_object($result_stg, $i);
	?>
	<RDF:li RDF:resource="<?php echo $rdf_url.$row_stg->kurzbz; ?>" />
	<RDF:li>
    	<RDF:Seq RDF:about="<?php echo $rdf_url.$row_stg->kurzbz; ?>">
		<?php
		$sql_query="SELECT DISTINCT semester FROM tbl_student WHERE studiengang_kz=$row_stg->studiengang_kz ORDER BY semester";
		if(!($result_sem=pg_query($conn, $sql_query)))
			die(pg_errormessage($conn));
		$num_rows_sem=pg_numrows($result_sem);
		for  ($j=0; $j<$num_rows_sem; $j++)
		{
			$row_sem=pg_fetch_object($result_sem, $j);
			?>
			<RDF:li RDF:resource="<?php echo $rdf_url.$row_stg->kurzbz.'/'.$row_sem->semester; ?>" />
			<RDF:li>
	  			<RDF:Seq RDF:about="<?php echo $rdf_url.$row_stg->kurzbz.'/'.$row_sem->semester; ?>">
				<?php
				$sql_query="SELECT DISTINCT verband FROM tbl_student WHERE verband!=' ' AND studiengang_kz=$row_stg->studiengang_kz AND semester=$row_sem->semester ORDER BY verband";
				if(!($result_ver=pg_exec($conn, $sql_query)))
					die(pg_errormessage($conn));
				$num_rows_ver=pg_numrows($result_ver);
				for  ($k=0; $k<$num_rows_ver; $k++)
				{
					$row_ver=pg_fetch_object($result_ver, $k);
					?>
					<RDF:li RDF:resource="<?php echo $rdf_url.$row_stg->kurzbz.'/'.$row_sem->semester.'/'.$row_ver->verband; ?>" />
					<RDF:li>
						<RDF:Seq RDF:about="<?php echo $rdf_url.$row_stg->kurzbz.'/'.$row_sem->semester.'/'.$row_ver->verband; ?>">
							<?php
							$sql_query="SELECT DISTINCT gruppe FROM tbl_student WHERE studiengang_kz=$row_stg->studiengang_kz AND semester=$row_sem->semester AND verband='$row_ver->verband' ORDER BY gruppe";
							if(!($result_grp=pg_exec($conn, $sql_query)))
								die(pg_errormessage($conn));
							$num_rows_grp=pg_numrows($result_grp);
							for  ($l=0; $l<$num_rows_grp; $l++)
							{
								$row_grp=pg_fetch_object($result_grp, $l);
								?>
								<RDF:li RDF:resource="<?php echo $rdf_url.$row_stg->kurzbz.'/'.$row_sem->semester.'/'.$row_ver->verband.'/'.$row_grp->gruppe; ?>" />
								<?php
							}
							?>
						</RDF:Seq>
    				</RDF:li>
					<?php
				}

			$sql_query="SELECT bezeichnung, gruppe_kurzbz FROM tbl_gruppe WHERE studiengang_kz=$row_stg->studiengang_kz AND semester=$row_sem->semester ORDER BY bezeichnung";
			//echo $sql_query;
			if(!($result_einh=pg_exec($conn, $sql_query)))
				die(pg_errormessage($conn));
			$num_rows_einh=pg_numrows($result_einh);
			for  ($m=0; $m<$num_rows_einh; $m++)
			{
				$row_einh=pg_fetch_object($result_einh, $m);
				?>
					<RDF:li RDF:resource="<?php echo $rdf_url.$row_stg->kurzbz.'/'.$row_sem->semester.'/'.$row_einh->gruppe_kurzbz; ?>" />
				<?php
			}
			?>
							</RDF:Seq>
						</RDF:li>
			<?php
		}
		?>
		</RDF:Seq>
	</RDF:li>
	<?php
}
?>

</RDF:Seq>

</RDF:RDF>
