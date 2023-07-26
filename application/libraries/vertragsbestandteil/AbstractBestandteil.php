<?php
namespace vertragsbestandteil;

/**
 * Description of AbstractBestandteil
 *
 * @author bambi
 */
abstract class AbstractBestandteil
{
	protected $modifiedcolumns;
	protected $fromdb;
	
	public function __construct()
	{
		$this->modifiedcolumns = array();
		$this->fromdb  = false;
	}
	
	public function isDirty() {
		return count($this->modifiedcolumns) > 0;
	}
	
	protected function markDirty($columnname, $old_value, $new_value) {
		if( !$this->fromdb && ($old_value != $new_value) ) {
			$this->modifiedcolumns[$columnname] = $columnname;
		}
	}
	
	abstract public function hydrateByStdClass($data, $fromdb=false);
}
