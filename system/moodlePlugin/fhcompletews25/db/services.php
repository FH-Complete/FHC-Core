<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Web service local plugin template external functions and service definitions.
 *
 * @package    localwstemplate
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
	'fhcomplete_get_course_grades' => array(
        'classname'   => 'local_fhcompletews_external',
        'methodname'  => 'get_course_grades',
        'classpath'   => 'local/fhcompletews/externallib.php',
        'description' => 'Get Grades of a course',
        'type'        => 'read',
        'capabilities'=> 'moodle/course:update,moodle/course:viewhiddencourses',
    ),

	'fhcomplete_courses_by_shortname' => array(
        'classname'   => 'local_fhcompletews_external',
        'methodname'  => 'get_courses_by_shortname',
        'classpath'   => 'local/fhcompletews/externallib.php',
        'description' => 'Get course contents by Shortname',
        'type'        => 'read',
        'capabilities'=> 'moodle/course:update,moodle/course:viewhiddencourses',
    ),

	'fhcomplete_user_get_users' => array(
        'classname'   => 'core_user_external',
        'methodname'  => 'get_users',
        'classpath'   => 'user/externallib.php',
        'description' => 'get Users by Criteria',
        'type'        => 'read',
        'capabilities'=> 'moodle/user:viewdetails, moodle/user:viewhiddendetails, moodle/course:useremail, moodle/user:update',
    ),

);
