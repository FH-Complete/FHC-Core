<?php

/**
 * It exends the Widget class to represent an HTML textarea
 */
class TextareaWidget extends HTMLWidget
{
	const TEXT = 'text'; // 
	const ROWS = 'rows'; // 
	const COLS = 'cols'; // 
	
	/**
	 * 
	 */
	protected function setText($text)
	{
		$this->_args[TextareaWidget::TEXT] = $text;
	}
	
	/**
	 * 
	 */
	protected function loadTextareaView()
	{
		$this->view('widgets/textarea', $this->_args);
	}
}