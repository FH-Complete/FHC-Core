<?php
/**
 *  author: maximilian schremser <max@technikum-wien.at>
 *  date:   
 *  title: Authorization.class.php
 *  manual: $auth = new Authorization($uid, $course_id, $semester, $lesson);
 *
 * 	Tries to login a user as a student, if fails, it tries
 *	to login as a teacher.
 *	returns 0 for student
 *		1 for teacher
 *		-1 on error
 *	holt den firstname, lastname, uid und id des Benutzers
 */
class Authorization 
{
	var $firstname;
	var $lastname;
	var $id;
	var $uid;
	var $stg_id;
	var $semester;
	var $verband;
	var $gruppe;
	var $isLector;
	var $module;
	var $lehrfach;
	var $date;
	var $session;
	var $birthday;

	function Authorization($uid ="", $course_id = 0, $semester = 0, $lesson = "",$conn="")
	{
		$this->isLector = -1;
		$this->module    = 0;
		
		if ($uid == "oesi" || $uid == "kates")
		{
			$this->uid = $uid;
			$this->stg_id      = $course_id;
			$this->lehrfach    = $lesson;
			$this->semester    = $semester;
			$this->isLector    = 1;
			return $this->isLector;
		}
				
		// try to looon as a student
		$sql = "SELECT * ".
		"FROM tbl_student JOIN tbl_person USING (uid) WHERE 
		uid = '$uid' AND studiengang_kz = $course_id 
		AND semester >= $semester";
		
	    $rs  = new pgRS($conn,$sql);
		
       	if ($rs->num > 0) 
       	{
      		$this->firstname = $rs->arr[0]["vornamen"];
           	$this->lastname  = $rs->arr[0]["nachname"];
     	    //$this->id        = $rs->arr[0]["id"];
			$this->uid       = $uid;
			$this->stg_id    = $rs->arr[0]["studiengang_kz"];
			$this->semester  = $rs->arr[0]["semester"];
			$this->verband   = $rs->arr[0]["verband"];
			$this->gruppe    = $rs->arr[0]["gruppe"];
			$this->birthday  = $rs->arr[0]["gebdatum"];
			$this->isLector = 0;
		}
        else
        {
			/* oesi 17-01-2005
			$sql = "SELECT * FROM lehre.tbl_lehrfachzuteilung JOIN tbl_person ON
				(lektor_uid = uid) WHERE lektor_uid = '$uid' AND
				lehrfachzuteilung_kurzbz = '$lesson' AND 
				semester = $semester AND studiengang_kz = $course_id LIMIT 1";
			*/
			$sql = "SELECT * FROM tbl_lehrveranstaltung, tbl_lehrfach, tbl_person WHERE 
					tbl_lehrveranstaltung.lehrfach_nr=tbl_lehrfach.lehrfach_nr AND 
					tbl_person.uid=lektor AND lektor='$uid' AND 
					tbl_lehrfach.studiengang_kz=$course_id AND 
					tbl_lehrfach.semester=$semester AND lehrevz='$lesson' LIMIT 1";
			
	        $rs = new pgRS($conn,$sql);
			if ($rs->num > 0) 
			{
				$this->firstname = $rs->arr[0]["vornamen"];
				$this->lastname  = $rs->arr[0]["nachname"];
				$this->birthday  = $rs->arr[0]["gebdatum"];
				$this->uid       = $uid;
				$this->stg_id    = $course_id;
				$this->lehrfach  = $lesson;
				$this->semester  = $semester;
				$this->isLector = 1;
			}
			else 
			{
				//$sql = "SELECT DISTINCT tbl_person.uid FROM public.tbl_person, lehre.tbl_lehrfachzuteilung WHERE tbl_person.uid='$uid' AND tbl_lehrfachzuteilung.lektor_uid=tbl_person.uid AND studiengang_kz='$course_id' UNION SELECT DISTINCT tbl_person.uid FROM public.tbl_person, public.tbl_personfunktion WHERE tbl_person.uid='$uid' AND tbl_personfunktion.uid=tbl_person.uid AND studiengang_kz='$course_id' LIMIT 1";
				$sql = "SELECT vornamen, nachname, gebdatum FROM public.tbl_person, tbl_lehrveranstaltung 
						WHERE tbl_person.uid='$uid' AND tbl_lehrveranstaltung.lektor=tbl_person.uid AND 
						studiengang_kz='$course_id' 
						UNION 
						SELECT vornamen, nachname, gebdatum FROM public.tbl_person, public.tbl_personfunktion 
						WHERE tbl_person.uid='$uid' AND tbl_personfunktion.uid=tbl_person.uid AND 
						studiengang_kz='$course_id' LIMIT 1";

				$rs = new pgRS($conn,$sql);
	            
				if ($rs->num > 0) 
				{
					$this->firstname = $rs->arr[0]["vornamen"];
					$this->lastname  = $rs->arr[0]["nachname"];
					$this->birthday  = $rs->arr[0]["gebdatum"];
					$this->uid       = $uid;
					$this->stg_id    = $course_id;
					$this->lehrfach  = $lesson;
					$this->semester  = $semester;
					$this->isLector = 1;
				}
			}
		}
		return $this->isLector;
	}
}
?>
