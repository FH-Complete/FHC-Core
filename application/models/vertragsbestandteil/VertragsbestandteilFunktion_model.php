<?php
/**
 * Description of VertragsbestandteilFunktion_model
 *
 * @author bambi
 */
class VertragsbestandteilFunktion_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_vertragsbestandteil_funktion';
		$this->pk = 'vertragsbestandteil_id';
	}
	
	public function isBenutzerfunktionAlreadyAttachedToAnotherVB($benutzerfunktion_id, $vertragsbestandteil_id)
	{
		$where = array('benutzerfunktion_id' => $benutzerfunktion_id);
		if( intval($vertragsbestandteil_id) > 0 ) 
		{
			$where['vertragsbestandteil_id != '] = $vertragsbestandteil_id;
		}
		$this->addSelect('count(*) AS vbscount');
		$res = $this->loadWhere($where);
		if(isError($res)) 
		{
			throw new Exception('failed to check if benutzerfunktionid is already attached to another vertragsbestanteil');
		}
		$count = (getData($res))[0]->vbscount;
		return $count > 0;
	}
}
