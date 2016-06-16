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
}