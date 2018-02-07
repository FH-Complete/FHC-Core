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

		echo '<li class="'.$expanded.'">';

		echo '<a href="'.$item['link'].'"'.$expanded.'>';

		if (isset($item['icon']))
		{
			echo '<i class="fa fa-'.$item['icon'].' fa-fw"></i> ';
		}

		// echo '<span>'.$item['description'].'</span>'.'<span style="">test</span>';
		echo $item['description'];

		if (!empty($item['children']))
		{
			echo '<span class="fa arrow"></span>';
		}

		echo '</a>';

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
