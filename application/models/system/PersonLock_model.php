<?php

/**
 * Enables content locking.
 * An entry in the locktable means certain content is marked locked and a user is its editor.
 */
class PersonLock_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_person_lock';
		$this->pk = 'lock_id';
	}

	/**
	 * Checks if a specific person is locked. By default, looks for entries with no app in locktable for the person.
	 * Alternatively, looks only for locks in a certain app.
	 * @param $person_id
	 * @param null $app
	 * @return array all locks for a person if locked, null otherwise
	 */
	public function checkIfLocked($person_id, $app = null)
	{
		$lockdata = array('person_id' => $person_id, 'app' => $app);

		$result = $this->loadWhere($lockdata);

		if ($result->error) return $result;

		if (count($result->retval) > 0)
			return success($result->retval);
		else
			return success(null);
	}

	/**
	 * Locks a person. Returns null if person was not locked (e.g. when already locked).
	 * @param $person_id
	 * @param $uid user who locks the person
	 * @param $app optional, application in which person is locked
	 * @return array inserted lock id if person was locked, null otherwise
	 */
	public function lockPerson($person_id, $uid, $app = null)
	{
		$locked = $this->checkIfLocked($person_id, $app);

		if ($locked->error) return $locked;

		//insert only if not already locked
		if ($locked->retval === null)
			return $this->insert(array('person_id' => $person_id, 'uid' => $uid, 'app' => $app));
		else
			return success(null);
	}

	/**
	 * Remove a lock for a person. By default, removes any entries in locktable for the person.
	 * Alternatively, removes only locks in a certain app.
	 * @param $person_id
	 * @param null $app
	 * @return array deleted lock ids if person was locked, null otherwise
	 */
	public function unlockPerson($person_id, $app = null)
	{
		$deleted = array();
		$locks = $this->checkIfLocked($person_id, $app);

		if ($locks->retval === null)
			return success(null);

		foreach ($locks->retval as $lock)
		{
			$result = $this->delete($lock->lock_id);
			if ($result->error) return $result;

			$deleted[] = $lock;
		}

		return success($deleted);
	}
}
