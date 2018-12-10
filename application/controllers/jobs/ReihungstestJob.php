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

if (! defined('BASEPATH'))
	exit('No direct script access allowed');

class ReihungstestJob extends FHC_Controller
{
	private $VILESCI_RT_VERWALTUNGS_URL;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Allow script execution only from CLI
		if ($this->input->is_cli_request())
		{
			$cli = true;
		}
		else
		{
			$this->output->set_status_header(403, 'Jobs must be run from the CLI');
			echo "Jobs must be run from the CLI";
			exit;
		}
		
		$this->VILESCI_RT_VERWALTUNGS_URL = site_url(). "/organisation/Reihungstest";
		
		// Load models
		$this->load->model('crm/Reihungstest_model', 'ReihungstestModel');

		// Load helpers
		$this->load->helper('hlp_sancho_helper');
	}

	/**
	 * Main function index as help
	 *
	 * @return	void
	 */
	public function index()
	{
		$result = "The following are the available command line interface commands\n\n";
		$result .= "php index.ci.php jobs/ReihungstestJob runReihungstestInfo";

		echo $result. PHP_EOL;
	}
	
	public function runReihungstestJob()
	{
		// Get study plans that have no assigned placement tests yet
		$result = $this->ReihungstestModel->checkMissingReihungstest();

		$missing_rt_arr = array();
		if (hasData($result))
		{
			$missing_rt_arr = $result->retval;
		}
		elseif (isError($result))
		{
			show_error($result->error);
		}
		
		// Get free places
		$result = $this->ReihungstestModel->getFreePlaces();
				
		$free_places_arr = array();
		if (hasData($result))
		{
			$free_places_arr = $result->retval;
		}
		elseif (isError($result))
		{
			show_error($result->error);
		}
		
		// Prepare data for mail template 'ReihungstestJob'
		$content_data_arr = $this->_getContentData($missing_rt_arr, $free_places_arr);
		
		// Send email in Sancho design
		if (!empty($missing_rt_arr) || !empty($free_places_arr))
		{
			sendSanchoMail(
				'ReihungstestJob',
				$content_data_arr,
				MAIL_INFOCENTER,
				'Support für die Reihungstest-Verwaltung');
		}
	}
	
	// ------------------------------------------------------------------------
	// Private methods
	/**
	 * Returns associative array with data as needed in the reihungstest job template.
	 * @param array $missing_rt_arr	Array with studienpläne, which have no assigned placement tests.
	 * @param array $free_places_arr Array with info and amount of free placement test places.
	 * @return array 
	 */
	private function _getContentData($missing_rt_arr, $free_places_arr)
	{
		$style_tbl1 = ' cellpadding="0" cellspacing="10" width="100%" style="font-family: courier, verdana, sans-serif; font-size: 0.95em; border: 1px solid #000000;" ';
		$style_tbl2 = ' cellpadding="0" cellspacing="20" width="100%" style="font-family: courier, verdana, sans-serif; font-size: 0.95em; border: 1px solid #000000;" ';
		
		// Prepare HTML table with study plans that have no placement tests yet
		if (!empty($missing_rt_arr))
		{
			$studienplan_list = '
				<table'. $style_tbl2.'>
			';
			
			foreach ($missing_rt_arr as $rt)
			{
				$studienplan_list .= ' 
					<tr><td>'. $rt->bezeichnung. '</td></tr>
				';
			}
			
			$studienplan_list .= ' 
				</table>
			';
		}
		else
		{
			$studienplan_list = '
				<table'. $style_tbl1.'>					
					<tr><td>Alles okay! Alle Studienpläne haben zumindest einen Reihungstest.</td></tr>
				</table>
			';
		}
		
		// Prepare HTML table with information and amount of free places
		if (!empty($free_places_arr))
		{
			$freie_plaetze_list = '
				<table'. $style_tbl2.'>
					<tr>
						<th>Fakultät</th>
						<th>Reihungstesttermine</th>
						<th>Freie Plätze</th>
					</tr>
			';
			
			foreach ($free_places_arr as $free_place)
			{
				$datum = new DateTime($free_place->datum);
				$style_alarm = ($free_place->freie_plaetze <= 5) ? ' style=" color: red; font-weight: bold" ' : '';	// mark if <=5 free places
				
				$freie_plaetze_list .= '
					<tr>
						<td width="350">'. $free_place->fakultaet. '</td>
						<td align="center">'. $datum->format('d.m.Y'). '</td>
						<td align="center"'. $style_alarm.'>'. $free_place->freie_plaetze. '</td>
					</tr>
				';			
			}
			
			$freie_plaetze_list .= '
				</table>
			';
		}
		else
		{
			$freie_plaetze_list = '
				<table'. $style_tbl1.'>						
					<tr><td>Es gibt heute keine Ergebnisse zu freien Reihungstestplätze.</td></tr>
				</table>
			';
		}
			
		// Set associative array with the prepared HTML tables and URL be used by the template's variables
		$content_data_arr['studienplan_list'] = $studienplan_list;
		$content_data_arr['freie_plaetze_list'] = $freie_plaetze_list;
		$content_data_arr['link'] = $this->VILESCI_RT_VERWALTUNGS_URL;
		;

		return $content_data_arr;
	}
}

