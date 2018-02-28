<?php

/**
 *
 */
class NavigationMenuWidget extends Widget
{
	private $navigationMenu;

	private static $navigationMenuWidgetInstance;

	/**
	 *
	 */
	public function display($widgetData)
	{
		$this->navigationMenu = $widgetData;

		self::$navigationMenuWidgetInstance = $this;

		$this->view('widgets/navigationMenu');
	}

	/**
	 *
	 */
	public static function printNavigationMenu()
	{
		foreach (self::$navigationMenuWidgetInstance->navigationMenu as $item)
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

		if (isset($item['subscriptLinkId']) && isset($item['subscriptDescription']))
		{
			echo '<span>';
		}

		echo '<a href="'.$item['link'].'"'.$expanded.'>';

		if (isset($item['icon']))
		{
			echo '<i class="fa fa-'.$item['icon'].' fa-fw"></i> ';
		}

		echo $item['description'];

		if (!empty($item['children']))
		{
			echo '<span class="fa arrow"></span>';
		}

		echo '</a>';

		if (isset($item['subscriptLinkId']) && isset($item['subscriptDescription']))
		{
			echo '<a id="'.$item['subscriptLinkId'].'" class="menuSubscriptLink" value="'.$item['subscriptLinkValue'].'" href="#">'.$item['subscriptDescription'].'</a>';
		}

		if (isset($item['subscriptLinkId']) && isset($item['subscriptDescription']))
		{
			echo '</span>';
		}

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
