<?php
	header("Cache-Control: no-cache");
	header("Cache-Control: post-check=0, pre-check=0",false);
	header("Pragma: no-cache");
	if (isset($_GET['path']))
		$path=$_GET['path'];
	else
		$path='../';
	require ($path.'config/cis.config.inc.php');

	//die($_SERVER["HTTP_USER_AGENT"]);

	//setcookie('stylesheet', DEFAULT_STYLE);
	//Name des Stylesheets darf nur buchstaben von A-Z enthalten (ohne umlaute)
	if (isset($_COOKIE['stylesheet']) && preg_match('/^[a-zA-Z]+$/', $_COOKIE['stylesheet']))
	{
		$stylesheet=$_COOKIE['stylesheet'];
		if(!CHOOSE_LAYOUT)
			$stylesheet=DEFAULT_STYLE;
	}
	else
	{
		if (jahresplan_check_mobile())
			$stylesheet="mobile";
		else
			$stylesheet=DEFAULT_STYLE;
	}
	//$stylesheet="mobile";
	//setcookie('stylesheet', DEFAULT_STYLE);
	header("Content-Type: text/css");
	//echo $_COOKIE['stylesheet'];
	readfile ($path.'skin/styles/'.$stylesheet.'.css');
#--------------------------------------------------------------------------------------------------
#	$const=@get_defined_constants();
#	@reset($const);
#	print_r($const);
function jahresplan_check_mobile() {
  $agents = array(
    'Windows CE', 'Pocket', 'Mobile',
    'Portable', 'Smartphone', 'SDA',
    'PDA', 'Handheld', 'Symbian',
    'WAP', 'Palm', 'Avantgo',
    'cHTML', 'BlackBerry', 'Opera Mobi',
	'Nokia','iPhone','iPad','Android','BlackBerry',
  	'PlayBook','Kindle','Kindle Fire', 'Windows Phone'
  );
  // PrÃƒÂ¼fen der Browserkennung
  for ($i=0; $i<count($agents); $i++) {
    if(isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"], $agents[$i]) !== false)
      return true;
  }
  return false;
}
?>

