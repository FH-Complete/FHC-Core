<?php

/**
 *
 */
class NavigationWidget extends Widget
{
	const NAVIGATION_HEADER = 'navigationHeader'; //
	const NAVIGATION_MENU = 'navigationMenu'; //

	/**
	 *
	 */
	public function display($widgetData)
	{
		$this->view('widgets/navigation', array('widgetData' => $widgetData));
	}
}
