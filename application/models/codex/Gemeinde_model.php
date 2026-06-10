<?php

class Gemeinde_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "bis.tbl_gemeinde";
		$this->pk = "gemeinde_id";
	}
	
	public function getGemeindeByPlz($plz)
	{
		$this->addSelect("DISTINCT ON (ortschaftsname) ortschaftsname, gemeinde_id, plz, name, ortschaftskennziffer, bulacode, bulabez, kennziffer");
		$this->addOrder("ortschaftsname");
		
		return $this->loadWhere(array("plz" => $plz));
	}

	public function getGemeindeByNation($nation, $zip)
	{
		$this->addSelect(["name"]);

		if ($nation == "A") 
		{
			if (isset($zip) && $zip > 999 && $zip < 32000) 
			{
				$gemeinde_res = $this->GemeindeModel->loadWhere(['plz' => $zip]);
				if (isError($gemeinde_res))
				{
					show_error("error while trying to query bis.tbl_gemeinde");
				}
				$gemeinde_res = hasData($gemeinde_res) ? getData($gemeinde_res) : null;
				$gemeinde_res = array_map(function ($obj) {
					return $obj->name;
				}, $gemeinde_res);
				echo json_encode($gemeinde_res);

			} else {
				echo json_encode(error("ortschaftskennziffer code was not valid"));
			}
		}
	}

	public function checkLocation($plz, $gemeinde, $ort)
	{
		$this->db->where('ortschaftsname', $ort);
		$this->db->where('name', $gemeinde);
		$this->db->where('plz', $plz);

		return (boolean)$this->db->count_all_results($this->dbTable);
	}
}
