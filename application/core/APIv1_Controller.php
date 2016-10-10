<?php

require_once APPPATH . '/libraries/REST_Controller.php';

class APIv1_Controller extends REST_Controller 
{
    function __construct()  
    {
        parent::__construct();
		$this->load->helper('Message');
    }
    
	/** ---------------------------------------------------------------
	 * Success
	 *
	 * @param   mixed  $retval
	 * @return  array
	 */
	protected function _success($retval, $message = null)
	{
		return success($retval, $message);
	}

	/** ---------------------------------------------------------------
	 * General Error
	 *
	 * @return  array
	 */
	protected function _error($retval, $message = null)
	{
		return error($retval, $message);
	}
	
    /**
     * 
     * @param type $data
     * @return typeparses empty string to NULL
     */
    protected function _parseData($data)
    {
        if(is_array($data))
        {
            foreach($data as $key=>$value)
            {
                if($value === "")
                {
                    $data[$key] = NULL;
                }
            }
            return $data;
        }
        elseif(is_object($data))
        {
            //TODO
        }
        else
        {
            if($data == "")
            {
                return NULL;
            }
        }
    }
    
    /** ----------------------------------------------------------------------------------------------------------------------------------
     * Workaround for converting a pgsql array to a php array
     * To be dropped as soon as possible :D
     */
    protected function escapeArrays($result, $fields_names)
	{
		if (is_object($result) && isset($result->retval) && is_array($result->retval))
		{
			for ($i = 0; $i < count($result->retval); $i++)
			{
				foreach($fields_names as $field_name)
				{
					if (isset($result->retval[$i]->{$field_name}))
					{
						$result->retval[$i]->{$field_name} = $this->_pgsqlArrayToPhpArray($result->retval[$i]->{$field_name});
					}
				}
			}
		}
		
		return $result;
	}
	
	/**
	* To be moved to DB_model
	*/
	private function _pgsqlArrayToPhpArray($string)
	{
		$result = array();
		
		if (!empty($string))
		{
			preg_match_all(
				'/(?<=^\{|,)(([^,"{]*)|\s*"((?:[^"\\\\]|\\\\(?:.|[0-9]+|x[0-9a-f]+))*)"\s*)(,|(?<!^\{)(?=\}$))/i',
				$string,
				$matches,
				PREG_SET_ORDER
			);
			
			foreach ($matches as $match)
			{
				$result[] = $match[3] != '' ? stripcslashes($match[3]) : (strtolower($match[2]) == 'null' ? null : $match[2]);
			}
		}
		
		return $result;
	}
	
	// --------------------------------------------------------------------------------------------------------------------------------------------
}