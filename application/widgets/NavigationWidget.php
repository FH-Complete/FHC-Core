<?php

/**
 * Renders the navigation widget making use of the NavigationMenuWidget and the NavigationHeaderWidget
 */
class NavigationWidget extends Widget
{
	/**
	 * Renders the entire widget
	 */
	public function display($widgetData)
	{
		$this->view('widgets/navigation');
	}
}
