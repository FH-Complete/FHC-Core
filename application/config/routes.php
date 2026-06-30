<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

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
$route['translate_uri_dashes'] = false;

// Class name conflicts
$route['api/v1/organisation/[S|s]tudiengang/(:any)'] = 'api/v1/organisation/studiengang2/$1';
$route['api/v1/organisation/[F|f]achbereich/(:any)'] = 'api/v1/organisation/fachbereich2/$1';
$route['api/v1/organisation/[G|g]eschaeftsjahr/(:any)'] = 'api/v1/organisation/geschaeftsjahr2/$1';
$route['api/v1/organisation/[O|o]rganisationseinheit/(:any)'] = 'api/v1/organisation/organisationseinheit2/$1';
$route['api/v1/ressource/[B|b]etriebsmittelperson/(:any)'] = 'api/v1/ressource/betriebsmittelperson2/$1';
$route['api/v1/system/[S|s]prache/(:any)'] = 'api/v1/system/sprache2/$1';

$route['Cis/LvPlan/.*'] = 'Cis/LvPlan/index/$1';
$route['Cis/MyLvPlan/.*'] = 'Cis/MyLvPlan/index/$1';
$route['Cis/MyLv/.*'] = 'Cis/MyLv/index/$1';

$route['Abgabetool/Assistenz'] = 'Cis/Abgabetool/Assistenz';
$route['Abgabetool/Assistenz/(:any)'] = 'Cis/Abgabetool/Assistenz/$1';
$route['Abgabetool/Mitarbeiter'] = 'Cis/Abgabetool/Mitarbeiter';
$route['Abgabetool/Student'] = 'Cis/Abgabetool/Student';
$route['Abgabetool/Student/(:any)'] = 'Cis/Abgabetool/Student/$1';
$route['Abgabetool/Deadlines'] = 'Cis/Abgabetool/Deadlines';

// Studierendenverwaltung List Routes
$route['api/frontend/v1/treemenudata/stv/stdsem/(:any)/inout/1/incoming/1'] = 'api/frontend/v1/stv/students/getIncoming/$1';
$route['api/frontend/v1/treemenudata/stv/stdsem/(:any)/inout/1/outgoing/1'] = 'api/frontend/v1/stv/students/getOutgoing/$1';
$route['api/frontend/v1/treemenudata/stv/stdsem/(:any)/inout/1/shared_studies/1'] = 'api/frontend/v1/stv/students/getGemeinsamestudien/$1';

$route['api/frontend/v1/treemenudata/stv/stdsem/:any/stg/(:any)/prestudent/1/stdsem/(:any)/:any/1/(:any)/1'] = 'api/frontend/v1/stv/students/getPrestudents/$1/$2/$3';
$route['api/frontend/v1/treemenudata/stv/stdsem/:any/stg/(:any)/prestudent/1/stdsem/(:any)/(:any)/1'] = 'api/frontend/v1/stv/students/getPrestudents/$1/$2/$3';
$route['api/frontend/v1/treemenudata/stv/stdsem/:any/stg/(:any)/prestudent/1/stdsem/(:any)'] = 'api/frontend/v1/stv/students/getPrestudents/$1/$2';
$route['api/frontend/v1/treemenudata/stv/stdsem/:any/stg/(:any)/prestudent/1'] = 'api/frontend/v1/stv/students/getPrestudents/$1';

$route['api/frontend/v1/treemenudata/stv/stdsem/:any/stg/(:any)/orgform/(:any)/prestudent/1/stdsem/(:any)/:any/1/(:any)/1'] = 'api/frontend/v1/stv/students/getPrestudentsOrgform/$1/$2/$3/$4';
$route['api/frontend/v1/treemenudata/stv/stdsem/:any/stg/(:any)/orgform/(:any)/prestudent/1/stdsem/(:any)/(:any)/1'] = 'api/frontend/v1/stv/students/getPrestudentsOrgform/$1/$2/$3/$4';
$route['api/frontend/v1/treemenudata/stv/stdsem/:any/stg/(:any)/orgform/(:any)/prestudent/1/stdsem/(:any)'] = 'api/frontend/v1/stv/students/getPrestudentsOrgform/$1/$2/$3';
$route['api/frontend/v1/treemenudata/stv/stdsem/:any/stg/(:any)/orgform/(:any)/prestudent/1'] = 'api/frontend/v1/stv/students/getPrestudentsOrgform/$1/$2';

$route['api/frontend/v1/treemenudata/stv/stdsem/(:any)/stg/(:any)/semester/(:any)/verband/(:any)/group/(:any)'] = 'api/frontend/v1/stv/students/getStudents/$1/$2/$3/$4/$5';
$route['api/frontend/v1/treemenudata/stv/stdsem/(:any)/stg/(:any)/semester/(:any)/verband/(:any)'] = 'api/frontend/v1/stv/students/getStudents/$1/$2/$3/$4';
$route['api/frontend/v1/treemenudata/stv/stdsem/(:any)/stg/(:any)/semester/(:any)'] = 'api/frontend/v1/stv/students/getStudents/$1/$2/$3';
$route['api/frontend/v1/treemenudata/stv/stdsem/(:any)/stg/(:any)'] = 'api/frontend/v1/stv/students/getStudents/$1/$2';

$route['api/frontend/v1/treemenudata/stv/stdsem/(:any)/stg/(:any)/orgform/(:any)/semester/(:any)/verband/(:any)/group/(:any)'] = 'api/frontend/v1/stv/students/getStudentsOrgform/$1/$2/$3/$4/$5/$6';
$route['api/frontend/v1/treemenudata/stv/stdsem/(:any)/stg/(:any)/orgform/(:any)/semester/(:any)/verband/(:any)'] = 'api/frontend/v1/stv/students/getStudentsOrgform/$1/$2/$3/$4/$5';
$route['api/frontend/v1/treemenudata/stv/stdsem/(:any)/stg/(:any)/orgform/(:any)/semester/(:any)'] = 'api/frontend/v1/stv/students/getStudentsOrgform/$1/$2/$3/$4';
$route['api/frontend/v1/treemenudata/stv/stdsem/(:any)/stg/(:any)/orgform/(:any)'] = 'api/frontend/v1/stv/students/getStudentsOrgform/$1/$2/$3';

$route['api/frontend/v1/treemenudata/stv/stdsem/(:any)/stg/(:any)/semester/(:any)/group/(:any)'] = 'api/frontend/v1/stv/students/getStudentsSpezialgruppe/$1/$2/$3/$4';

$route['api/frontend/v1/treemenudata/stv/stdsem/(:any)/stg/(:any)/orgform/(:any)/semester/(:any)/group/(:any)'] = 'api/frontend/v1/stv/students/getStudentsOrgformSpezialgruppe/$1/$3/$2/$4/$5';

$route['api/frontend/v1/treemenudata/stv/.*'] = 'api/frontend/v1/stv/students/index';


// // (studiensemester_kurzbz)/uid/(uid)
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/uid/(:any)'] = 'api/frontend/v1/stv/Students/getStudent/$1/$2';
// // (studiensemester_kurzbz)/prestudent/(prestudent_id)
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/prestudent/(:num)'] = 'api/frontend/v1/stv/Students/getPrestudent/$1/$2';
// // (studiensemester_kurzbz)/person/(person_id)
$route['api/frontend/v1/stv/[sS]tudents/([WS]S[0-9]{4})/person/(:num)'] = 'api/frontend/v1/stv/Students/getPerson/$1/$2';

// load routes from extensions, also look for environment-specific configs
$subdirs = ['application/config/extensions', 'application/config/' . ENVIRONMENT . '/extensions'];

foreach($subdirs as $subdir)
{
	if(is_dir($subdir))
	{
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
	}
}
