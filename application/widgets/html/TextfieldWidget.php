<?php

/**
 * It exends the Widget class to represent an HTML textarea
 */
class TextfieldWidget extends HTMLWidget
{
	const VALUE = 'text'; // Text value
	const SIZE = 'size'; // Size attribute
	
	/**
	 * Set the text value
	 */
	protected function setValue($value)
	{
		$this->_args[TextfieldWidget::VALUE] = $value;
	}
	
	/**
	 * Loads the view that renders an input text
	 */
	protected function loadTextfieldView()
	{
		$this->view('widgets/textfield', $this->_args);
	}
}