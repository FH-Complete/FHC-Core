<?php
	$dont_sync_php=array('254','255','256','257','258','297','298','299','302','327','328','329','999');
//	$dont_sync_php=array('256','257','258','297','299','302','327','328','329','999');

	$dont_sync_sql='studiengang_kz!=256 AND studiengang_kz!=257 AND studiengang_kz!=258
		AND studiengang_kz!=297 AND studiengang_kz!=299 AND studiengang_kz!=302 AND
		studiengang_kz!=327 AND studiengang_kz!=328 AND studiengang_kz!=329 AND studiengang_kz!=999';

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