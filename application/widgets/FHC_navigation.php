<?php

/**
 *
 */
class FHC_navigation extends Widget
{
	const NAVIGATION_MENU = 'navigationMenu'; //

	private $navigationMenu;

	private static $FHC_navigationInstance;

	/**
	 *
	 */
	public function display($widgetData)
	{
		$this->navigationMenu = $widgetData;

		self::$FHC_navigationInstance = $this;

		$this->view('widgets/fhcnavigation');
	}

	/**
	 *
	 */
	public static function printNavigationMenu()
	{
		foreach (self::$FHC_navigationInstance->navigationMenu as $item)
			self::printNavItem($item);
	}

	/**
	 *
	 */
	public static function printNavItem($item, $depth = 1)
	{
		$expanded = isset($item['expand']) && $item['expand'] === true ? ' active' : '';
		echo '<li class="'.$expanded.'">
					<a href="'.$item['link'].'"'.$expanded.'>'.(isset($item['icon']) ? '<i class="fa fa-'.$item['icon'].' fa-fw"></i> ' : '').$item['description'].(!empty($item['children']) ? '<span class="fa arrow"></span>':'').'</a>';
		if (!empty($item['children']))
		{
			$level = '';
			if ($depth === 1)
				$level = 'second';
			elseif ($depth > 1)
				$level = 'third';

			echo '<ul class="nav nav-'.$level.'-level" '.$expanded.'>';
			foreach ($item['children'] as $child)
				self::printNavItem($child, ++$depth);
			echo '</ul>';
		}
		echo '</li>';
	}
}
