<?php

/**
 * It exends the TextareaWidget class to represent an HTML textarea
 */
class TextareaWidgetUDF extends TextareaWidget
{
	/**
	 * NOTE: echo $this->content() is needed
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