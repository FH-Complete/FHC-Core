<?php

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class ApiTester extends \Codeception\Actor
{
	const WAIT = 70000; // 0.07 seconds
	
    use _generated\ApiTesterActions;

   /**
    * Define custom actions here
    */
	
	public function wait($micro_seconds = null)
	{
		if (!is_null($micro_seconds))
		{
			usleep($micro_seconds);
		}
		else
		{
			usleep(ApiTester::WAIT);
		}
	}
}