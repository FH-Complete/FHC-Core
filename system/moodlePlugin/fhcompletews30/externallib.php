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
                array(
					'courseid' => new external_value(PARAM_INT, 'Moodle CourseID'),
					'type' => new external_value(PARAM_INT,'Type 1=Punkte, 2=Prozent, 3=Endnote lt Skala')
				), 'Get Course Grades'
        );
    }

    /**
     * Get course Grades
     * @param int courseid
     * @return array
     */
    public static function get_course_grades($courseid, $type) 
	{
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");
		require_once($CFG->dirroot.'/grade/export/lib.php');

        //validate parameter
        $params = self::validate_parameters(self::get_course_grades_parameters(),
                        array('courseid' => $courseid, 'type'=>$type));

		$notenart = $type;
		//$notenart=2; // 1=Punkte; 2=Prozent; 3=Endnote nach Skala
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

		$moodle28=false;

		try
		{
			$method = new ReflectionMethod('grade_export_ods','__construct');
		
			if(count($method->getParameters())==3)
				$moodle28=true;
		}
		catch(ReflectionException $e)
		{
		}

		if($moodle28)
		{
			//ab Moodle 2.8 hat grade_export_ods nur noch 3 Parameter		
			$formdata = new stdClass();
			$formdata->display=$notenart;
			$formdata->itemids=0;
			$formdata->decimals=2;
			$formdata->export_feedback=false;
			if (!$export = new grade_export_ods($course, 0, $formdata))
			{
				throw new moodle_exception('Fehler', '', '', null, "Moodle-Kurs ".$id." ".$shortname." - keine Export Information gefunden");
				return false;
			}
		}
		else
		{
			if (!$export = new grade_export_ods($course, 0, 0, false, false, $notenart, 2))
			{
				throw new moodle_exception('Fehler', '', '', null, "Moodle-Kurs ".$id." ".$shortname." - keine Export Information gefunden");
				return false;
			}
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
				
				// Wenn Prozent dann Prozentzeichen entfernen
				if(strpos($user_item['note'],'%')!==false)
			     	$user_item['note']=trim(str_replace('%','',$user_item['note']));

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
}
