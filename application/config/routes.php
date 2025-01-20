<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
| example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
| https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
| $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
| $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
| $route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples: my-controller/index -> my_controller/index
|   my-controller/my-method -> my_controller/my_method
*/
$route['default_controller'] = defined('CIS4') && CIS4 ? 'Cis4' : 'Vilesci';
$route['translate_uri_dashes'] = FALSE;

// Class name conflicts
$route['api/v1/organisation/[S|s]tudiengang/(:any)'] = 'api/v1/organisation/studiengang2/$1';
$route['api/v1/organisation/[F|f]achbereich/(:any)'] = 'api/v1/organisation/fachbereich2/$1';
$route['api/v1/organisation/[G|g]eschaeftsjahr/(:any)'] = 'api/v1/organisation/geschaeftsjahr2/$1';
$route['api/v1/organisation/[O|o]rganisationseinheit/(:any)'] = 'api/v1/organisation/organisationseinheit2/$1';
$route['api/v1/ressource/[B|b]etriebsmittelperson/(:any)'] = 'api/v1/ressource/betriebsmittelperson2/$1';
$route['api/v1/system/[S|s]prache/(:any)'] = 'api/v1/system/sprache2/$1';

$route['CisVue'] = 'CisVue/dashboard';
$route['Cis/Stundenplan/(:any)'] = 'Cis/Stundenplan';

// load routes from extensions
$subdir = 'application/config/extensions';
$dirlist = scandir($subdir);

if ($dirlist)
{
	$files = array_diff($dirlist, array('.','..'));

	foreach ($files as &$item)
	{
		if (is_dir($subdir . DIRECTORY_SEPARATOR . $item))
		{
			$routes_file = $subdir . DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . 'routes.php';

			if (file_exists($routes_file))
			{
				require($routes_file);
			}
		}
	}
}