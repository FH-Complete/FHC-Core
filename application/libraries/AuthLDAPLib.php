<?php

class AuthLDAPLib
{
	const LDAP_CONF_FILE = 'ldap'; // LDAP config file name

	// LDAP codes
	const LDAP_NO_USER_DN = 10; // No users found
	const LDAP_TOO_MANY_USER_DN = 11; // More then one user found

	// LDAP configuration array elements
	const SERVER = 'server';
	const PORT = 'port';
	const STARTTLS = 'starttls';
	const BASEDN = 'basedn';
	const USERNAME = 'username';
	const PASSWORD = 'password';
	const USF = 'usf';
	const TIMEOUT = 'timeout';

	const DN = 'dn'; // LDAP dn name

	const LDAP_PROTOCOL_VERSION = 3; // Specifies the LDAP protocol to be used (V2 or V3) V2 is deprecated
	const LDAP_REFERRALS = 0; // Specifies whether to automatically follow referrals returned by the LDAP server (0 = disabled)
	const LDAP_INVALID_CREDENTIALS = 49; // LDAP invalid credentials code
	const LDAP_DEFAULT_TIMEOUT = 1; // Default LDAP timeout in seconds

	/**
	 * Sets the properties and loads the LDAP configuration
	 */
	public function __construct()
	{
		// Gets CI instance
		$this->_ci =& get_instance();

		// Loads the LogLib
		$this->_ci->load->library('LogLib');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Checks if the given credentials are valid on one of the configured LDAP servers
	 */
	public function checkUsernamePassword($username, $password)
	{
		$authenticated = false;

		if (isEmptyString($username) || isEmptyString($password)) return error('Wrong username and password');

		$ldapConfigArrays = $this->_loadConfig(); // NOTE: always the last to be called!

		// For each configured LDAP server
		foreach ($ldapConfigArrays as $ldapConfigs)
		{
			// Check if the LDAP server is up and running
			if (!$this->_servicePing($ldapConfigs))
			{
				// If not available log debug and skip to the next configured server
				$this->_ci->loglib->logError('This LDAP server is not available: '.$ldapConfigs[self::SERVER]);
				continue;
			}

			// Connection without username and passoword _or_ with the configured username and password
			$noCredentialsConnectResult = $this->_connect($ldapConfigs);
			if (isError($noCredentialsConnectResult)) // If an error occurred
			{
				// If the error is due to invalid credentials or
				// the LDAP server does not support anonymous authentication
				if (getCode($noCredentialsConnectResult) == AUTH_INVALID_CREDENTIALS)
				{
					$this->_ci->loglib->logDebug(getError($noCredentialsConnectResult).' on server '.$ldapConfigs[self::SERVER]);
				}
				else // otherwise if it was due to a fatal error
				{
					$this->_ci->loglib->logError(getError($noCredentialsConnectResult).' on server '.$ldapConfigs[self::SERVER]);
				}

				continue; // anyway skip to the next configured server
			}

			// If it is a success
			$noCredentialsConnection = getData($noCredentialsConnectResult);

			// Check if the user exists on this LDAP server
			$userDNResult = $this->_getUserDN(
				$noCredentialsConnection,
				$ldapConfigs[self::BASEDN],
				$ldapConfigs[self::USF],
				$username
			);
			// If an error occurred or the user was not found or many users were found
			if (isError($userDNResult))
			{
				// Log debug and skip to the next configured server
				// If the error is due to invalid credentials or
				// the LDAP server does not support anonymous authentication
				if (getCode($userDNResult) == self::LDAP_NO_USER_DN)
				{
					$this->_ci->loglib->logDebug(getError($userDNResult).' on server '.$ldapConfigs[self::SERVER]);
				}
				elseif (getCode($userDNResult) == self::LDAP_TOO_MANY_USER_DN)
				{
					$this->_ci->loglib->logDebug(getError($userDNResult).' on server '.$ldapConfigs[self::SERVER]);
				}
				else // otherwise if it was due to a fatal error
				{
					$this->_ci->loglib->logError(getError($userDNResult).' on server '.$ldapConfigs[self::SERVER]);
				}

				$this->_close($noCredentialsConnection); // Close the current LDAP connection
				continue; // anyway skip to the next configured server
			}

			$this->_close($noCredentialsConnection); // Close the current LDAP connection

			// Connect to LDAP with the userDN and password
			$credentialsConnectResult = $this->_connect($ldapConfigs, getData($userDNResult), $password);
			if (isError($credentialsConnectResult)) // If an error occurred
			{
				// Log debug and skip to the next configured server
				$this->_ci->loglib->logError(getError($credentialsConnectResult).' on server '.$ldapConfigs[self::SERVER]);
				continue;
			}
			else // otherwise the user is authenticated
			{
				$this->_close(getData($credentialsConnectResult));
				$authenticated = true;
				break;
			}
		}

		return $authenticated;
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Loads the LDAP configuration file and returns the LDAP configuration array
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

		return $ldap[$ldap_active_group];
	}

	/**
	 * Establish a connection to LDAP with the given LDAP configuration array and eventually with
	 * with a given username and password
	 */
	private function _connect($ldapConfigs, $username = null, $password = null)
	{
		// Checks if the LDAP configuraion is empty
		if (isEmptyArray($ldapConfigs)) return error('Wrong parameters given');

		// LDAP connection
		$ldapConnection = @ldap_connect($ldapConfigs[self::SERVER].':'.$ldapConfigs[self::PORT]);
		if ($ldapConnection) // if success
		{
			// Sets the LDAP protocol version
			if (!@ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, self::LDAP_PROTOCOL_VERSION))
			{
				return error('Was not possible to set the protocol version using LDAP sever '.$ldapConfigs[self::SERVER]);
			}

			// Enable/disable the LDAP referrals
			if (!@ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, self::LDAP_REFERRALS))
			{
				return error('Was not possible to enable referrals using LDAP sever '.$ldapConfigs[self::SERVER]);
			}

			// Starts TLS if required
			if ($ldapConfigs[self::STARTTLS] === true && !@ldap_start_tls($ldapConnection))
			{
				return error('Was not possible to enable TLS using LDAP sever '.$ldapConfigs[self::SERVER]);
			}

			// If username or password are not provided...
			if (isEmptyString($username) || isEmptyString($password))
			{
				// ...use those provided by the configuration
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
					return error('Was not possible to bind to the LDAP sever '.$ldapConfigs[self::SERVER]);
				}
			}

			return success($ldapConnection); // connected!!!
		}
		else // Connection error
		{
			return error('An error occurred while connecting to the LDAP server '.$ldapConfigs[self::SERVER]);
		}
	}

	/**
	 * Check if the network service is up and running
	 */
	private function _servicePing($ldapConfigs)
	{
		// Set the default timeout
		$timeout = self::LDAP_DEFAULT_TIMEOUT;

		// If a timeout was configured for this server then use it
		if (isset($ldapConfigs[self::TIMEOUT])) $timeout = $ldapConfigs[self::TIMEOUT];

		// The LDAP server name or URL
		$host = $ldapConfigs[self::SERVER];

		// If it is a URL
		if (strpos($ldapConfigs[self::SERVER], 'ldap://') !== false
			|| strpos($ldapConfigs[self::SERVER], 'ldaps://') !== false)
		{
			// Get the host from the URL
			$host = parse_url($ldapConfigs[self::SERVER], PHP_URL_HOST);
		}

		// Check if the given host answers on the given port using the given timeout
		if ($op = @fsockopen($host, $ldapConfigs[self::PORT], $errno, $errstr, $timeout))
		{
			// If it works then close the socket connection
			fclose($op);
			return true;
		}

		return false; // otherwise this server is not up or LDAP service is not running on the given port
	}

	/**
	 * Close the current connection to LDAP if present
	 */
	private function _close($connection)
	{
		@ldap_unbind($connection);
	}

	/**
	 * Get the user DN from LDAP using the given username
	 */
	private function _getUserDN($connection, $baseDN, $usf, $username)
	{
		$userDN = error('AuthLDAPLib->_getUserDN() failed');

		// Tries to search for a user DN using the given username
		$searchResultIdentifier = @ldap_search(
			$connection,
			$baseDN,
			$usf.'='.$username
		);
		if (!$searchResultIdentifier) // Error
		{
			$userDN = error(ldap_error($connection));
		}

		// Counts the number of found entries
		$countEntries = @ldap_count_entries($connection, $searchResultIdentifier);
		if ($countEntries === false) // Error
		{
			$userDN = error(ldap_error($connection));
		}
		elseif ($countEntries == 0)
		{
			$userDN = error('No user DN were found with username: '.$username, self::LDAP_NO_USER_DN);
		}
		elseif ($countEntries > 1)
		{
			$userDN = error('Too many users DN were found with username: '.$username, self::LDAP_TOO_MANY_USER_DN);
		}
		else // One entry was found
		{
			$entries = @ldap_get_entries($connection, $searchResultIdentifier);
			if (!$entries) // Error
			{
				$userDN = error(ldap_error($connection));
			}
			else
			{
				$userDN = success($entries[0][self::DN]);
			}
		}

		return $userDN;
	}
}

