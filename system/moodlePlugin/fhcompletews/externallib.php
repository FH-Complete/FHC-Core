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
 * External Web Service Template
 *
 * @package    localwstemplate
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

class local_fhcompletews_external extends external_api 
{

/**************************************************
 * Webservice get_course_grades
 *
 * Laedt die Noten eines Kurses
 **************************************************/
	public static function get_course_grades_parameters() 
	{
        return new external_function_parameters(
                array('courseid' => new external_value(PARAM_INT, 'CourseID')), 'ID of the Course'
        );
    }

    /**
     * Get course Grades
     * @param int courseid
     * @return array
     */
    public static function get_course_grades($courseid) 
	{
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");
		require_once($CFG->dirroot.'/grade/export/lib.php');

        //validate parameter
        $params = self::validate_parameters(self::get_course_grades_parameters(),
                        array('courseid' => $courseid));

		$notenart=3; // 2=Prozent; 3=Endnote nach Skala
		$gui=array();	  
		$final_id='';
		$data = array();

		// Kursdaten Laden
		if (!$course = $DB->get_record('course', array('id'=>$courseid)))
		{
			throw new moodle_exception('Course not found', '', '', null, 'The course ' . $courseid . ' is not found');
			return false;
		}

		$id=$course->id;
		$kursname=$course->fullname;
		$shortname=$course->shortname;

		//ODS Notenexport starten
		require_login($course);
		$context = get_context_instance(CONTEXT_COURSE, $courseid);
		require_once($CFG->dirroot.'/grade/export/ods/grade_export_ods.php');

		if (!$export = new grade_export_ods($course, 0, 0, false, false, $notenart, 2))
		{
			throw new moodle_exception('Fehler', '', '', null, "Moodle-Kurs ".$id." ".$shortname." - keine Export Information gefunden");
			return false;
		}

		$grad =$export->columns;
	
		// Im Export sind die Noten fuer alle Abgaben, Quiz, etc enthalten
		// Wir brauchen hier nur die Gesamtnote fuer die ganzen Kurs
		foreach ($export->columns as $key=>$grade_item) 
		{
			// Gesamtnote hat den itemtype "course"
			if($grade_item->itemtype=='course')
			{
				$final_id=$key;
				$finalitem = $grade_item;
				break;
			}
		}

		if($final_id=='')
		{
			throw new moodle_exception('Fehler', '', '', null,"Moodle-Kurs ".$id." ".$shortname." - keine Endnote gefunden");
			return false;
		}

		// Liste mit allen Studierenden des Kurses durchlaufen
		$geub = new grade_export_update_buffer();	 
		$gui = new graded_users_iterator($export->course, array($final_id=>$finalitem), $export->groupid); //$export->columns

		$gui->init();	
		$kursgrad =array();

		while ($userdata = $gui->next_user()) 
		{
			$user_item=array();
		   	$user = $userdata->user;
		   	$user_item['vorname']=$user->firstname;
		   	$user_item['nachname']=$user->lastname;
		   	$user_item['idnummer']=$user->idnumber;
			$user_item['username']=$user->username;

			// Aus den vorhanden Noten wird die Endnote fuer den Kurs herausgesucht
			if(isset($userdata->grades[$final_id]))
			{
			  	$gradestr = $export->format_grade($userdata->grades[$final_id]);
		     	$user_item['note']=$gradestr;

				// nur zurueckliefern wenn eine Note gefunden wurde und diese nicht '-' ist
				if($user_item['note']!='-')
					$data[]=$user_item;
			}
		}
	
		$gui->close();
		$geub->close();
	
		if (count($data)==0)	
		{
			throw new moodle_exception('Fehler', '', '', null,"Moodle-Kurs ".$id." ".$shortname." - keine Kurs-Noten Informationen gefunden ");
			return false;
		}

		return $data;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_course_grades_returns() 
	{
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                            'vorname' => new external_value(PARAM_TEXT, 'vorname'),
                            'nachname' => new external_value(PARAM_TEXT, 'nachname'),
                            'idnummer' => new external_value(PARAM_TEXT, 'idnummer'),
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'note' => new external_value(PARAM_TEXT, 'note'),
                        ), 'course'
                )
        );
    }

/**************************************************
 * Webservice get_courses_by_shortname
 *
 * Laedt Kurse anhand der Kurzbezeichnung
 **************************************************/
	public static function get_courses_by_shortname_parameters() 
	{
        return new external_function_parameters(
                array('options' => new external_single_structure(
                            array('shortnames' => new external_multiple_structure(
                                        new external_value(PARAM_RAW, 'Short Name')
                                        , 'List of short names. If empty return all courses
                                            except front page course.',
                                        VALUE_OPTIONAL)
                            ), 'options - operator OR is used', VALUE_DEFAULT, array())
                )
        );
    }

    /**
     * Get courses
     * @param array $options
     * @return array
     */
    public static function get_courses_by_shortname($options) 
	{
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");

        //validate parameter
        $params = self::validate_parameters(self::get_courses_by_shortname_parameters(),
                        array('options' => $options));

        //retrieve courses
        if (!key_exists('shortnames', $params['options'])
                or empty($params['options']['shortnames'])) {
            $courses = $DB->get_records('course');
        } else {
            $courses = $DB->get_records_list('course', 'shortname', $params['options']['shortnames']);
        }

        //create return value
        $coursesinfo = array();
        foreach ($courses as $course) 
		{

            // now security checks
            $context = get_context_instance(CONTEXT_COURSE, $course->id);
            try 
			{
                self::validate_context($context);
            } 
			catch (Exception $e) 
			{
                $exceptionparam = new stdClass();
                $exceptionparam->message = $e->getMessage();
                $exceptionparam->shortname = $course->shortname;
                throw new moodle_exception(
                        get_string('errorcoursecontextnotvalid', 'webservice', $exceptionparam));
            }
            require_capability('moodle/course:view', $context);

            $courseinfo = array();
            $courseinfo['id'] = $course->id;
            $courseinfo['fullname'] = $course->fullname;
            $courseinfo['shortname'] = $course->shortname;
            $courseinfo['categoryid'] = $course->category;
            $courseinfo['summary'] = $course->summary;
            $courseinfo['summaryformat'] = $course->summaryformat;
            $courseinfo['format'] = $course->format;
            $courseinfo['startdate'] = $course->startdate;
            $courseinfo['numsections'] = $course->numsections;

            //some field should be returned only if the user has update permission
            $courseadmin = has_capability('moodle/course:update', $context);
            if ($courseadmin) 
			{
                $courseinfo['categorysortorder'] = $course->sortorder;
                $courseinfo['idnumber'] = $course->idnumber;
                $courseinfo['showgrades'] = $course->showgrades;
                $courseinfo['showreports'] = $course->showreports;
                $courseinfo['newsitems'] = $course->newsitems;
                $courseinfo['visible'] = $course->visible;
                $courseinfo['maxbytes'] = $course->maxbytes;
                $courseinfo['hiddensections'] = $course->hiddensections;
                $courseinfo['groupmode'] = $course->groupmode;
                $courseinfo['groupmodeforce'] = $course->groupmodeforce;
                $courseinfo['defaultgroupingid'] = $course->defaultgroupingid;
                $courseinfo['lang'] = $course->lang;
                $courseinfo['timecreated'] = $course->timecreated;
                $courseinfo['timemodified'] = $course->timemodified;
                $courseinfo['forcetheme'] = $course->theme;
                $courseinfo['enablecompletion'] = $course->enablecompletion;
                $courseinfo['completionstartonenrol'] = $course->completionstartonenrol;
                $courseinfo['completionnotify'] = $course->completionnotify;
            }

            if ($courseadmin or $course->visible
                    or has_capability('moodle/course:viewhiddencourses', $context)) 
			{
                $coursesinfo[] = $courseinfo;
            }
        }

        return $coursesinfo;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_courses_by_shortname_returns() 
	{
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                            'categoryid' => new external_value(PARAM_INT, 'category id'),
                            'categorysortorder' => new external_value(PARAM_INT,
                                    'sort order into the category', VALUE_OPTIONAL),
                            'fullname' => new external_value(PARAM_TEXT, 'full name'),
                            'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
                            'summary' => new external_value(PARAM_RAW, 'summary'),
                            'summaryformat' => new external_value(PARAM_INT,
                                    'the summary text Moodle format'),
                            'format' => new external_value(PARAM_ALPHANUMEXT,
                                    'course format: weeks, topics, social, site,..'),
                            'showgrades' => new external_value(PARAM_INT,
                                    '1 if grades are shown, otherwise 0', VALUE_OPTIONAL),
                            'newsitems' => new external_value(PARAM_INT,
                                    'number of recent items appearing on the course page', VALUE_OPTIONAL),
                            'startdate' => new external_value(PARAM_INT,
                                    'timestamp when the course start'),
                            'numsections' => new external_value(PARAM_INT, 'number of weeks/topics'),
                            'maxbytes' => new external_value(PARAM_INT,
                                    'largest size of file that can be uploaded into the course',
                                    VALUE_OPTIONAL),
                            'showreports' => new external_value(PARAM_INT,
                                    'are activity report shown (yes = 1, no =0)', VALUE_OPTIONAL),
                            'visible' => new external_value(PARAM_INT,
                                    '1: available to student, 0:not available', VALUE_OPTIONAL),
                            'hiddensections' => new external_value(PARAM_INT,
                                    'How the hidden sections in the course are displayed to students',
                                    VALUE_OPTIONAL),
                            'groupmode' => new external_value(PARAM_INT, 'no group, separate, visible',
                                    VALUE_OPTIONAL),
                            'groupmodeforce' => new external_value(PARAM_INT, '1: yes, 0: no',
                                    VALUE_OPTIONAL),
                            'defaultgroupingid' => new external_value(PARAM_INT, 'default grouping id',
                                    VALUE_OPTIONAL),
                            'timecreated' => new external_value(PARAM_INT,
                                    'timestamp when the course have been created', VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_INT,
                                    'timestamp when the course have been modified', VALUE_OPTIONAL),
                            'enablecompletion' => new external_value(PARAM_INT,
                                    'Enabled, control via completion and activity settings. Disbaled,
                                        not shown in activity settings.',
                                    VALUE_OPTIONAL),
                            'completionstartonenrol' => new external_value(PARAM_INT,
                                    '1: begin tracking a student\'s progress in course completion
                                        after course enrolment. 0: does not',
                                    VALUE_OPTIONAL),
                            'completionnotify' => new external_value(PARAM_INT,
                                    '1: yes 0: no', VALUE_OPTIONAL),
                            'lang' => new external_value(PARAM_ALPHANUMEXT,
                                    'forced course language', VALUE_OPTIONAL),
                            'forcetheme' => new external_value(PARAM_ALPHANUMEXT,
                                    'name of the force theme', VALUE_OPTIONAL),
                        ), 'course'
                )
        );
    }


/***********************************************************************
 * get_users - Laedt User Anhand des Usernamens
 * Backport von Moodle 2.5
 * Ab Moodle 2.5 sollte dieses Webservice bereits integriert sein 
 ***********************************************************************/


	/**
	* Returns description of get_users() parameters.
	*
	* @return external_function_parameters
	* @since Moodle 2.5
	*/
    public static function get_users_parameters() {
        return new external_function_parameters(
            array(
                'criteria' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'key' => new external_value(PARAM_ALPHA, 'the user column to search, expected keys (value format) are:
"id" (int) matching user id,
"lastname" (string) user last name (Note: you can use % for searching but it may be considerably slower!),
"firstname" (string) user first name (Note: you can use % for searching but it may be considerably slower!),
"idnumber" (string) matching user idnumber,
"username" (string) matching user username,
"email" (string) user email (Note: you can use % for searching but it may be considerably slower!),
"auth" (string) matching user auth plugin'),
                            'value' => new external_value(PARAM_RAW, 'the value to search')
                        )
                    ), 'the key/value pairs to be considered in user search. Values can not be empty.
Specify different keys only once (fullname => \'user1\', auth => \'manual\', ...) -
key occurences are forbidden.
The search is executed with AND operator on the criterias. Invalid criterias (keys) are ignored,
the search is still executed on the valid criterias.
You can search without criteria, but the function is not designed for it.
It could very slow or timeout. The function is designed to search some specific users.'
                )
            )
        );
    }

    /**
	* Retrieve matching user.
	*
	* @param array $criteria the allowed array keys are id/lastname/firstname/idnumber/username/email/auth.
	* @return array An array of arrays containing user profiles.
	* @since Moodle 2.5
	*/
    public static function get_users($criteria = array()) {
        global $CFG, $USER, $DB;

        require_once($CFG->dirroot . "/user/lib.php");

        $params = self::validate_parameters(self::get_users_parameters(),
                array('criteria' => $criteria));

        // Validate the criteria and retrieve the users.
        $users = array();
        $warnings = array();
        $sqlparams = array();
        $usedkeys = array();

        // Do not retrieve deleted users.
        $sql = ' deleted = 0';

        foreach ($params['criteria'] as $criteriaindex => $criteria) {

            // Check that the criteria has never been used.
            if (array_key_exists($criteria['key'], $usedkeys)) {
                throw new moodle_exception('keyalreadyset', '', '', null, 'The key ' . $criteria['key'] . ' can only be sent once');
            } else {
                $usedkeys[$criteria['key']] = true;
            }

            $invalidcriteria = false;
            // Clean the parameters.
            $paramtype = PARAM_RAW;
            switch ($criteria['key']) {
                case 'id':
                    $paramtype = PARAM_INT;
                    break;
                case 'idnumber':
                    $paramtype = PARAM_RAW;
                    break;
                case 'username':
                    $paramtype = PARAM_RAW;
                    break;
                case 'email':
                    // We use PARAM_RAW to allow searches with %.
                    $paramtype = PARAM_RAW;
                    break;
                case 'auth':
                    $paramtype = PARAM_AUTH;
                    break;
                case 'lastname':
                case 'firstname':
                    $paramtype = PARAM_TEXT;
                    break;
                default:
                    // Send back a warning that this search key is not supported in this version.
                    // This warning will make the function extandable without breaking clients.
                    $warnings[] = array(
                        'item' => $criteria['key'],
                        'warningcode' => 'invalidfieldparameter',
                        'message' => 'The search key \'' . $criteria['key'] . '\' is not supported, look at the web service documentation'
                    );
                    // Do not add this invalid criteria to the created SQL request.
                    $invalidcriteria = true;
                    unset($params['criteria'][$criteriaindex]);
                    break;
            }

            if (!$invalidcriteria) {
                $cleanedvalue = clean_param($criteria['value'], $paramtype);

                $sql .= ' AND ';

                // Create the SQL.
                switch ($criteria['key']) {
                    case 'id':
                    case 'idnumber':
                    case 'username':
                    case 'auth':
                        $sql .= $criteria['key'] . ' = :' . $criteria['key'];
                        $sqlparams[$criteria['key']] = $cleanedvalue;
                        break;
                    case 'email':
                    case 'lastname':
                    case 'firstname':
                        $sql .= $DB->sql_like($criteria['key'], ':' . $criteria['key'], false);
                        $sqlparams[$criteria['key']] = $cleanedvalue;
                        break;
                    default:
                        break;
                }
            }
        }

        $users = $DB->get_records_select('user', $sql, $sqlparams, 'id ASC');

        // Finally retrieve each users information.
        $returnedusers = array();
        foreach ($users as $user) {
            $userdetails = user_get_user_details_courses($user);

            // Return the user only if all the searched fields are returned.
            // Otherwise it means that the $USER was not allowed to search the returned user.
            if (!empty($userdetails)) {
                $validuser = true;

                foreach($params['criteria'] as $criteria) {
                    if (empty($userdetails[$criteria['key']])) {
                        $validuser = false;
                    }
                }

                if ($validuser) {
                    $returnedusers[] = $userdetails;
                }
            }
        }

        return array('users' => $returnedusers, 'warnings' => $warnings);
    }

    /**
	* Returns description of get_users result value.
	*
	* @return external_description
	* @since Moodle 2.5
	*/
    public static function get_users_returns() {
        return new external_single_structure(
            array('users' => new external_multiple_structure(
                                self::user_description()
                             ),
                  'warnings' => new external_warnings('always set to \'key\'', 'faulty key name')
            )
        );
    }

	/**
	* Create user return value description.
	*
	* @param array $additionalfields some additional field
	* @return single_structure_description
	*/
    public static function user_description($additionalfields = array()) {
        $userfields = array(
                    'id' => new external_value(PARAM_INT, 'ID of the user'),
                    'username' => new external_value(PARAM_RAW, 'The username', VALUE_OPTIONAL),
                    'firstname' => new external_value(PARAM_NOTAGS, 'The first name(s) of the user', VALUE_OPTIONAL),
                    'lastname' => new external_value(PARAM_NOTAGS, 'The family name of the user', VALUE_OPTIONAL),
                    'fullname' => new external_value(PARAM_NOTAGS, 'The fullname of the user'),
                    'email' => new external_value(PARAM_TEXT, 'An email address - allow email as root@localhost', VALUE_OPTIONAL),
                    'address' => new external_value(PARAM_TEXT, 'Postal address', VALUE_OPTIONAL),
                    'phone1' => new external_value(PARAM_NOTAGS, 'Phone 1', VALUE_OPTIONAL),
                    'phone2' => new external_value(PARAM_NOTAGS, 'Phone 2', VALUE_OPTIONAL),
                    'icq' => new external_value(PARAM_NOTAGS, 'icq number', VALUE_OPTIONAL),
                    'skype' => new external_value(PARAM_NOTAGS, 'skype id', VALUE_OPTIONAL),
                    'yahoo' => new external_value(PARAM_NOTAGS, 'yahoo id', VALUE_OPTIONAL),
                    'aim' => new external_value(PARAM_NOTAGS, 'aim id', VALUE_OPTIONAL),
                    'msn' => new external_value(PARAM_NOTAGS, 'msn number', VALUE_OPTIONAL),
                    'department' => new external_value(PARAM_TEXT, 'department', VALUE_OPTIONAL),
                    'institution' => new external_value(PARAM_TEXT, 'institution', VALUE_OPTIONAL),
                    'idnumber' => new external_value(PARAM_RAW, 'An arbitrary ID code number perhaps from the institution', VALUE_OPTIONAL),
                    'interests' => new external_value(PARAM_TEXT, 'user interests (separated by commas)', VALUE_OPTIONAL),
                    'firstaccess' => new external_value(PARAM_INT, 'first access to the site (0 if never)', VALUE_OPTIONAL),
                    'lastaccess' => new external_value(PARAM_INT, 'last access to the site (0 if never)', VALUE_OPTIONAL),
                    'auth' => new external_value(PARAM_PLUGIN, 'Auth plugins include manual, ldap, imap, etc', VALUE_OPTIONAL),
                    'confirmed' => new external_value(PARAM_INT, 'Active user: 1 if confirmed, 0 otherwise', VALUE_OPTIONAL),
                    'lang' => new external_value(PARAM_SAFEDIR, 'Language code such as "en", must exist on server', VALUE_OPTIONAL),
                    'theme' => new external_value(PARAM_PLUGIN, 'Theme name such as "standard", must exist on server', VALUE_OPTIONAL),
                    'timezone' => new external_value(PARAM_TIMEZONE, 'Timezone code such as Australia/Perth, or 99 for default', VALUE_OPTIONAL),
                    'mailformat' => new external_value(PARAM_INT, 'Mail format code is 0 for plain text, 1 for HTML etc', VALUE_OPTIONAL),
                    'description' => new external_value(PARAM_RAW, 'User profile description', VALUE_OPTIONAL),
                    'descriptionformat' => new external_format_value('description', VALUE_OPTIONAL),
                    'city' => new external_value(PARAM_NOTAGS, 'Home city of the user', VALUE_OPTIONAL),
                    'url' => new external_value(PARAM_URL, 'URL of the user', VALUE_OPTIONAL),
                    'country' => new external_value(PARAM_ALPHA, 'Home country code of the user, such as AU or CZ', VALUE_OPTIONAL),
                    'profileimageurlsmall' => new external_value(PARAM_URL, 'User image profile URL - small version'),
                    'profileimageurl' => new external_value(PARAM_URL, 'User image profile URL - big version'),
                    'customfields' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'type' => new external_value(PARAM_ALPHANUMEXT, 'The type of the custom field - text field, checkbox...'),
                                'value' => new external_value(PARAM_RAW, 'The value of the custom field'),
                                'name' => new external_value(PARAM_RAW, 'The name of the custom field'),
                                'shortname' => new external_value(PARAM_RAW, 'The shortname of the custom field - to be able to build the field class in the code'),
                            )
                        ), 'User custom fields (also known as user profile fields)', VALUE_OPTIONAL),
                    'preferences' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'name' => new external_value(PARAM_ALPHANUMEXT, 'The name of the preferences'),
                                'value' => new external_value(PARAM_RAW, 'The value of the custom field'),
                            )
                    ), 'Users preferences', VALUE_OPTIONAL)
                );
        if (!empty($additionalfields)) {
            $userfields = array_merge($userfields, $additionalfields);
        }
        return new external_single_structure($userfields);
    }
}

/**
 * Tries to obtain user details, either recurring directly to the user's system profile
 * or through one of the user's course enrollments (course profile).
 *
 * @param object $user The user.
 * @return array if unsuccessful or the allowed user details.
 */
function user_get_user_details_courses($user) {
    global $USER;
    $userdetails = null;

    //  Get the courses that the user is enrolled in (only active).
    $courses = enrol_get_users_courses($user->id, true);

    $systemprofile = false;
    if (can_view_user_details_cap($user) || ($user->id == $USER->id) || has_coursecontact_role($user->id)) {
        $systemprofile = true;
    }

    // Try using system profile.
    if ($systemprofile) {
        $userdetails = user_get_user_details($user, null);
    } else {
        // Try through course profile.
        foreach ($courses as $course) {
            if ($can_view_user_details_cap($user, $course) || ($user->id == $USER->id) || has_coursecontact_role($user->id)) {
                $userdetails = user_get_user_details($user, $course);
            }
        }
    }

    return $userdetails;
}


/**
 * Check if $USER have the necessary capabilities to obtain user details.
 *
 * @param object $user
 * @param object $course if null then only consider system profile otherwise also consider the course's profile.
 * @return bool true if $USER can view user details.
 */
function can_view_user_details_cap($user, $course = null) {
    // Check $USER has the capability to view the user details at user context.
    $usercontext = get_context_instance(CONTEXT_USER, $user->id);
    $result = has_capability('moodle/user:viewdetails', $usercontext);
    // Otherwise can $USER see them at course context.
    if (!$result && !empty($course)) {
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        $result = has_capability('moodle/user:viewdetails', $context);
    }
    return $result;
}
