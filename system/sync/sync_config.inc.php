<?php
	$dont_sync_php=array('11','91','92','94','145','182','203','204','222','227','254','255','256','257','258','297','298','299','302','327','328','329','476','999');
																			//'228','300','301','303','308','330','331','332','333','334','335','336'

	function dont_sync_sql($dont_sync_php)
	{
		$no_sync_sql='';
		foreach ($dont_sync_php AS $stgkz)
			$no_sync_sql.=' AND studiengang_kz!='.$stgkz;
		$no_sync_sql=substr($no_sync_sql,5);
		return $no_sync_sql;
	}
	$dont_sync_sql=dont_sync_sql($dont_sync_php);

	function dont_sync_sql_fas($dont_sync_php)
	{
		$no_sync_sql='';
		foreach ($dont_sync_php AS $stgkz)
			$no_sync_sql.=' AND studiengang.kennzahl!='.$stgkz;
		return $no_sync_sql;
	}
	$dont_sync_sql_fas=dont_sync_sql_fas($dont_sync_php);

	$adress='fas_sync@technikum-wien.at';
	//$adress='pam@technikum-wien.at';
	//$adress='ruhan@technikum-wien.at';
	$adress_stpl='lvplan@technikum-wien.at';
	//$adress_stpl='pam@technikum-wien.at';
	$adress_fas='fas_sync@technikum-wien.at';
?>