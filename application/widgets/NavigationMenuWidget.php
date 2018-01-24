<?php

/**
 *
 */
class NavigationMenuWidget extends Widget
{
	private $navigationMenu;

	private static $NavigationMenuWidgetInstance;

	/**
	 *
	 */
	public function display($widgetData)
	{
		$this->navigationMenu = $widgetData;

		self::$NavigationMenuWidgetInstance = $this;

		$this->view('widgets/navigationMenu');
	}

	/**
	 *
	 */
	public static function printNavigationMenu()
	{
		foreach (self::$NavigationMenuWidgetInstance->navigationMenu as $item)
		{
			self::printNavItem($item);
		}
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
