<?php
class Student_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_student';
		$this->pk = 'student_uid';
	}

    /**
     * Checks if the user is a Student.
     * @param string $uid
     * @return array
     */
    public function isStudent($uid)
    {
        $this->addSelect('1');

       
        $result = $this->loadWhere(array('student_uid' => $uid));
        

        if(hasData($result))
        {
            return success(true);
        }
        else
        {
            return success(false);
        }
    }
    
   //! THIS FILE WAS CREATED USING THE Mitarbeiter_model.php FILE 
    
   
}
