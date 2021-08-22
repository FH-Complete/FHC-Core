<?php

class LDAPLib
{
	const LDAP_CONF_FILE = 'ldap'; // LDAP config file name

	// LDAP configuration array elements
	const SERVER = 'server';
	const PORT = 'port';
	const STARTTLS = 'starttls';
	const BASEDN = 'basedn';
	const USERNAME = 'username';
	const PASSWORD = 'password';
	const USF = 'usf';

	const DN = 'dn'; // LDAP dn name

	const LDAP_PROTOCOL_VERSION = 3; // Specifies the LDAP protocol to be used (V2 or V3) V2 is deprecated
	const LDAP_REFERRALS = 0; // Specifies whether to automatically follow referrals returned by the LDAP server (0 = disabled)
	const LDAP_INVALID_CREDENTIALS = 49; // LDAP invalid credentials code

	// Private properties
	private $_connection;
	private $_workingConfigArray;
	private $_ldapConfigArray;

	/**
	 * Sets the properties and loads the LDAP configuration
	 */
	public function __construct()
	{
		$this->_connection = null;
		$this->_workingConfigArray = null;
		$this->_ldapConfigArray = null;

		$this->_loadConfig(); // NOTE: always the last to be called!
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Tries to connect to LDAP using configurations in property _ldapConfigArray
	 * The first that works is used and stored in property _workingConfigArray
	 * The LDAP connection link is stored in _connection
	 */
	public function anonymousConnect()
	{
		$connect = error('Did not found a working LDAP configuration');

		// Loops through LDAP configurations
		foreach ($this->_ldapConfigArray as $ldapConfigs)
		{
			// Tries to establish a connection
			$connect = $this->_connect($ldapConfigs);
			if (isSuccess($connect))
			{
				break; // found a working LDAP configuration and successfully connected!
			}
			else
			{
				$this->close(); // close the eventually established connection
			}
		}

		return $connect;
	}

	/**
	 * Tries to connect using the given username and password and the last working configuration with anonymous connection
	 */
	public function connectUsernamePassword($username, $password)
	{
		if (isEmptyString($username) || isEmptyString($password)) return error('Wrong username and password');

		return $this->_connect($this->_workingConfigArray, $username, $password);
	}

	/**
	 * Close the current connection to LDAP if present
	 */
	public function close()
	{
		if ($this->_connection != null) @ldap_unbind($this->_connection);
	}

	/**
	 * Get the user DN from LDAP using the given username
	 */
	public function getUserDN($username)
	{
		$userDN = error('No user DN were found', LDAP_NO_USER_DN);

		// Tries to search for a user DN using the given username
		$searchResultIdentifier = @ldap_search(
			$this->_connection,
			$this->_workingConfigArray[self::BASEDN],
			$this->_workingConfigArray[self::USF].'='.$username
		);
		if (!$searchResultIdentifier) // Error
		{
			$userDN = error(ldap_error($this->_connection));
		}

		// Counts the number of found entries
		$countEntries = @ldap_count_entries($this->_connection, $searchResultIdentifier);
		if ($countEntries === false) // Error
		{
			$userDN = error(ldap_error($this->_connection));
		}
		elseif ($countEntries == 0)
		{
			$userDN = error('No user DN were found', LDAP_NO_USER_DN);
		}
		elseif ($countEntries > 1)
		{
			$userDN = error('Too many users DN were found', LDAP_TOO_MANY_USER_DN);
		}
		else // One entry was found
		{
			$entries = @ldap_get_entries($this->_connection, $searchResultIdentifier);
			if (!$entries) // Error
			{
				$userDN = error(ldap_error($this->_connection));
			}
			else
			{
				$userDN = success($entries[0][self::DN]);
			}
		}

		return $userDN;
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Loads the LDAP configuration file and store the LDAP configuration array into _ldapConfigArray property
	 */
	private function _loadConfig()
	{
		// Tries to require the LDAP configuration file...
		// ...first in the ENVIRONMENT subdirectory...
		if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/'.self::LDAP_CONF_FILE.'.php'))
		{
			require_once(APPPATH.'config/'.ENVIRONMENT.'/'.self::LDAP_CONF_FILE.'.php');
		}
		else // ...then in the default config directory
		{
			require_once(APPPATH.'config/'.self::LDAP_CONF_FILE.'.php');
		}

		$this->_ldapConfigArray = $ldap[$ldap_active_group]; // store the active LDAP configuration array
	}

	/**
	 * Establish a connection to LDAP with the given LDAP configuration array and eventually with
	 * with a given username and password
	 */
	private function _connect($ldapConfigs, $username = null, $password = null)
	{
		// Checks if the LDAP configuraion is empty
		if (isEmptyArray($ldapConfigs))
		{
			return error('Wrong parameters given');
		}

		// LDAP connection
		$ldapConnection = @ldap_connect($ldapConfigs[self::SERVER].':'.$ldapConfigs[self::PORT]);
		if ($ldapConnection) // if success
		{
			// Sets the LDAP protocol version
			if (!@ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, self::LDAP_PROTOCOL_VERSION))
			{
				return error(ldap_error($ldapConnection));
			}

			// Enable/disable the LDAP referrals
			if (!@ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, self::LDAP_REFERRALS))
			{
				return error(ldap_error($ldapConnection));
			}

			// Starts TLS if required
			if ($ldapConfigs[self::STARTTLS] === true)
			{
				if (!@ldap_start_tls($ldapConnection))
				{
					return error(ldap_error($ldapConnection));
				}
			}

			// If username and password are not provided...
			if ($username == null || $password == null)
			{
				// ...uses those provided by the configuration
				$username = $ldapConfigs[self::USERNAME];
				$password = $ldapConfigs[self::PASSWORD];
			}

			// Binds to LDAP directory
			if (!@ldap_bind($ldapConnection, $username, $password))
			{
				// Wrong username and/or password
				if (ldap_errno($ldapConnection) == self::LDAP_INVALID_CREDENTIALS)
				{
					return error('Invalid credentials', AUTH_INVALID_CREDENTIALS);
				}
				else // Error
				{
					return error(ldap_error($ldapConnection));
				}
			}

			$this->_connection = $ldapConnection; // save the connection into _connection property
			$this->_workingConfigArray = $ldapConfigs; // save the working LDAP configuration into _workingConfigArray property

			return success('Connected'); // connected!!!
		}
		else // Connection error
		{
			return error(
				'An error occurred while connecting to the LDAP server: '.$ldapConfigs[self::SERVER].':'.$ldapConfigs[self::PORT]
			);
		}
	}
}
