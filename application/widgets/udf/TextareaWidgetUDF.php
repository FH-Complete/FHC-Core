<?php

/**
 * 
 */
class TextareaWidgetUDF extends TextareaWidget
{
	/**
	 * 
	 */
	public function render($parameters)
	{
		if ($parameters != null)
		{
			$this->setText($parameters);
		}
		else
		{
			$this->setText('');
		}
		
		$this->loadTextareaView();
		
		echo $this->content();
    }
}