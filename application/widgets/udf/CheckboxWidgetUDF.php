<?php

/**
 * It exends the CheckboxWidget class to represent an HTML checkbox
 */
class CheckboxWidgetUDF extends CheckboxWidget
{
	/**
	 * NOTE: echo $this->content() is needed
	 */
	public function render()
	{
		$this->loadCheckboxView();
		
		echo $this->content();
    }
}