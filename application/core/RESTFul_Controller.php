<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * NOTE: this class overrides parent's methods and properties
 */
class RESTFul_Controller extends REST_Controller
{
	protected $is_valid_request = TRUE; // Change the accessibility of this property

	/**
     * Totally overriden
     */
    protected function early_checks()
    {
		// Loads helper message to manage returning messages
		$this->load->helper('hlp_return_object');

		// Loads helper session to manage the php session
		$this->load->helper('hlp_session');

		// Loads helper with generic utility function
		$this->load->helper('hlp_common');
    }

	/**
     * Totally overrode parent's _perform_library_auth method to keep file and class name
	 * for AuthLib and to call AuthLib with the extra parameter
     */
    protected function _perform_library_auth($username = '', $password = NULL)
    {
        if (empty($username))
        {
            log_message('error', 'Library Auth: Failure, empty username');
            return FALSE;
        }

        $auth_library_class = $this->config->item('auth_library_class');
        $auth_library_function = $this->config->item('auth_library_function');

        if (empty($auth_library_class))
        {
            log_message('debug', 'Library Auth: Failure, empty auth_library_class');
            return FALSE;
        }

        if (empty($auth_library_function))
        {
            log_message('debug', 'Library Auth: Failure, empty auth_library_function');
            return FALSE;
        }

        if (is_callable([$auth_library_class, $auth_library_function]) === FALSE)
        {
            $this->load->library($auth_library_class, array(false));
        }

        return $this->{strtolower($auth_library_class)}->$auth_library_function($username, $password);
    }

	/**
     * Totally overrode parent's _remap method to change the naming convention of controllers methods
     */
    public function _remap($object_called, $arguments = [])
    {
        // Should we answer if not over SSL?
        if ($this->config->item('force_https') && $this->request->ssl === FALSE)
        {
            $this->response([
                $this->config->item('rest_status_field_name') => FALSE,
                $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_unsupported')
            ], self::HTTP_FORBIDDEN);

            $this->is_valid_request = false;
        }

        // Remove the supported format from the function name e.g. index.json => index
        $object_called = preg_replace('/^(.*)\.(?:'.implode('|', array_keys($this->_supported_formats)).')$/', '$1', $object_called);

		// NOTE: START changes
		$controller_method = $object_called.'_'.$this->request->method; // Method name fallback
		// If the config entry rest_methods_name_format is provided and is not empty then use it to produce the method name
		if (!empty($this->config->item('rest_methods_name_format')))
		{
			$controller_method = sprintf($this->config->item('rest_methods_name_format'), $object_called, $this->request->method);
		}
		// END changes

        // Does this method exist? If not, try executing an index method
        if (!method_exists($this, $controller_method)) {
            $controller_method = "index_" . $this->request->method;
            array_unshift($arguments, $object_called);
        }

        // Do we want to log this method (if allowed by config)?
        $log_method = ! (isset($this->methods[$controller_method]['log']) && $this->methods[$controller_method]['log'] === FALSE);

        // Use keys for this method?
        $use_key = ! (isset($this->methods[$controller_method]['key']) && $this->methods[$controller_method]['key'] === FALSE);

        // They provided a key, but it wasn't valid, so get them out of here
        if ($this->config->item('rest_enable_keys') && $use_key && $this->_allow === FALSE)
        {
            if ($this->config->item('rest_enable_logging') && $log_method)
            {
                $this->_log_request();
            }

            // fix cross site to option request error
            if($this->request->method == 'options') {
                exit;
            }

            $this->response([
                $this->config->item('rest_status_field_name') => FALSE,
                $this->config->item('rest_message_field_name') => sprintf($this->lang->line('text_rest_invalid_api_key'), $this->rest->key)
            ], self::HTTP_FORBIDDEN);

            $this->is_valid_request = false;
        }

        // Check to see if this key has access to the requested controller
        if ($this->config->item('rest_enable_keys') && $use_key && empty($this->rest->key) === FALSE && $this->_check_access() === FALSE)
        {
            if ($this->config->item('rest_enable_logging') && $log_method)
            {
                $this->_log_request();
            }

            $this->response([
                $this->config->item('rest_status_field_name') => FALSE,
                $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_api_key_unauthorized')
            ], self::HTTP_UNAUTHORIZED);

            $this->is_valid_request = false;
        }

        // Sure it exists, but can they do anything with it?
        if (! method_exists($this, $controller_method))
        {
            $this->response([
                $this->config->item('rest_status_field_name') => FALSE,
                $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_unknown_method')
            ], self::HTTP_METHOD_NOT_ALLOWED);

            $this->is_valid_request = false;
        }

        // Doing key related stuff? Can only do it if they have a key right?
        if ($this->config->item('rest_enable_keys') && empty($this->rest->key) === FALSE)
        {
            // Check the limit
            if ($this->config->item('rest_enable_limits') && $this->_check_limit($controller_method) === FALSE)
            {
                $response = [$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_api_key_time_limit')];
                $this->response($response, self::HTTP_UNAUTHORIZED);

                $this->is_valid_request = false;
            }

            // If no level is set use 0, they probably aren't using permissions
            $level = isset($this->methods[$controller_method]['level']) ? $this->methods[$controller_method]['level'] : 0;

            // If no level is set, or it is lower than/equal to the key's level
            $authorized = $level <= $this->rest->level;
            // IM TELLIN!
            if ($this->config->item('rest_enable_logging') && $log_method)
            {
                $this->_log_request($authorized);
            }
            if($authorized === FALSE)
            {
                // They don't have good enough perms
                $response = [$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_api_key_permissions')];
                $this->response($response, self::HTTP_UNAUTHORIZED);

                $this->is_valid_request = false;
            }
        }

        //check request limit by ip without login
        elseif ($this->config->item('rest_limits_method') == "IP_ADDRESS" && $this->config->item('rest_enable_limits') && $this->_check_limit($controller_method) === FALSE)
        {
            $response = [$this->config->item('rest_status_field_name') => FALSE, $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_ip_address_time_limit')];
            $this->response($response, self::HTTP_UNAUTHORIZED);

            $this->is_valid_request = false;
        }

        // No key stuff, but record that stuff is happening
        elseif ($this->config->item('rest_enable_logging') && $log_method)
        {
            $this->_log_request($authorized = TRUE);
        }

        // Call the controller method and passed arguments
        try
        {
            if ($this->is_valid_request) {
                call_user_func_array([$this, $controller_method], $arguments);
            }
        }
        catch (Exception $ex)
        {
            if ($this->config->item('rest_handle_exceptions') === FALSE) {
                throw $ex;
            }

            // If the method doesn't exist, then the error will be caught and an error response shown
            $_error = &load_class('Exceptions', 'core');
            $_error->show_exception($ex);
        }
    }
}
