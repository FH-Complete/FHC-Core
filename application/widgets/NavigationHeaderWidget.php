<?php

/**
 *
 */
class NavigationHeaderWidget extends Widget
{
	/**
	 *
	 */
	public function display($data)
	{
		$this->view('widgets/navigationHeader', $data);
	}
}
