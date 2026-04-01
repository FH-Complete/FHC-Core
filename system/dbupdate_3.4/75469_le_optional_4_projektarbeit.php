<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

$cols_to_add = [
	'lehrveranstaltung_id'   => "INTEGER",
	'studiensemester_kurzbz' => "VARCHAR(16)"
];

foreach ($cols_to_add as $col => $type) {
	$check = "SELECT 1 FROM information_schema.columns 
              WHERE table_schema = 'lehre' AND table_name = 'tbl_projektarbeit' AND column_name = '$col'";

	if ($result = $db->db_query($check)) {
		if ($db->db_num_rows($result) === 0) {
			$qry = "ALTER TABLE lehre.tbl_projektarbeit ADD COLUMN IF NOT EXISTS $col $type;";
			if ($db->db_query($qry)) {
				echo "<br>Column $col hinzugefuegt.";
			} else {
				echo "<strong>Error adding $col: ".$db->db_last_error()."</strong><br>";
			}
		}
	}
}

// retrieve lehrveranstaltung_id & studiensemester_kurzbz from tbl_lehreinheit
// into new columns based on the existing reference
$migration_qry = "UPDATE lehre.tbl_projektarbeit p
                  SET lehrveranstaltung_id = l.lehrveranstaltung_id,
                      studiensemester_kurzbz = l.studiensemester_kurzbz
                  FROM lehre.tbl_lehreinheit l
                  WHERE p.lehreinheit_id = l.lehreinheit_id
                  AND (p.lehrveranstaltung_id IS NULL OR p.studiensemester_kurzbz IS NULL);";

if ($db->db_query($migration_qry)) {
	echo "<br>Datenmigration von lehreinheit_id zu lehrveranstaltung_id & studiensemester_kurzbz abgeschlossen.";
} else {
	echo "<strong>Migration Error: ".$db->db_last_error()."</strong><br>";
}

// set NOT NULL and make lehreinheit_id NULLable
$constraint_qry = "ALTER TABLE lehre.tbl_projektarbeit 
                   ALTER COLUMN lehrveranstaltung_id SET NOT NULL,
                   ALTER COLUMN studiensemester_kurzbz SET NOT NULL,
                   ALTER COLUMN lehreinheit_id DROP NOT NULL;";

if ($db->db_query($constraint_qry)) {
	echo "<br>Constraints updated lehrveranstaltung_id SET NOT NULL, studiensemester_kurzbz SET NOT NULL, lehreinheit_id DROP NOT NULL.";
} else {
	echo "<strong>Alter Constraints Error: ".$db->db_last_error()."</strong><br>";
}

// add fkey for lehrveranstaltung_id
$fk_check_lv = "SELECT 1 FROM information_schema.table_constraints 
                WHERE constraint_name = 'fk_projektarbeit_lehrveranstaltung_id' AND table_schema = 'lehre'";

if ($result = $db->db_query($fk_check_lv)) {
	if ($db->db_num_rows($result) === 0) {
		$qry = "ALTER TABLE lehre.tbl_projektarbeit
               ADD CONSTRAINT fk_projektarbeit_lehrveranstaltung_id
               FOREIGN KEY (lehrveranstaltung_id) 
               REFERENCES lehre.tbl_lehrveranstaltung (lehrveranstaltung_id)
               ON DELETE RESTRICT;";
		if ($db->db_query($qry)) echo '<br>fk_projektarbeit_lehrveranstaltung_id hinzugefuegt';
	}
}

// add fkey for studiensemester_kurzbz
$fk_check_sem = "SELECT 1 FROM information_schema.table_constraints 
                 WHERE constraint_name = 'fk_projektarbeit_studiensemester_kurzbz' AND table_schema = 'lehre'";

if ($result = $db->db_query($fk_check_sem)) {
	if ($db->db_num_rows($result) === 0) {
		$qry = "ALTER TABLE lehre.tbl_projektarbeit
               ADD CONSTRAINT fk_projektarbeit_studiensemester_kurzbz
               FOREIGN KEY (studiensemester_kurzbz) 
               REFERENCES public.tbl_studiensemester (studiensemester_kurzbz)
               ON DELETE RESTRICT;";
		if ($db->db_query($qry)) echo '<br>fk_projektarbeit_studiensemester_kurzbz hinzugefuegt';
	}
}