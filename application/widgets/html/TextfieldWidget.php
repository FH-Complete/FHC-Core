<?php

/**
 * It exends the Widget class to represent an HTML textarea
 */
class TextfieldWidget extends HTMLWidget
{
	const VALUE = 'text'; // 
	const SIZE = 'size'; // 
	
	/**
	 * 
	 */
	protected function setValue($value)
	{
		$this->_args[TextfieldWidget::VALUE] = $value;
	}
	
	/**
	 * 
	 */
	protected function loadTextfieldView()
	{
		$this->view('widgets/textfield', $this->_args);
	}
}