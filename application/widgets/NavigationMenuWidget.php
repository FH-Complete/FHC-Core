<?php

/**
 * Renders the left side menu
 */
class NavigationMenuWidget extends Widget
{
	/**
	 * Renders the left side menu
	 */
	public function display($widgetData)
	{
		$this->view('widgets/navigationMenu');
	}
}
