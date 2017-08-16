<?php

/**
 * 
 */
class TextfieldWidgetUDF extends TextfieldWidget
{
	/**
	 * 
	 */
	public function render($parameters)
	{
		if ($parameters != null)
		{
			$this->setValue($parameters);
		}
		else
		{
			$this->setValue('');
		}
		
		$this->loadTextfieldView();
		
		echo $this->content();
    }
}