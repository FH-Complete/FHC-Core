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
	const AUTHENTICATION_LOGOUT_PAGE = 'authentication_logout_page';

	// Login object properties
	const AO_PERSON_ID = 'person_id';
	const AO_NAME = 'name';
	const AO_SURNAME = 'surname';
	const AO_USERNAME = 'username';

	// Sessions names
	const SESSION_NAME = 'AUTH';
	const SESSION_AUTH_OBJ = 'AUTH_OBJ';
	const SESSION_AUTH_OBJ_ORIGIN = 'AUTH_OBJ_ORIGIN';
	const SESSION_LANDING_PAGE = 'LANDING_PAGE';

	private $_ci; // CI instance

	/**
	 * Construct
	 *
	 * @param bool $authenticate If the authentication must be performed.
	 */
	public function __construct($authenticate = true)
	{
		// Gets CI instance
		$this->_ci =& get_instance();

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
	 * The logged user is able, if it is allowed, to get the identity of another user by its given uid
	 */
	public function loginASByUID($uid)
	{
		$loginAS = error('Not authenticated', AUTH_NOT_AUTHENTICATED); // not authenticated by default

		// A user must be already logged
		if ($this->_isLogged())
		{
			// - The uid must be NOT an empty string
			// - The current user should NOT be already logged as the given uid
			if (!isEmptyString($uid) && $this->getAuthObj()->username != $uid)
			{
				$this->_ci->load->library('PermissionLib'); // Loads permissions library

				// Checks if the logged user is allowed to obtain the new identity
				if ($this->_ci->permissionlib->isEntitledLoginASByUID($uid))
				{
					// Create the authentication object with new identity data
					$loginAS = $this->_createAuthObjByPerson(array('uid' => $uid));
					if (isSuccess($loginAS))
					{
						// Store the new authentication object in authentication session
						setSessionElement(self::SESSION_NAME, self::SESSION_AUTH_OBJ, getData($loginAS));
					}
				}
				else
				{
					$loginAS = error('It is not allowed to switch to this user', AUTH_NOT_AUTHENTICATED); // not authenticated by default
				}
			}
			else
			{
				$loginAS = error('The given uid is not valid', AUTH_INVALID_CREDENTIALS);
			}
		}

		return $loginAS;
	}

	/**
	 * The logged user is able, if it is allowed, to get the identity of another user by its given person id
	 */
	public function loginASByPersonId($person_id)
	{
		$loginAS = error('Not authenticated', AUTH_NOT_AUTHENTICATED); // not authenticated by default

		// A user must be already logged
		if ($this->_isLogged())
		{
			// - The person id must be a number
			// - The current user should NOT be already logged as the given person id
			if (is_numeric($person_id) && $this->getAuthObj()->person_id != $person_id)
			{
				$this->_ci->load->library('PermissionLib'); // Loads permissions library

				// Checks if the logged user is allowed to obtain the new identity by its person id
				if ($this->_ci->permissionlib->isEntitledLoginASByPersonId($person_id))
				{
					// Create the authentication object with new identity data
					$loginAS = $this->_createAuthObjByPerson(array('person_id' => $person_id));
					if (isSuccess($loginAS)) // if successfully created
					{
						$authObj = getData($loginAS); // get the authenticate object
						if ($authObj->{self::AO_USERNAME} != null) // if the username is present
						{
							// Checks if the logged user is allowed to obtain the new identity by its uid
							if ($this->_ci->permissionlib->isEntitledLoginASByUID($authObj->{self::AO_USERNAME}))
							{
								// Store the new authentication object in authentication session
								setSessionElement(self::SESSION_NAME, self::SESSION_AUTH_OBJ, $authObj);
							}
							else // if does NOT have permissions
							{
								$loginAS = error('Not authenticated', AUTH_NOT_AUTHENTICATED);
							}
						}
						else // otherwise it's NOT possible to check other permissions
						{
							// Store the new authentication object in authentication session
							setSessionElement(self::SESSION_NAME, self::SESSION_AUTH_OBJ, $authObj);
						}
					}
				}
			}
			else
			{
				$loginAS = error('The given person id is not valid', AUTH_INVALID_CREDENTIALS);
			}
		}

		return $loginAS;
	}

	/**
	 * Logs out the current logged user
	 * If the user is using the LoginAs functionality then it is logged out from the acquired user
	 * and its original one is restored
	 */
	public function logout()
	{
		$authObj = getSessionElement(self::SESSION_NAME, self::SESSION_AUTH_OBJ);
		$authObjOrigin = getSessionElement(self::SESSION_NAME, self::SESSION_AUTH_OBJ_ORIGIN);

		// NOT logged in
		if ($authObj == null || $authObjOrigin == null) return;

		// LoginAs functionality NOT in use
		if ($authObj->{self::AO_PERSON_ID} == $authObjOrigin->{self::AO_PERSON_ID})
		{
			// Clean the entire session -> fully logged out
			cleanSession(self::SESSION_NAME);
		}
		else // loginAs functionality in use
		{
			// Copy the origin authentication object as the authentication object in session
			// The LoginAs account is logged out
			// The user is again connected with its real account
			setSessionElement(self::SESSION_NAME, self::SESSION_AUTH_OBJ, $authObjOrigin);
		}
	}

	/**
	 * Login a user with the given username and password using LDAP
	 */
	public function loginLDAP($username, $password)
	{
		// If already logged do NOT check another time, returns the authentication object
		if ($this->_isLogged()) return success($this->getAuthObj(), AUTH_SUCCESS);

		// Otherwise checks the given credentials
		$loginUP = $this->_checkLDAPAuthentication($username, $password);
		if (isSuccess($loginUP))
		{
			$loginUP = $this->_createAuthObjByPerson(array('uid' => $username));

			// If were possible to retrieve user's data without failing,
			// then stores the authentication object into authentication session
			if (isSuccess($loginUP)) $this->_storeSessionAuthObj(getData($loginUP));
		}

		return $loginUP;
	}

	/**
	 * Redirect to the previously stored landing page
	 */
	public function redirectToLandingPage($altLandingPage = null)
	{
		$this->_redirectTemporarily($this->getLandingPage($altLandingPage)); // redirect to landing page
	}

	/**
	 * Returns the landing page
	 */
	public function getLandingPage($altLandingPage = null)
	{
		// Tries to get the previously stored landing page
		$landingPage = getSessionElement(self::SESSION_NAME, self::SESSION_LANDING_PAGE);
		if ($landingPage == null) // if not present
		{
			// If it was given a valid alternative landing page
			if (!isEmptyString($altLandingPage))
			{
				$landingPage = $altLandingPage;
			}
			else
			{
				$landingPage = site_url(); // use the default home page
			}
		}

		// Clean the previously stored landing page
		cleanSessionElement(self::SESSION_NAME, self::SESSION_LANDING_PAGE);

		return $landingPage;
	}

	/**
	 * Checks the authentication of an addon. Returns TRUE if valid, otherwise FALSE
	 */
	public function basicAuthentication($username, $password)
	{
		return isSuccess($this->loginLDAP($username, $password));
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
			$authObj->{self::AO_USERNAME} = $username;
			$authObj->{self::AO_SURNAME} = $surname;
			$authObj->{self::AO_NAME} = $name;
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
		$this->_ci->output->set_status_header(REST_Controller::HTTP_UNAUTHORIZED); // set the HTTP header as unauthorized
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
		$this->_ci->output->set_status_header(REST_Controller::HTTP_UNAUTHORIZED); // set the HTTP header as unauthorized

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

		$this->_ci->loglib->logError($errorMessage); // CI log error

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
			$bt = $this->_createAuthObjByPerson(array('person_id' => $_SESSION['bewerbung/personId']));
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
		if (!isset($_SERVER['PHP_AUTH_USER'])
			|| isError($hta = $this->_checkLDAPAuthentication($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])))
		{
			// If NOT send the header to perform an HTTP basic authentication
			header('WWW-Authenticate: Basic realm="'.AUTH_NAME.'"');
		}
		else // otherwise
		{
			// NOTE: Username needs to be trimmed and lowered because htaccess is allowing login
			$hta = $this->_createAuthObjByPerson(array('uid' => mb_strtolower(trim($_SERVER['PHP_AUTH_USER']))));
		}

		// Invalid credentials
		// NOTE: this is a corner case because of the HTTP basic authentication
		if (getCode($hta) == AUTH_NOT_AUTHENTICATED || getCode($hta) == AUTH_INVALID_CREDENTIALS
			|| getCode($hta) == AuthLDAPLib::LDAP_NO_USER_DN || getCode($hta) == AuthLDAPLib::LDAP_TOO_MANY_USER_DN)
		{
			$this->_showInvalidAuthentication(); // this also stop the execution
		}
		elseif (isError($hta)) // display error and stop execution
		{
			$this->_showError(getError($hta));
		}

		return $hta; // if success then is returned!
	}

	/**
	 * Checks the provided username and password with LDAP
	 */
	private function _checkLDAPAuthentication($username, $password)
	{
		$ldap = error('Not authenticated', AUTH_NOT_AUTHENTICATED); // by default is NOT authenticated

		$this->_ci->load->library('AuthLDAPLib'); // Loads the LDAP library

		// If it is possible to authenticate on LDAP with the given username and password
		if ($this->_ci->authldaplib->checkUsernamePassword($username, $password) === true)
		{
			$ldap = success('Authenticated', AUTH_SUCCESS); // authenticated!
		}

		return $ldap;
	}

	/**
	 * Tries to find if the user is already logged via a foreign authentication method
	 * using a list of foreign authentication methods provided with the configurations
	 * If the user is logged via a foreign authentication method then an authentication object is returned
	 * NOTE: _checkHBALDAPAuthentication is the last to be called and it is a corner case due the HTTP basic
	 *		authentication mechanism, and it does not return anything
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
	 * Everything was fine, the user at this point is authenticated, it is possible to store the authentication object
	 * in the user session
	 */
	private function _storeSessionAuthObj($authObj)
	{
		setSessionElement(self::SESSION_NAME, self::SESSION_AUTH_OBJ, $authObj); // authentication object
		setSessionElement(self::SESSION_NAME, self::SESSION_AUTH_OBJ_ORIGIN, $authObj); // authentication original object
	}

	/**
	 * Stores the user accessed point into the authentication session
	 */
	private function _storeSessionLandingPage($loginPage, $logoutPage)
	{
		// Build the curret URL (user access point)
		$currentURL = current_url().(!isEmptyString($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '');

		// If the access point is not the login page or the logout page
		if ($currentURL != site_url($loginPage) && $currentURL != site_url($logoutPage))
		{
			setSessionElement(self::SESSION_NAME, self::SESSION_LANDING_PAGE, $currentURL);
		}
	}

	/**
	 * Redirect the user's browser to the configured login page
	 */
	private function _redirectToLogin()
	{
		$al = $this->_ci->config->item(self::AUTHENTICATION_LOGIN); // selected login method
		$alip = $this->_ci->config->item(self::AUTHENTICATION_LOGIN_PAGES); // login pages configuration array
		$alop = $this->_ci->config->item(self::AUTHENTICATION_LOGOUT_PAGE); // logout page configuration

		// If the configuration is valid
		if (!isEmptyArray($alip) && isset($alip[$al]))
		{
			$this->_storeSessionLandingPage($alip[$al], $alop); // stores in session the user access point

			$this->_redirectTemporarily(site_url($alip[$al])); // redirect to login page
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
			$this->_ci->config->load('auth'); // Loads auth configuration

			// Checks if already logged with a foreign authentication method
			$auth = $this->_checkForeignAuthentication();
			if (hasData($auth)) // Authenticated with a foreign authentication method
			{
				$this->_storeSessionAuthObj(getData($auth)); // store the session authentication object
			}
			elseif (getCode($auth) == AUTH_NOT_AUTHENTICATED) // if no foreign authentication was found...
			{
				$this->_redirectToLogin(); // ...then redirect to login page
			}
			elseif (isError($auth)) // blocking error
			{
				$this->_showError(getError($auth)); // display a generic error message and logs the occurred error
			}
		}
		// else the user is already logged, then loads authentication helper and continue with the execution
		// NOTE: it is needed only here because:
		//		- it is called when a user is already logged in
		//		- it is called after login the user
		//		- it is NOT called in case of fatal error or wrong authentication
		$this->_ci->load->helper('hlp_authentication');
	}

	/**
	 * It uses the given query where clause to select data for a user and then stores these data into a authentication object
	 */
	private function _createAuthObjByPerson($queryParamsArray)
	{
		$authObj = error('No user data found'); // pessimistic as usual

		$this->_ci->load->model('person/person_model', 'PersonModel'); // Loads model PersonModel

		$this->_ci->PersonModel->resetQuery(); // Reset an eventually already built query

		// Needed information
		$this->_ci->PersonModel->addSelect('person_id, vorname, nachname, uid');
		// Retrieves the uid if it is possible for active users
		$this->_ci->PersonModel->addJoin(
			'(SELECT uid, person_id FROM public.tbl_benutzer WHERE aktiv = TRUE) tb', 'person_id',
			'LEFT'
		);

		// Execute query with where clause
		$personResult = $this->_ci->PersonModel->loadWhere($queryParamsArray);
		if (hasData($personResult))
		{
			$person = getData($personResult)[0];

			// Stores user data into the authentication object and then into a success object
			$authObj = success(
				$this->_createAuthObj($person->person_id, $person->vorname, $person->nachname, $person->uid),
				AUTH_SUCCESS
			);
		}
		elseif (isError($personResult)) // blocking error
		{
			$authObj = $personResult; // to be returned
		}

		return $authObj;
	}

	/**
	 * HTTP temporary redirection
	 */
	private function _redirectTemporarily($url)
	{
		// Temporary redirection
		header('HTTP/1.1 302 Moved temporary');
		header('Location: '.$url); // redirect to the given URL
		exit(); // stops execution!
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
			$finalUserBasicDataByPersonID = new stdClass();
			// Store the person_id and eventually all the uid and prestudent_id related to this final user
			$finalUserBasicDataByPersonID->person_id = $benutzer->retval[0]->person_id;

			$finalUserBasicDataByUID = success($finalUserBasicDataByPersonID);
		}

		return $finalUserBasicDataByUID;
	}
}
