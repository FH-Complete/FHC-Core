<?php

/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

defined('BASEPATH') OR exit('No direct script access allowed');

class Person extends API_Controller
{
	//public $session;
    /**
     * Person API constructor.
     */
    function __construct()
    {
        parent::__construct();

        $this->load->model('person/person_model');
    }

    public function person_get()
    {
        //if (!$this->session_model->validate($this->get('session_id'), $this->get('device_id')))
        //    $this->response(array(['success' => false, 'message' => 'access denied']), REST_Controller::HTTP_UNAUTHORIZED);

        $code = $this->get('code');
        
        if (!is_null($code))
			$result = $this->person_model->getPersonByCode($code);
		//	var_dump($result[0]);

        if (empty($result))
        {
            $payload = [
                        'success' => false,
                        'message' => 'Person not found'
                    ];
                    $httpstatus = REST_Controller::HTTP_OK;
        }
		else
		{
			// return all available locations
            $payload = [
                'success' => true,
                'message' => 'Person with code found',
                'person_id' => $result[0]->person_id
            ];
            $httpstatus = REST_Controller::HTTP_OK;
		}

        // Set the response and exit
        $this->response($payload, $httpstatus);
    }

    /**
     * Creates a new location for whisper or returns all available locations
     * within a certain radius
     * @return string JSON that indicates success/failure of creating location
     * @example http://wsp.fortyseeds.at/backend/api/whisper/location/name/Foo/latitude/37.37888785004527/longitude/-120.333251953125/session_id/55afab8ba6f1b/device_id/abcdef123
     */
    public function location_get()
    {
        if (!$this->session_model->validate($this->get('session_id'), $this->get('device_id')))
            $this->response(array(['success' => false, 'message' => 'access denied']), REST_Controller::HTTP_UNAUTHORIZED);

        $name = urldecode($this->get('name'));
        $latitude = $this->get('latitude');
        $longitude = $this->get('longitude');

        if (!empty($name) && !empty($latitude) && !empty($longitude))
        {
            // check available locations
            $locsWithinRadius = $this->location_model->getLocationsWithinRadius($latitude, $longitude);

            if (empty($locsWithinRadius))
            {
                // create new location
                $locId = $this->location_model->create($name, $latitude, $longitude);

                if ($locId !== false)
                {
                    $payload = [
                        'success' => true,
                        'message' => 'location created successfully',
                        'location_id' => $locId
                    ];
                    $httpstatus = REST_Controller::HTTP_CREATED;
                }
                else
                {
                    $payload = [
                        'success' => false,
                        'message' => 'location could not be created'
                    ];
                    $httpstatus = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
                }
            }
            else
            {
                // return all available locations
                $payload = [
                    'success' => true,
                    'message' => '1 or more locations available',
                    'location_id' => $locsWithinRadius
                ];
                $httpstatus = REST_Controller::HTTP_OK;
            }
        }
        else
        {
            $payload = [
                'success' => false,
                'message' => "name, latitude or longitude missing"
            ];
            $httpstatus = REST_Controller::HTTP_BAD_REQUEST;
        }

        // Set the response and exit
        $this->response($payload, $httpstatus);
    }

    /**
     * Creates a new whisper
     * @return string JSON that indicates success/failure of creating location
     * @example http://wsp.fortyseeds.at/backend/api/whisper/create/session_id/55afab8ba6f1b/device_id/abcdef123
     */
    public function create_post()
    {
        if (!$this->session_model->validate($this->get('session_id'), $this->get('device_id')))
            $this->response(array(['success' => false, 'message' => 'access denied']), REST_Controller::HTTP_UNAUTHORIZED);

        $data = $this->post('whisper');

        // perform checks if whisper can be created
        $errormsg = "";
        $notNull = array('location_id', 'name', 'type', 'description', 'scenery', 'price', 'sportiness', 'address', 'category');
        foreach ($notNull as $key)
        {
            if (empty($data[$key]))
            {
                $errormsg = "missing data";
                break;
            }
        }

        if (empty($errormsg))
        {
            if (!empty($data['picture']))
            {
                // save file name in the profile
                $data['picture'] = $this->_savePicture($data['picture']);
            }

            // add user ID to data
            $session = $this->session_model->load($this->get('session_id'));
            $data['user_id'] = $session->user_id;

            // create new whisper
            $whisperId = $this->whisper_model->create($data);

            if ($whisperId !== false)
            {
                // check if user status change is necessary
                if ($this->status_model->current($session->user_id) != 'full' &&
                    $this->whisper_model->count($session->user_id) >= $this->config->item('userstatus_full_whisperer'))
                {
                    $this->status_model->set($session->user_id, 'full');
                }

                $payload = [
                    'success' => true,
                    'message' => 'whisper created successfully',
                    'whisper_id' => $whisperId
                ];
                $httpstatus = REST_Controller::HTTP_CREATED;
            }
            else
            {
                $payload = [
                    'success' => false,
                    'message' => 'whisper could not be created'
                ];
                $httpstatus = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
            }
        }
        else
        {
            $payload = [
                'success' => false,
                'message' => $errormsg
            ];
            $httpstatus = REST_Controller::HTTP_BAD_REQUEST;
        }

        // Set the response and exit
        $this->response($payload, $httpstatus);
    }

    /**
     * Edits a whisper
     * @return string JSON that indicates success/failure of editing whisper
     * @example http://wsp.fortyseeds.at/backend/api/whisper/edit/whisper_id/1/session_id/55afab8ba6f1b/device_id/abcdef123
     */
    public function edit_post()
    {
        if (!$this->session_model->validate($this->get('session_id'), $this->get('device_id')))
            $this->response(array(['success' => false, 'message' => 'access denied']), REST_Controller::HTTP_UNAUTHORIZED);

        $data = $this->post('whisper');
        $whisperId = $this->get('whisper_id');

        // perform checks if whisper can be edited
        $errormsg = "";
        $notNull = array('location_id', 'name', 'type', 'description', 'scenery', 'price', 'sportiness', 'address', 'category');
        foreach ($notNull as $key)
        {
            if (isset($data[$key]) && empty($data[$key]))
            {
                $errormsg = "missing data";
                break;
            }
        }

        if (empty($errormsg))
        {
            if (!empty($data['picture']))
            {
                $data['picture'] = $this->_savePicture($data['picture']);
            }

            // load user session
            $session = $this->session_model->load($this->get('session_id'));

            // save changes
            $result = $this->whisper_model->edit($whisperId, $data, $session->user_id);

            if ($result === 1)
            {
                $payload = [
                    'success' => true,
                    'message' => 'whisper edited successfully'
                ];
                $httpstatus = REST_Controller::HTTP_OK;
            }
            else
            {
                $payload = [
                    'success' => false,
                    'message' => 'whisper does not exist or does not belong to user'
                ];
                $httpstatus = REST_Controller::HTTP_BAD_REQUEST;
            }
        }
        else
        {
            $payload = [
                'success' => false,
                'message' => $errormsg
            ];
            $httpstatus = REST_Controller::HTTP_BAD_REQUEST;
        }

        // Set the response and exit
        $this->response($payload, $httpstatus);
    }

    /**
     * Returns all whispers of a user
     * @return string JSON with whisper data
     * @example http://wsp.fortyseeds.at/backend/api/whisper/personal/session_id/55afab8ba6f1b/device_id/abcdef123
     */
    public function personal_get()
    {
        if (!$this->session_model->validate($this->get('session_id'), $this->get('device_id')))
            $this->response(array(['success' => false, 'message' => 'access denied']), REST_Controller::HTTP_UNAUTHORIZED);

        $profile = $this->profile_model->loadBySession($this->get('session_id'));
        $whispers = $this->whisper_model->getByUser($profile->user_id);

        $payload = [
            'success' => true,
            'message' => 'whispers returned successfully',
            'whispers' => $whispers
        ];
        $httpstatus = REST_Controller::HTTP_OK;

        // Set the response and exit
        $this->response($payload, $httpstatus);
    }

    /**
     * Deletes a whisper
     * @return string JSON that indicates success/failure of deleting whisper
     * @example http://wsp.fortyseeds.at/backend/api/whisper/delete/session_id/d05434b3728bd2a525a1947c3ec4d754/device_id/abcdef123/whisper_id/7/reason/Gef%C3%A4llt%20mir%20nicht%20mehr
     */
    public function delete_get()
    {
        if (!$this->session_model->validate($this->get('session_id'), $this->get('device_id')))
            $this->response(array(['success' => false, 'message' => 'access denied']), REST_Controller::HTTP_UNAUTHORIZED);

        $whisperId = $this->get('whisper_id');
        $this->get('reason') == '' ? $reason = 'null' : $reason = "'" . urldecode($this->get('reason')) . "'";
        $profile = $this->profile_model->loadBySession($this->get('session_id'));

        $result = $this->whisper_model->delete($whisperId, $profile->user_id, $reason);

        if ($result === 0)
        {
            $payload = [
                'success' => false,
                'message' => 'whisper does not exist or does not belong to user'
            ];
            $httpstatus = REST_Controller::HTTP_BAD_REQUEST;
        }
        else
        {
            $payload = [
                'success' => true,
                'message' => 'whisper deleted successfully'
            ];
            $httpstatus = REST_Controller::HTTP_OK;
        }

        // Set the response and exit
        $this->response($payload, $httpstatus);
    }

    /**
     * Decodes base64 image data and saves file to disk
     * @param string $base64data
     * @return string path and file name of picture
     */
    private function _savePicture($base64data)
    {
        // decode data and get file type
        $imgdata = base64_decode($base64data);
        $fileinfo = finfo_open();
        $mimetype = finfo_buffer($fileinfo, $imgdata, FILEINFO_MIME_TYPE);
        $ext = str_replace('image/', '.', $mimetype);

        $tmpfname = tempnam($this->config->item('whisperpic_path'), "wsp");
        $picfname = $tmpfname . $ext;

        // save pic to disk
        $handle = fopen($picfname, "w");
        fwrite($handle, $imgdata);
        fclose($handle);

        // delete tmp file
        if (is_file($tmpfname))
            unlink($tmpfname);

        // return file name
        return $picfname;
    }
}
