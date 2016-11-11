<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_fhc30 extends CI_Migration
{
	public function up()
	{
		echo '<br/><h1>Update to FHC 3.0</h1><br/>';
		$this->db=$this->load->database('system', true); 	
		$this->load->helper('fhcdb');
		$db = new basis_db($this);
		require_once('./system/dbupdate_3.0.php');
	}

	public function down()
	{
		/*$this->db->simple_query('DROP TABLE fue.tbl_scrumteam;');
		$this->db->simple_query('DROP TABLE lehre.tbl_studienordnung;');
		$this->db->simple_query('DROP TABLE lehre.tbl_studienordnung_semester;');
		$this->db->simple_query('DROP TABLE lehre.tbl_studienplan;');*/
	}
}