<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if (!$result = @$db->db_query('SELECT 1 FROM public.tbl_status_grund_status LIMIT 1'))
{
	$qry = 'CREATE TABLE public.tbl_status_grund_status
			(
				status_kurzbz varchar(20) NOT NULL,
				statusgrund_id int NOT NULL,

				CONSTRAINT pk_status_grund_status PRIMARY KEY (status_kurzbz, statusgrund_id),
				CONSTRAINT fk_status_grund_status_kurzbz FOREIGN KEY (status_kurzbz) 
					REFERENCES public.tbl_status (status_kurzbz) ON DELETE RESTRICT,

				CONSTRAINT fk_status_grund_status_statusgrund_id FOREIGN KEY (statusgrund_id)
					REFERENCES public.tbl_status_grund (statusgrund_id) ON DELETE RESTRICT                                
			);

			GRANT SELECT ON public.tbl_status_grund_status TO web;
			GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_status_grund_status TO vilesci;

			INSERT INTO public.tbl_status_grund_status (status_kurzbz, statusgrund_id)
			SELECT status_kurzbz, statusgrund_id FROM public.tbl_status_grund;
		';

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_status_grund_status: '.$db->db_last_error().'</strong><br>';
	else
	{
		echo 'public.tbl_status_grund_status: Tabelle hinzugefuegt<br>';

		$qryDrop = "ALTER TABLE public.tbl_status_grund DROP COLUMN status_kurzbz;";

		if(!$db->db_query($qryDrop))
			echo '<strong>public.tbl_status_grund: '.$db->db_last_error().'</strong><br>';
		else
			echo 'public.tbl_status_grund: Spalte status_kurzbz entfernt.<br>';
	}

}