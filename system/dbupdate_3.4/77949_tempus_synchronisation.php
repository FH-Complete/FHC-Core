<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

if(!$result = @$db->db_query("SELECT kalender_syncstatus_id FROM lehre.tbl_kalender_syncstatus LIMIT 1"))
{
	$qry = "CREATE TABLE lehre.tbl_kalender_syncstatus 
			(
				kalender_syncstatus_id bigserial NOT NULL,
				oe_kurzbz character varying(32),
				studiensemester_kurzbz character varying(32),
				datum_bis date,
				studienplan_id integer,
				ausbildungssemester smallint,
				sync_status_kurzbz character varying(32),
				mail boolean DEFAULT false NOT NULL,
				insertamum timestamp DEFAULT now(),
				insertvon character varying(32),
				updateamum timestamp,
				updatevon character varying(32),
				CONSTRAINT tbl_kalender_syncstatus_pk PRIMARY KEY (kalender_syncstatus_id)
			);

		ALTER TABLE lehre.tbl_kalender_syncstatus ADD CONSTRAINT tbl_kalender_syncstatus_oe_kurzbz_fk FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_kalender_syncstatus ADD CONSTRAINT tbl_kalender_syncstatus_studiensemester_kurzbz_fk FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester(studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_kalender_syncstatus ADD CONSTRAINT tbl_kalender_syncstatus_studienplan_id_fk FOREIGN KEY (studienplan_id) REFERENCES lehre.tbl_studienplan (studienplan_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_kalender_syncstatus ADD CONSTRAINT tbl_kalender_syncstatus_status_sync_status_kurzbz_fk FOREIGN KEY (sync_status_kurzbz) REFERENCES lehre.tbl_kalender_status (status_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT, UPDATE ON lehre.tbl_kalender_syncstatus_kalender_syncstatus_id_seq TO vilesci;
		GRANT SELECT ON lehre.tbl_kalender_syncstatus_kalender_syncstatus_id_seq TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_kalender_syncstatus to vilesci;
		GRANT SELECT ON lehre.tbl_kalender_typ to web;";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_kalender_syncstatus: '.$db->db_last_error().'</strong><br>';
	else
		echo ' lehre.tbl_kalender_syncstatus: Tabelle hinzugefuegt<br>';

}