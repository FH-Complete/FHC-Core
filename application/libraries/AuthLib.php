<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class AuthLib
{
	// Config entry name
	const LOGOUT_PAGE = 'system/Logout';

	// Config entry name
	const AUTHENTICATION_FOREIGN_METHODS = 'authentication_foreign_methods';
	const AUTHENTICATION_LOGIN = 'authentication_login';
	const AUTHENTICATION_LOGIN_PAGES = 'authentication_login_pages';

	// Login object properties
	const AO_PERSON_ID = 'person_id';
	const AO_NAME = 'name';
	const AO_SURNAME = 'surname';
	const AO_USERNAME = 'username';

	// Sessions names
	const SESSION_NAME = 'AUTH';
	const SESSION_AUTH_OBJ = 'AUTH_OBJ';
	const SESSION_AUTH_OBJ_ORIGIN = 'AUTH_OBJ_ORIGIN';

	private $_ci; // CI instance

	/**
	 * Construct
	 *
	 *
	 * @param bool $authenticate If the authentication must be performed.
	 */
	public function __construct($authenticate = true)
	{
		// Gets CI instance
		$this->_ci =& get_instance();

		// Loads auth configuration
		$this->_ci->config->load('auth');

		// Load model PersonModel
		$this->_ci->load->model('person/person_model', 'PersonModel');

		if ($authenticate === true) $this->_authenticate(); // if required -> authenticate the current user
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Returns the authentication object stored in the session
	 * if the user is not logged then returns null
	 */
	public function getAuthObj()
	{
		return getSessionElement(self::SESSION_NAME, self::SESSION_AUTH_OBJ);
	}

	/**
	 * Checks the authentication of an addon. Returns TRUE if valid, otherwise FALSE
	 */
	public function basicAuthentication($username, $password)
	{
		return isSuccess($this->_checkLDAPAuthentication($username, $password));
	}

	/**
	 * Checks if the given username and password of a final user are valid
	 */
	public function checkUserAuthByUsernamePassword($username, $password, $keys = false)
	{
		$result = error(false);

		if (isset($username) && isset($password))
		{
			if (isSuccess($this->_checkLDAPAuthentication($username, $password)))
			{
				if ($keys === true)
				{
					$result = $this->_getFinalUserBasicDataByUID($username);
				}
				else
				{
					$result = success(true);
				}
			}
		}

		return $result;
	}

	/**
	 * Checks if the given code of a final user is valid
	 */
	public function checkUserAuthByCode($code)
	{
		$result = error(false);

		$person = $this->_ci->PersonModel->loadWhere(array('zugangscode' => $code));
		if (hasData($person))
		{
			$result = $this->_getFinalUserBasicDataByPersonID($person->retval[0]->person_id);
		}

		return $result;
	}

	/**
	 * Checks if the given code and email of a final user are valid
	 */
	public function checkUserAuthByCodeEmail($code, $email)
	{
		$result = error(false);

		$person = $this->_ci->PersonModel->getPersonKontaktByZugangscode($code, $email);
		if (hasData($person))
		{
			$result = $this->_getFinalUserBasicDataByPersonID($person->retval[0]->person_id);
		}

		return $result;
	}

	/**
	 * Logs out the current logged user
	 * If the user is using the LoginAs functionality then it is logged out from the acquired user
	 * and its original one is restored
	 */
	public function logout()
	{
		$authObj = getSessionElement(AuthLib::SESSION_NAME, AuthLib::SESSION_AUTH_OBJ);
		$authObjOrigin =getSessionElement(AuthLib::SESSION_NAME, AuthLib::SESSION_AUTH_OBJ_ORIGIN);

		// NOT logged in
		if ($authObj == null || $authObjOrigin == null) return;

		// LoginAs functionality NOT in use
		if ($authObj->{AuthLib::AO_PERSON_ID} == $authObjOrigin->{AuthLib::AO_PERSON_ID})
		{
			// Clean the entire session -> fully logged out
			cleanSession(AuthLib::SESSION_NAME);
		}
		else // loginAs functionality in use
		{
			// Copy the origin authentication object as the authentication object in session
			// The LoginAs account is logged out
			// The user is again connected with its real account
			setSessionElement(
				AuthLib::SESSION_NAME,
				AuthLib::SESSION_AUTH_OBJ,
				getSessionElement(self::SESSION_NAME, self::SESSION_AUTH_OBJ_ORIGIN)
			);
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Create an authentication object with all the information about the logged user
	 * The minimum information required is the person_id
	 * Username should be null only if the user is authenticated with a foreign authentication method
	 */
	private function _createAuthObj($person_id, $name = null, $surname = null, $username = null)
	{
		$authObj = null;

		if (is_numeric($person_id))
		{
			$authObj = new stdClass();

			$authObj->{self::AO_PERSON_ID} = $person_id;
			$authObj->{self::AO_NAME} = $name;
			$authObj->{self::AO_SURNAME} = $surname;
			$authObj->{self::AO_USERNAME} = $username;
		}

		return $authObj;
	}

	/**
	 * If the object store in $_SESSIOn['AUTH']['AUTH_OBJ']
	 * is NOT null (logged) then returns true otherwise false (NOT logged)
	 */
	private function _isLogged()
	{
		return $this->getAuthObj() != null;
	}

	/**
	 * Display an invalid credentials error
	 * Used only by HTTP basic authentication!
	 */
	private function _showInvalidAuthentication()
	{
		header('HTTP/1.0 401 Unauthorized'); // set the HTTP header as unauthorized
		unset($_SERVER['PHP_AUTH_USER']);

		$this->_ci->load->library('EPrintfLib'); // loads the EPrintfLib to format the output

		// Prints the main error message
		$this->_ci->eprintflib->printError('The provided authentication credentials are invalid');
		// Prints the called controller name
		$this->_ci->eprintflib->printInfo('Controller name: '.$this->_ci->router->class);
		// Prints the called controller method name
		$this->_ci->eprintflib->printInfo('Method name: '.$this->_ci->router->method);

		exit; // immediately terminate the execution
	}

	/**
	 * Display a generic blocking error occurred while authenticating the user
	 */
	private function _showError($errorMessage)
	{
		$this->_ci->load->library('EPrintfLib'); // loads the EPrintfLib to format the output
		$this->_ci->load->library('LogLib'); // Loads the logs library

		// Prints the main error message
		$this->_ci->eprintflib->printError('An error occurred while checking the provided credentials');
		$this->_ci->eprintflib->printError('Please contact the system administrator');
		// Prints the called controller name
		$this->_ci->eprintflib->printInfo('Controller name: '.$this->_ci->router->class);
		// Prints the called controller method name
		$this->_ci->eprintflib->printInfo('Method name: '.$this->_ci->router->method);
		// Prints date and time
		$this->_ci->eprintflib->printInfo('Date and time: '.date('Y.m.d H:i:s'));

		$this->_ci->loglib->logError($errorMessage);

		exit; // immediately terminate the execution
	}

	/**
	 * Checks if the user is already authenticated with the Bewerbung Tool
	 * NOTE: this method does NOT set the username in the authentication object
	 */
	private function _checkBTAuthentication()
	{
		$bt = error('Not authenticated', AUTH_NOT_AUTHENTICATED); // by default is NOT authenticated

		// Checks if an authentication were performed via BT
		if (isset($_SESSION['bewerbung/personId']) && is_numeric($_SESSION['bewerbung/personId']) && isset($_SESSION['bewerbung/user']))
		{
			$this->_ci->PersonModel->resetQuery(); // Reset an eventually already built query

			// Then retrieves the person data from DB using the person_id
			$this->_ci->PersonModel->addSelect('vorname, nachname');

			// Retrieves user data using its own person_id
			$personResult = $this->_ci->PersonModel->load($_SESSION['bewerbung/personId']);
			if (hasData($personResult)) // found!
			{
				$person = getData($personResult)[0];

				// Stores used data into the authentication object and then into a success object
				$bt = success(
					$this->_createAuthObj($_SESSION['bewerbung/personId'], $person->vorname, $person->nachname),
					AUTH_SUCCESS
				);
			}
			elseif (isError($person)) // blocking error
			{
				$bt = $person; // return it!
			}
		}

		return $bt;
	}

	/**
	 * Checks if the user is already authenticated with HTTP basic authentication + LDAP
	 * NOTE: this method also display a login, not possible to be avoided due HTTP basic authentication limitations
	 */
	private function _checkHBALDAPAuthentication()
	{
		$hta = error('Not authenticated', AUTH_NOT_AUTHENTICATED); // by default is NOT authenticated

		// Checks if an HTTP basic authentication is active and checks credentials using LDAP
		if (!isset($_SERVER['PHP_AUTH_USER']) || isError($hta = $this->_checkLDAPAuthentication($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])))
		{
			// If NOT send the header to perform an HTTP basic authentication
			header('WWW-Authenticate: Basic realm="'.AUTH_NAME.'"');
		}
		else // otherwise
		{
			$this->_ci->PersonModel->resetQuery(); // Reset an eventually already built query

			// Retrieves user data from DB using the UID
			$personResult = $this->_ci->PersonModel->getByUid($_SERVER['PHP_AUTH_USER']);
			if (hasData($personResult))
			{
				$person = getData($personResult)[0];

				// Stores used data into the authentication object and then into a success object
				$hta = success(
					$this->_createAuthObj($person->person_id, $person->vorname, $person->nachname, $_SERVER['PHP_AUTH_USER']),
					AUTH_SUCCESS
				);
			}
			elseif (isError($personResult)) // blocking error
			{
				$hta = $personResult; // return it!
			}
		}

		return $hta;
	}

	/**
	 * Checks the provided username and password with LDAP
	 */
	private function _checkLDAPAuthentication($username, $password)
	{
		$ldap = error('Not authenticated', AUTH_NOT_AUTHENTICATED); // by default is NOT authenticated
		$ldapModel = new LDAP_Model(); // LDAP model handles the LDAP connection

		$ldapConnection = $ldapModel->connect(); // connect!
		if (isSuccess($ldapConnection)) // connected!!
		{
			// Get the user DN from LDAP
			$userDN = $ldapModel->getUserDN($username);
			if (isSuccess($userDN)) // got it!
			{
				$ldapModel->close(); // close the previous LDAP connection

				// Connects to LDAP using the last working configuration + the retrieved user DN + the provided password
				$ldapConnection = $ldapModel->connectUsernamePassword(getData($userDN), $password);
				if (isSuccess($ldapConnection)) // connected!
				{
					$ldapModel->close(); // close the previous connection
					$ldap = success('Authenticated'); // authenticated!
				}
				else // blocking error
				{
					$ldap = $ldapConnection;
				}
			}
			else // blocking error
			{
				$ldap = $userDN;
			}
		}
		else // blocking error
		{
			$ldap = $ldapConnection;
		}

		return $ldap;
	}

	/**
	 * Tries to find if the user is already logged via a foreign authentication method
	 * using a list of foreign authentication methods provided with the configurations
	 * If the user is logged via a foreign authentication method then an authentication object is returned
	 */
	private function _checkForeignAuthentication()
	{
		$auth = error('Not authenticated', AUTH_NOT_AUTHENTICATED); // by default is NOT authenticated
		$am = $this->_ci->config->item(self::AUTHENTICATION_FOREIGN_METHODS); // foreign authentication methods array

		// Loops through the foreign authentication methods
		foreach ($am as $method)
		{
			// Performs a different action for each foreign authentication method
			switch ($method)
			{
				case AUTH_BT: // Bewerbung tool
					$auth = $this->_checkBTAuthentication();
					break;
				case AUTH_HBALDAP: // HTTP basic authentication + LDAP
					$auth = $this->_checkHBALDAPAuthentication();
					break;
			}

			// Invalid credentials
			// NOTE: this is a corner case because of the HTTP basic authentication
			if (getCode($auth) == AUTH_INVALID_CREDENTIALS)
			{
				$this->_showInvalidAuthentication(); // this also stop the execution
			}

			// If not authenticated with this method...
			if (getCode($auth) == AUTH_NOT_AUTHENTICATED)
			{
				// ...then continue to the next one
			}
			// If generic error or a foreign authentication was found stop checking
			elseif (isError($auth) || (hasData($auth) && is_object(getData($auth))))
			{
				break;
			}
		}

		return $auth;
	}

	/**
	 * Stores the authentication object into the authentication session
	 */
	private function _storeAuthObj($authObj)
	{
		setSessionElement(self::SESSION_NAME, self::SESSION_AUTH_OBJ, $authObj); // authentication object
		setSessionElement(self::SESSION_NAME, self::SESSION_AUTH_OBJ_ORIGIN, $authObj); // authentication original object
	}

	/**
	 * Redirect the user's browser to the configured login page
	 */
	private function _redirectToLogin()
	{
		$al = $this->_ci->config->item(self::AUTHENTICATION_LOGIN); // selected login method
		$alp = $this->_ci->config->item(self::AUTHENTICATION_LOGIN_PAGES); // login pages configuration array

		// If the configuration is valid
		if (!isEmptyArray($alp) && isset($alp[$al]))
		{
			header('HTTP/1.1 301 Moved Permanently'); // permanent redirection
			header('Location: '.site_url().$alp[$al]); // redirect to the configured login page
			exit(); // stops execution!
		}
		else
		{
			$this->_showError('No valid login page was set'); // display a generic error message and logs the occurred error
		}
	}

	/**
	 * Starts the user authentication!
	 */
	private function _authenticate()
	{
		// If NOT logged
		if (!$this->_isLogged())
		{
			// Checks if already logged with a foreign authentication method
			$auth = $this->_checkForeignAuthentication();
			if (hasData($auth)) // Authenticated with a foreign authentication method
			{
				$this->_storeAuthObj(getData($auth)); // store the session authentication object
			}
			elseif (getCode($auth) == AUTH_NOT_AUTHENTICATED) // if no foreign authentication was found...
			{
				$this->_redirectToLogin(); // ...then redirect to login page
			}
			elseif (isError($auth)) // blocking error
			{
				$this->_showError(getData($auth)); // display a generic error message and logs the occurred error
			}
		}
		// else the user is already logged, then continue with the execution
	}

	/**
	 * Returns all the keys with which is possible to obtain personal data about a final user
	 * using the given username (uid)
	 */
	private function _getFinalUserBasicDataByUID($uid)
	{
		$finalUserBasicDataByUID = null;

		// Load model BenutzerModel
		$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel');

		$benutzer = $this->_ci->BenutzerModel->load($uid);
		if (hasData($benutzer))
		{
			$finalUserBasicDataByUID = $this->_getFinalUserBasicDataByPersonID($benutzer->retval[0]->person_id);
		}

		return $finalUserBasicDataByUID;
	}

	/**
	 * Returns all the keys with which is possible to obtain personal data about a final user
	 * using the given person_id
	 */
	private function _getFinalUserBasicDataByPersonID($person_id)
	{
		$finalUserBasicDataByPersonID = new stdClass(); // returned object

		// Store the person_id and eventually all the uid and prestudent_id related to this final user
		$finalUserBasicDataByPersonID->person_id = $person_id;

		return success($finalUserBasicDataByPersonID);
	}
}
