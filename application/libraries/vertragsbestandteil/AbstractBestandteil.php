<?php
namespace vertragsbestandteil;

use vertragsbestandteil\IValidation;

/**
 * Description of AbstractBestandteil
 *
 * @author bambi
 */
abstract class AbstractBestandteil implements IValidation
{
	protected $isvalid;
	protected $validationerrors;
	
	protected $modifiedcolumns;
	protected $fromdb;
	
	public function __construct()
	{
		$this->isvalid = false;
		$this->validationerrors = array();
		
		$this->modifiedcolumns = array();
		$this->fromdb  = false;
	}
	
	public function isDirty() {
		return count($this->modifiedcolumns) > 0;
	}
	
	protected function markDirty($columnname, $old_value, $new_value) {
		if( $this->fromdb ) {
			// data comes from db dont check for changes
			if( isset($this->modifiedcolumns[$columnname]) ) {
				unset($this->modifiedcolumns[$columnname]);
			}
			return;
		}	
		
		if( is_bool($new_value) && ($old_value !== $new_value) ) {
			$this->modifiedcolumns[$columnname] = $columnname;
		} else if($old_value != $new_value) {		
			$this->modifiedcolumns[$columnname] = $columnname;
		}
	}
	
	public function isValid()
	{
		return $this->isvalid;
	}

	public function getValidationErrors()
	{
		return $this->validationerrors;
	}

	
	public function addValidationError($errormsg)
	{
		if( !in_array($errormsg, $this->validationerrors, true) )
		{
			$this->validationerrors[] = $errormsg;
		}
		$this->isvalid = false;
	}
	
	abstract public function hydrateByStdClass($data, $fromdb=false);
}
