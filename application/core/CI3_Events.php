<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class CI3_Events
{
	const PRIORITY_LOW = 200;
	const PRIORITY_NORMAL = 100;
	const PRIORITY_HIGH = 10;

	private static $events = [];
	private static $eventsSorted = [];

	public static function on($event, $function, $priority = self::PRIORITY_NORMAL)
	{
		if (!isset(self::$events[$event]))
			self::$events[$event] = [];

		self::$events[$event][] = [$priority, $function];

		if (!isset(self::$eventsSorted[$event]))
			self::$eventsSorted[$event] = true;
		else
			self::$eventsSorted[$event] = false;
	}

	public static function trigger($event, ...$args)
	{
		if (!isset(self::$events[$event]))
			return;

		if (!self::$eventsSorted[$event]) {
			usort(self::$events[$event], function ($a, $b) {
				return $a[0] - $b[0];
			});
			self::$eventsSorted[$event] = true;
		}
		
		foreach (self::$events[$event] as $conf) {
			$conf[1](...$args);
		}
	}
}

/**
 * NOTE(chris): Autoload Events config
 */
require_once(APPPATH.'config/Events.php');

$active_addons_array = explode(";", ACTIVE_ADDONS);

foreach (scandir(APPPATH.'config/extensions') as $dir)
	if ($dir[0] != '.' && file_exists(APPPATH.'config/extensions/'.$dir.'/Events.php'))
		require_once APPPATH.'config/extensions/'.$dir.'/Events.php';

foreach (scandir(FHCPATH.'addons') as $dir)
	if ($dir[0] != '.' && file_exists(FHCPATH.'addons/'.$dir.'/Events.php'))
	{
		// only includes the Events of the addon if the addon is one of the active addons in the cis config
		if(in_array($dir,$active_addons_array))
		{
			require_once FHCPATH . 'addons/' . $dir . '/Events.php';
		}
	}
		
