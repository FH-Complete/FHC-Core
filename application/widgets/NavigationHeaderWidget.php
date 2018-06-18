<?php

/**
 * Renders the header menu
 */
class NavigationHeaderWidget extends Widget
{
	/**
	 * Renders the header menu
	 */
	public function display($widgetData)
	{
		$this->view('widgets/navigationHeader');
	}
}
