<?php
/*
       *  author: maximilian schremser <max@technikum-wien.at>
       *  date: 2004-06-26
       *  date-modified: 2004-10-28
       *  title: pgRS.class.php
*/

class pgRS
{
	var $arr  = array();
	var $num  = 0;
	var $CONN = 0;
	var $iid  = 0;

	function pgRS($conn,$sql)
	{
		//echo $sql;
		$this->CONN=$conn;
		return $this->query($sql);
	}

/*	function pgRS($sql, $dbname = "vilesci") {
		if (!$this->CONN)
			$this->CONN = ConnectDB($dbname);
			return $this->query($sql);
	}

	function pgRS($sql, $dbname = "vilesci", $user = "", $pw = "")
	{
		if (!$this->CONN)
			$this->CONN = ConnectDB($dbname, $user, $pw);
			return $this->query($sql);
	}
*/
	function query($sql)
	{
		static $selects = 0;
		//echo $sql;
		$result = pg_exec($this->CONN, $sql);
		if (strtolower(substr($sql,0,6))=="select")
		{
			$arr = array();
			++$selects;
			$row = 0;
			if (($num = pg_numrows($result)) > 0)
				$this->num = $num;

			while ($row < $this->num)
				$arr[] = pg_fetch_array($result,$row++,
						PGSQL_ASSOC);

			$this->arr = $arr;

		}
		//else if (strtolower(substr($sql,0,6))=="insert"){
			$this->iid = pg_last_oid($result);
		//}
	}
}

?>
