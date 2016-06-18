<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Vorlage extends CI_Migration {

    public function up()
    {
		if (! @$this->db->simple_query('SELECT attribute FROM public.tbl_vorlage'))
		{
			$query= "ALTER TABLE public.tbl_vorlage 
				ADD COLUMN attribute json;
                ";
  			if ($this->db->simple_query($query))
				echo 'Column public.tbl_vorlage.attribute added!';
			else
				echo "Error adding public.tbl_vorlage.attribute!";
		}
		
		if (! @$this->db->simple_query('SELECT subject FROM public.tbl_vorlagestudiengang'))
		{
			$query= "ALTER TABLE public.tbl_vorlagestudiengang
				ADD COLUMN subject text;
                ";
  			if ($this->db->simple_query($query))
				echo 'Column public.tbl_vorlagestudiengang.subject added!';
			else
				echo "Error adding public.tbl_vorlagestudiengang.subject!";
		}

		if (! @$this->db->simple_query('SELECT orgform_kurzbz FROM public.tbl_vorlagestudiengang'))
		{
			$query= "ALTER TABLE public.tbl_vorlagestudiengang
				ADD COLUMN orgform_kurzbz varchar(3) references bis.tbl_orgform(orgform_kurzbz);
                ";
  			if ($this->db->simple_query($query))
				echo 'Column public.tbl_vorlagestudiengang.orgform_kurzbz added!';
			else
				echo "Error adding public.tbl_vorlagestudiengang.orgform_kurzbz!";
		}
	}

    public function down()
    {
		try
		{
			$this->dbforge->drop_column('public.tbl_vorlage', 'attribute');
			$this->dbforge->drop_column('public.tbl_vorlagestudiengang', 'subject');
			$this->dbforge->drop_column('public.tbl_vorlagestudiengang', 'orgform_kurzbz');
            echo "Column public.tbl_vorlage.attribute, public.tbl_vorlagestudiengang.subject, public.tbl_vorlagestudiengang.orgform_kurzbz dropped!";
		}
		catch(Exception $e)
		{
			echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
			echo $this->db->error();
		}
    }
}
