<?php
class Lehretools_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_lehre_tools';
		$this->pk = 'lehre_tools_id';
	}
	
	/**
	 * 
	 * Laedt die Tools zu einer Lehrveranstaltung
	 * @param $lehrveranstaltung_id
	 * @param $studiensemester_kurzbz
	 */
	public function getTools($lehrveranstaltung_id, $studiensemester_kurzbz, $sprache)
	{
		$query = "SELECT 
					lehre_tools_id,
					bezeichnung[(SELECT index FROM public.tbl_sprache WHERE sprache = " . $this->escape($sprache) . ")] AS bezeichnung,
					kurzbz,
					basis_url,
					logo_dms_id
				FROM 
					campus.tbl_lehre_tools
					JOIN campus.tbl_lehre_tools_organisationseinheit USING(lehre_tools_id)
				WHERE
					campus.tbl_lehre_tools_organisationseinheit.aktiv AND
					(
						oe_kurzbz IN(		
							SELECT 
								tbl_studiengang.oe_kurzbz
							FROM
								lehre.tbl_lehrveranstaltung
								JOIN public.tbl_studiengang USING(studiengang_kz)
							WHERE
								tbl_lehrveranstaltung.lehrveranstaltung_id = " . $this->escape(intval($lehrveranstaltung_id)) . "
							)
						OR
						oe_kurzbz IN( 
							SELECT 
								lehrfach.oe_kurzbz
							FROM
								lehre.tbl_lehreinheit
								JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(lehrfach_id=lehrfach.lehrveranstaltung_id)
							WHERE
								tbl_lehreinheit.studiensemester_kurzbz = " . $this->escape($studiensemester_kurzbz) . "
								AND tbl_lehreinheit.lehrveranstaltung_id = " . $this->escape(intval($lehrveranstaltung_id)) . "
							)
					)
					ORDER BY lehre_tools_id";
			
		$toolsres = $this->execReadOnlyQuery($query);
		$tools = (hasData($toolsres)) ? getData($toolsres) : array();
		
		return $tools;
	}
}