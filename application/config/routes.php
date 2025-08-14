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

$route['Cis/LvPlan/.*'] = 'Cis/LvPlan/index/$1';
$route['Cis/MyLvPlan/.*'] = 'Cis/MyLvPlan/index/$1';

// Studierendenverwaltung List Routes
$route['api/frontend/v1/stv/[sS]tudents/inout'] = 'api/frontend/v1/stv/Students/index';
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})'] = 'api/frontend/v1/stv/Students/index';

// (studiensemester_kurzbz)/inout[/(incoming|outgoing|gemeinsamestudien)]
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/inout'] = 'api/frontend/v1/stv/Students/index';
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/inout/incoming'] = 'api/frontend/v1/stv/Students/getIncoming';
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/inout/outgoing'] = 'api/frontend/v1/stv/Students/getOutgoing';
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/inout/gemeinsamestudien'] = 'api/frontend/v1/stv/Students/getGemeinsamestudien';

// (studiengang_kz)/prestudent[/(studiensemester_kurzbz)[/(filter)[/(otherfilter)]]]
$route['api/frontend/v1/stv/[sS]tudents/(:num)/prestudent'] = 'api/frontend/v1/stv/Students/getPrestudents/$1';
$route['api/frontend/v1/stv/[sS]tudents/(:num)/prestudent/([WS]S[0-9]{4})'] = 'api/frontend/v1/stv/Students/getPrestudents/$1/$2';
$route['api/frontend/v1/stv/[sS]tudents/(:num)/prestudent/([WS]S[0-9]{4})/(:any)'] = 'api/frontend/v1/stv/Students/getPrestudents/$1/$2/$3';
$route['api/frontend/v1/stv/[sS]tudents/(:num)/prestudent/([WS]S[0-9]{4})/(:any)/(:any)'] = 'api/frontend/v1/stv/Students/getPrestudents/$1/$2/$4';

// (studiengang_kz)/(orgform)/prestudent[/(studiensemester_kurzbz)[/(filter)[/(otherfilter)]]]
$route['api/frontend/v1/stv/[sS]tudents/(:num)/([A-Z]{2,3})/prestudent'] = 'api/frontend/v1/stv/Students/getPrestudentsOrgform/$1/$2';
$route['api/frontend/v1/stv/[sS]tudents/(:num)/([A-Z]{2,3})/prestudent/([WS]S[0-9]{4})'] = 'api/frontend/v1/stv/Students/getPrestudentsOrgform/$1/$2/$3';
$route['api/frontend/v1/stv/[sS]tudents/(:num)/([A-Z]{2,3})/prestudent/([WS]S[0-9]{4})/(:any)'] = 'api/frontend/v1/stv/Students/getPrestudentsOrgform/$1/$2/$3/$4';
$route['api/frontend/v1/stv/[sS]tudents/(:num)/([A-Z]{2,3})/prestudent/([WS]S[0-9]{4})/(:any)/(:any)'] = 'api/frontend/v1/stv/Students/getPrestudentsOrgform/$1/$2/$3/$5';

// (studiensemester_kurzbz)/(studiengang_kz)/(semester)/grp/(gruppe)
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/(-?[0-9]+)/(:num)/grp/(:any)'] = 'api/frontend/v1/stv/Students/getStudentsSpezialgruppe/$1/$2/$3/$4';

// (studiensemester_kurzbz)/(studiengang_kz)[/(semester)[/(verband)[/(gruppe)]]]
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/(-?[0-9]+)'] = 'api/frontend/v1/stv/Students/getStudents/$1/$2';
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/(-?[0-9]+)/(:num)'] = 'api/frontend/v1/stv/Students/getStudents/$1/$2/$3';
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/(-?[0-9]+)/(:num)/(:any)'] = 'api/frontend/v1/stv/Students/getStudents/$1/$2/$3/$4';
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/(-?[0-9]+)/(:num)/(:any)/(:any)'] = 'api/frontend/v1/stv/Students/getStudents/$1/$2/$3/$4/$5';

// (studiensemester_kurzbz)/(studiengang_kz)/(orgform)/(semester)/grp/(gruppe)
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/(-?[0-9]+)/([A-Z]{2,3})/(:num)/grp/(:any)'] = 'api/frontend/v1/stv/Students/getStudentsOrgformSpezialgruppe/$1/$2/$3/$4/$5';

// (studiensemester_kurzbz)/(studiengang_kz)/(orgform)[/(semester)[/(verband)[/(gruppe)]]]
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/(-?[0-9]+)/([A-Z]{2,3})'] = 'api/frontend/v1/stv/Students/getStudentsOrgform/$1/$2/$3';
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/(-?[0-9]+)/([A-Z]{2,3})/(:num)'] = 'api/frontend/v1/stv/Students/getStudentsOrgform/$1/$2/$3/$4';
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/(-?[0-9]+)/([A-Z]{2,3})/(:num)/(:any)'] = 'api/frontend/v1/stv/Students/getStudentsOrgform/$1/$2/$3/$4/$5';
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/(-?[0-9]+)/([A-Z]{2,3})/(:num)/(:any)/(:any)'] = 'api/frontend/v1/stv/Students/getStudentsOrgform/$1/$2/$3/$4/$5/$6';

// // (studiensemester_kurzbz)/uid/(uid)
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/uid/(:any)'] = 'api/frontend/v1/stv/Students/getStudent/$1/$2';
// // (studiensemester_kurzbz)/prestudent/(prestudent_id)
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/prestudent/(:num)'] = 'api/frontend/v1/stv/Students/getPrestudent/$1/$2';
// // (studiensemester_kurzbz)/person/(person_id)
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/person/(:num)'] = 'api/frontend/v1/stv/Students/getPerson/$1/$2';

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
