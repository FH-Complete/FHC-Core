<?php

require_once APPPATH . '/libraries/REST_Controller.php';

class APIv1_Controller extends REST_Controller 
{
    function __construct()  
    {
        parent::__construct();
        //$this->load->library('session'); // -> autoload
        //$this->load->library('database'); -> autoload
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