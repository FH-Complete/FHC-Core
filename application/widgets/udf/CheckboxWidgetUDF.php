<?php

/**
 * 
 */
class CheckboxWidgetUDF extends CheckboxWidget
{
	/**
	 * 
	 */
	public function render()
	{
		$this->loadCheckboxView();
		
		echo $this->content();
    }
}