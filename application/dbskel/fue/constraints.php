<?php

$constraintsArray = array(
	'pk_tbl_aktivitaet' => 'ALTER TABLE fue.tbl_aktivitaet ADD CONSTRAINT pk_tbl_aktivitaet PRIMARY KEY (aktivitaet_kurzbz)',
	'fk_projekt_oe' => 'ALTER TABLE fue.tbl_aktivitaet ADD CONSTRAINT fk_test FOREIGN KEY (oe_kurzbz) REFERENCES public.tbl_organisationseinheit (oe_kurzbz)',
	'uk_beschreibung' => 'ALTER TABLE fue.tbl_aktivitaet ADD CONSTRAINT uk_beschreibung UNIQUE (beschreibung)',
	'testchk' => 'ALTER TABLE fue.tbl_aktivitaet ADD CONSTRAINT testchk CHECK (sort > 0)'
);

