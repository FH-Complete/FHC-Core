<?php

/**
 * It exends the TextfieldWidget class to represent an HTML input text
 */
class TextfieldWidgetUDF extends TextfieldWidget
{
	/**
	 * NOTE: echo $this->content() is needed
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