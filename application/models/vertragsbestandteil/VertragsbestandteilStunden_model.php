<?php
/**
 * Description of VertragsbestandteilStunden_model
 *
 * @author bambi
 */
class VertragsbestandteilStunden_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_vertragsbestandteil_stunden';
		$this->pk = 'vertragsbestandteil_id';
	}
}
