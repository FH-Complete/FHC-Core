<?php
/*!
* @brief This function sends compute requests to
* ZipComp-Task and waits for response:
* @image html ZipCmd_ZipComp_Communication.png
*
* <!-- Hide plantuml commands from Doxygen inside comment.
*  Note: Use of the Doxygen tag command to hide code in 1.7.3 will hide the Doxygen docs that follow.
*  Warning: Don't replaced plantuml commands '@' with '\' - it won't work.
* @startuml ZipCmd_ZipComp_Communication.png
*
* ZipCmd -> ZipComp: First Compute Request
* ZipCmd <-- ZipComp: First Compute Response
*
* ZipCmd -> ZipComp: Second Compute Request
* ZipCmd <-- ZipComp: Second Compute Response
*
* @enduml
* -->
*
* @return some value on success.
*/

defined('BASEPATH') OR exit('No direct script access allowed');

/** 
 * @class Rest_server
 * @brief Rest Server Controller
 *
 * A more detailed class description.
*/
class Rest_server extends CI_Controller {

    public function index()
    {
        $this->load->helper('url');

        $this->load->view('rest_server');
    }
}
