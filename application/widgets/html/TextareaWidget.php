<?php

/**
 * It exends the Widget class to represent an HTML textarea
 */
class TextareaWidget extends HTMLWidget
{
	const TEXT = 'text'; // Text value
	const ROWS = 'rows'; // Rows attribute
	const COLS = 'cols'; // Cols attribute
	
	/**
	 * Set the text value
	 */
	protected function setText($text)
	{
		$this->_args[TextareaWidget::TEXT] = $text;
	}
	
	/**
	 * Loads the view that renders a text area
	 */
	protected function loadTextareaView()
	{
		$this->view('widgets/textarea', $this->_args);
	}
}