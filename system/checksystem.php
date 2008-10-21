<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 ******************************************************************************
 * Beschreibung:
 * Dieses Skript prueft die gesamte Systemumgebung und sollte nach jedem Update gestartet werden.
 * Geprueft wird: - die Datenbank auf aktualitaet, dabei werden fehlende Attribute angelegt.
 *                - Verzeichnisse (ob vorhanden und beschreibbar falls noetig).
 */

require ('../vilesci/config.inc.php');

// Datenbank Verbindung
//if (!$conn = pg_pconnect("host=.technikum-wien.at dbname= user= password="))
if (!$conn = pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!'.pg_last_error($conn));

echo '<H1>Systemcheck!</H1>';
echo '<H2>DB-Updates!</H2>';

// ************* Kontaktmedium **********************************************************
if (!@pg_query($conn,'SELECT * FROM public.tbl_kontaktmedium LIMIT 1;'))
{
	$sql='	ALTER TABLE tbl_preinteressent ADD COLUMN kontaktmedium_kurzbz varchar(32);
			Create table public.tbl_kontaktmedium
			(
				kontaktmedium_kurzbz Varchar(32) NOT NULL,
				beschreibung Varchar(256),
				constraint pk_tbl_kontaktmedium primary key (kontaktmedium_kurzbz)
			);
			Grant select on public.tbl_kontaktmedium to group "admin";
			Grant update on public.tbl_kontaktmedium to group "admin";
			Grant delete on public.tbl_kontaktmedium to group "admin";
			Grant insert on public.tbl_kontaktmedium to group "admin";
			Grant select on public.tbl_kontaktmedium to group "web";
			Alter table tbl_preinteressent add Constraint "kontaktmedium_preinteressent" foreign key ("kontaktmedium_kurzbz")
				references public.tbl_kontaktmedium ("kontaktmedium_kurzbz") on update cascade on delete restrict;
		';
		if (!pg_query($conn,$sql))
			echo '<strong>public.tbl_kontaktmedium: '.pg_last_error($conn).' </strong><BR>';
		else
			echo 'Tabelle public.tbl_kontaktmedium hinzugefuegt!<BR>Tabelle public.tbl_preinteressent.kontaktmedium_kurzbz hinzugefuegt!<BR>';

}

// ************** kommune.tbl_wettbewerbtyp.farbe **********************************************
if (!@pg_query($conn,'SELECT farbe FROM kommune.tbl_wettbewerbtyp LIMIT 1;'))
{
	$sql="	ALTER TABLE kommune.tbl_wettbewerbtyp ADD COLUMN farbe char(6);
		";
	if (!@pg_query($conn,$sql))
		echo '<strong>kommune.tbl_wettbewerbtyp: '.pg_last_error($conn).' </strong><BR>';
	else
		echo '	farbe wurde bei kommune.tbl_wettbewerbtyp hinzugefuegt!<BR>';
}

// ************** public.tbl_person.kurzbeschreibung **********************************************
if (!@pg_query($conn,'SELECT kurzbeschreibung FROM public.tbl_person LIMIT 1;'))
{
	$sql="	ALTER TABLE public.tbl_person ADD COLUMN kurzbeschreibung text;
			COMMENT ON COLUMN public.tbl_person.kurzbeschreibung IS 'Lebenslauf, OEH-Kandidatur, Kollegiumswahl, etc. ';
		";
	if (!@pg_query($conn,$sql))
		echo '<strong>public.tbl_person: '.pg_last_error($conn).' </strong><BR>';
	else
		echo '	kurzbeschreibung wurde bei public.tbl_person hinzugefuegt!<BR>';
}

// ************** public.tbl_benutzerfunktion.semester **********************************************
if (!@pg_query($conn,'SELECT semester FROM public.tbl_benutzerfunktion LIMIT 1;'))
{
	$sql="	ALTER TABLE public.tbl_benutzerfunktion ADD COLUMN semester smallint;
		";
	if (!@pg_query($conn,$sql))
		echo '<strong>public.tbl_benutzerfunktion: '.pg_last_error($conn).' </strong><BR>';
	else
		echo '	semester wurde bei public.tbl_benutzerfunktion hinzugefuegt!<BR>';
}


// ************** lehre.tbl_moodle.gruppen **********************************************
if (!@pg_query($conn,'SELECT gruppen FROM lehre.tbl_moodle LIMIT 1;'))
{
	$sql="	ALTER TABLE lehre.tbl_moodle ADD COLUMN gruppen boolean;
			COMMENT ON COLUMN lehre.tbl_moodle.gruppen IS 'Soll beim Sync die Gruppenzuordnung uebernommen werden?';
			UPDATE lehre.tbl_moodle SET gruppen=TRUE;
			ALTER TABLE lehre.tbl_moodle ALTER COLUMN gruppen SET NOT NULL;
			ALTER TABLE lehre.tbl_moodle ALTER COLUMN gruppen SET DEFAULT TRUE;
		";
	if (!@pg_query($conn,$sql))
		echo '<strong>lehre.tbl_moodle: '.pg_last_error($conn).' </strong><BR>';
	else
		echo '	gruppen wurde bei lehre.tbl_moodle hinzugefuegt!<BR>';
}

// ************* Wettbewerbstyp **********************************************************
if (!@pg_query($conn,'SELECT * FROM kommune.tbl_wettbewerbtyp LIMIT 1;'))
{
	$sql='	Create table kommune.tbl_wettbewerbtyp
			(
				wbtyp_kurzbz Varchar(16) NOT NULL,
				bezeichnung Varchar(256),
				constraint pk_tbl_wettbewerbtyp primary key (wbtyp_kurzbz)
			);
			Grant select on kommune.tbl_wettbewerbtyp to group "admin";
			Grant update on kommune.tbl_wettbewerbtyp to group "admin";
			Grant delete on kommune.tbl_wettbewerbtyp to group "admin";
			Grant insert on kommune.tbl_wettbewerbtyp to group "admin";
			Grant select on kommune.tbl_wettbewerbtyp to group "web";
			Grant update on kommune.tbl_wettbewerbtyp to group "web";
			Grant insert on kommune.tbl_wettbewerbtyp to group "web";
		';
		if (!pg_query($conn,$sql))
			echo '<strong>kommune.tbl_wettbewerbtyp: '.pg_last_error($conn).' </strong><BR>';
		else
			echo 'Tabelle kommune.tbl_wettbewerbtyp hinzugefuegt!<BR>';

}

// ************** kommune.tbl_wettbewerb.wbtyp_kurzbz, uid ************************
if (!@pg_query($conn,'SELECT wbtyp_kurzbz, uid FROM kommune.tbl_wettbewerb LIMIT 1;'))
{
	$sql="	ALTER TABLE kommune.tbl_wettbewerb ADD COLUMN wbtyp_kurzbz Varchar(16);
			ALTER TABLE kommune.tbl_wettbewerb ADD COLUMN uid Varchar(16);
			ALTER TABLE kommune.tbl_wettbewerb ADD COLUMN icon Text;
			COMMENT ON COLUMN kommune.tbl_wettbewerb.uid IS 'Moderator';
			ALTER TABLE kommune.tbl_wettbewerb add Constraint wettbewerbtyp_wettbewerb foreign key (wbtyp_kurzbz) references kommune.tbl_wettbewerbtyp (wbtyp_kurzbz) on update cascade on delete restrict;
		";
	if (!@pg_query($conn,$sql))
		echo '<strong>kommune.tbl_wettbewerb: '.pg_last_error($conn).' </strong><BR>';
	else
		echo '	wbtyp_kurzbz wurde bei kommune.tbl_wettbewerb hinzugefuegt!<BR>
				icon wurde bei kommune.tbl_wettbewerb hinzugefuegt!<BR>
				uid wurde bei kommune.tbl_wettbewerb hinzugefuegt!<BR>';
}

// ************** Constraint:tbl_person_geschlecht ************************
$result=pg_query($conn,"SELECT consrc FROM pg_catalog.pg_constraint WHERE conname='tbl_person_geschlecht';");
if ($row=pg_fetch_object($result))
{
	if ($row->consrc=="((geschlecht = 'm'::bpchar) OR (geschlecht = 'w'::bpchar))")
	{
		$sql="	ALTER TABLE public.tbl_person DROP CONSTRAINT tbl_person_geschlecht;
				ALTER TABLE public.tbl_person ADD CONSTRAINT tbl_person_geschlecht CHECK ((geschlecht = 'm'::bpchar) OR (geschlecht = 'w'::bpchar) OR (geschlecht = 'u'::bpchar));
			";
		if (!@pg_query($conn,$sql))
			echo '<strong>CONSTRAINT tbl_person_geschlecht: '.pg_last_error($conn).' </strong><BR>';
		else
			echo '	CONSTRAINT tbl_person_geschlecht wurde geaendert!<BR>';
	}
}
else
{
	$sql="	ALTER TABLE public.tbl_person ADD CONSTRAINT tbl_person_geschlecht CHECK ((geschlecht = 'm'::bpchar) OR (geschlecht = 'w'::bpchar) OR (geschlecht = 'u'::bpchar));
		";
	if (!@pg_query($conn,$sql))
		echo '<strong>CONSTRAINT tbl_person_geschlecht: '.pg_last_error($conn).' </strong><BR>';
	else
		echo '	CONSTRAINT tbl_person_geschlecht wurde hinzugefuegt!<BR>';
}

// ************* Moodle **********************************************************
if (!@pg_query($conn,'SELECT * FROM lehre.tbl_moodle LIMIT 1;'))
{
	$sql='	CREATE TABLE lehre.tbl_moodle
			(
				"moodle_id" Serial NOT NULL,
				"mdl_course_id" bigint NOT NULL,
				"lehreinheit_id" integer,
				"lehrveranstaltung_id" integer ,
				"studiensemester_kurzbz" Varchar(16) ,
				"insertamum" Timestamp Default now(),
				"insertvon" Varchar(16),
				constraint "pk_tbl_moodle" primary key ("moodle_id")
			);

			Alter table lehre.tbl_moodle add Constraint "lehreinheit_moodle" foreign key ("lehreinheit_id") references "lehre"."tbl_lehreinheit" ("lehreinheit_id") on update cascade on delete restrict;
			Alter table lehre.tbl_moodle add Constraint "studiensemester_moodle" foreign key ("studiensemester_kurzbz") references "public"."tbl_studiensemester" ("studiensemester_kurzbz") on update cascade on delete restrict;
			Alter table lehre.tbl_moodle add Constraint "lehrveranstaltung_moodle" foreign key ("lehrveranstaltung_id") references "lehre"."tbl_lehrveranstaltung" ("lehrveranstaltung_id") on update cascade on delete restrict;

			Grant select on lehre.tbl_moodle to group "admin";
			Grant update on lehre.tbl_moodle to group "admin";
			Grant delete on lehre.tbl_moodle to group "admin";
			Grant insert on lehre.tbl_moodle to group "admin";
			Grant select on lehre.tbl_moodle to group "web";
			Grant update on lehre.tbl_moodle to group "web";
			Grant insert on lehre.tbl_moodle to group "web";
		';
		if (!pg_query($conn,$sql))
			echo '<strong>lehre.tbl_moodle: '.pg_last_error($conn).' </strong><BR>';
		else
			echo 'Tabelle lehre.tbl_moodle hinzugefuegt!<BR>';
}

// ************* Newssprache **********************************************************
if (!@pg_query($conn,'SELECT * FROM campus.tbl_newssprache LIMIT 1;'))
{
	if (@pg_query($conn,'SELECT * FROM public.tbl_newssprache LIMIT 1;'))
		if (!@pg_query($conn,'DROP TABLE public.tbl_newssprache;'))
			echo '<strong>public.tbl_newssprache: '.pg_last_error($conn).' </strong><BR>';
		else
			echo 'public.tbl_newssprache wurde geloescht!<BR>';
	$sql='	CREATE TABLE campus.tbl_newssprache
			(
				sprache Varchar(16) NOT NULL,
				news_id integer NOT NULL,
				betreff Varchar(128),
				text Text,
				updateamum Timestamp,
				updatevon Varchar(16),
				insertamum Timestamp,
				insertvon Varchar(16),
				constraint "pk_tbl_newssprache" primary key ("sprache","news_id")
			);
			ALTER TABLE campus.tbl_newssprache add Constraint "sprache_newssprache" foreign key ("sprache") references public.tbl_sprache ("sprache") on update cascade on delete restrict;
			ALTER TABLE campus.tbl_newssprache add Constraint "news_newssprache" foreign key ("news_id") references campus.tbl_news ("news_id") on update cascade on delete restrict;
			GRANT select on campus.tbl_newssprache to group "admin";
			GRANT update on campus.tbl_newssprache to group "admin";
			GRANT delete on campus.tbl_newssprache to group "admin";
			GRANT insert on campus.tbl_newssprache to group "admin";
			GRANT select on campus.tbl_newssprache to group "web";
			GRANT update on campus.tbl_newssprache to group "web";
			GRANT delete on campus.tbl_newssprache to group "web";
			GRANT insert on campus.tbl_newssprache to group "web";
		';
		if (!@pg_query($conn,$sql))
			echo '<strong>campus.tbl_newssprache: '.pg_last_error($conn).' </strong><BR>';
		else
		echo 'Tabelle campus.tbl_newssprache hinzugefuegt!<BR>';
}

// ************** public.tbl_gruppe.orgform_kurzbz ************************
if (!@pg_query($conn,'SELECT orgform_kurzbz FROM public.tbl_gruppe LIMIT 1;'))
{
	$sql='	ALTER TABLE public.tbl_gruppe ADD COLUMN orgform_kurzbz varchar(3);
			ALTER TABLE public.tbl_lehrverband ADD COLUMN orgform_kurzbz varchar(3);
			ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN orgform_kurzbz varchar(3);
			ALTER TABLE lehre.tbl_lehrveranstaltung ADD Constraint "orgform_lehrveranstaltung" foreign key ("orgform_kurzbz") references bis.tbl_orgform ("orgform_kurzbz") on update cascade on delete restrict;
			ALTER TABLE public.tbl_gruppe ADD Constraint "orgform_gruppe" foreign key ("orgform_kurzbz") references bis.tbl_orgform ("orgform_kurzbz") on update cascade on delete restrict;
			ALTER TABLE public.tbl_lehrverband ADD Constraint "orgform_lehrverband" foreign key ("orgform_kurzbz") references bis.tbl_orgform ("orgform_kurzbz") on update cascade on delete restrict;
		';
	if (!@pg_query($conn,$sql))
		echo '<strong>orgform_kurzbz: '.pg_last_error($conn).' </strong><BR>';
	else
		echo '	orgform_kurzbz wurde bei public.tbl_gruppe hinzugefuegt!<BR>
				orgform_kurzbz wurde bei public.tbl_lehrverband hinzugefuegt!<BR>
				orgform_kurzbz wurde bei lehre.tbl_lehrveranstaltung hinzugefuegt!<BR>';
}

// ************** public.tbl_firma.schule ************************
if (!@pg_query($conn,'SELECT schule FROM public.tbl_firma LIMIT 1;'))
{
	$sql='	ALTER TABLE public.tbl_firma ADD COLUMN schule boolean;
			UPDATE public.tbl_firma SET schule=FALSE;
			ALTER TABLE public.tbl_firma ALTER COLUMN schule SET NOT NULL;
			ALTER TABLE public.tbl_firma ALTER COLUMN schule SET DEFAULT FALSE;
		';
	if (!@pg_query($conn,$sql))
		echo '<strong>public.tbl_firma: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'schule wurde bei public.tbl_firma hinzugefuegt!<BR>';
}

// ************** public.tbl_studiengang.moodle ************************
if (!@pg_query($conn,'SELECT moodle FROM public.tbl_studiengang LIMIT 1;'))
{
	$sql='	ALTER TABLE public.tbl_studiengang ADD COLUMN moodle boolean;
			UPDATE public.tbl_studiengang SET moodle=TRUE;
			ALTER TABLE public.tbl_studiengang ALTER COLUMN moodle SET NOT NULL;
			ALTER TABLE public.tbl_studiengang ALTER COLUMN moodle SET DEFAULT TRUE;
		';
	if (!@pg_query($conn,$sql))
		echo '<strong>public.tbl_studiengang: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'moodle wurde bei public.tbl_studiengang hinzugefuegt!<BR>';
}

// ************** campus.tbl_news.datum_bis ************************
if (!@pg_query($conn,'SELECT datum_bis FROM campus.tbl_news LIMIT 1;'))
{
	$sql='	ALTER TABLE campus.tbl_news ADD COLUMN datum_bis date;';
	if (!@pg_query($conn,$sql))
		echo '<strong>campus.tbl_news: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'datum_bis wurde bei campus.tbl_news hinzugefuegt!<BR>';
}

// ************** public.tbl_ort.standort_kurzbz,telefonklappe ************************
if (!@pg_query($conn,'SELECT standort_kurzbz,telefonklappe FROM public.tbl_ort LIMIT 1;'))
{
	$sql='	ALTER TABLE public.tbl_ort ADD COLUMN standort_kurzbz varchar(16);
			ALTER TABLE public.tbl_ort ADD COLUMN telefonklappe varchar(8);
			ALTER TABLE public.tbl_ort ADD Constraint "standort_ort" foreign key ("standort_kurzbz") references public.tbl_standort ("standort_kurzbz") on update cascade on delete restrict;';
	if (!@pg_query($conn,$sql))
		echo '<strong>public.tbl_ort: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'standort_kurzbz wurde bei public.tbl_ort hinzugefuegt!<BR>telefonklappe wurde bei public.tbl_ort hinzugefuegt!<BR>';
}

// ************** campus.tbl_zeitsperre.freigabeamum ************************
if (!@pg_query($conn,'SELECT freigabeamum FROM campus.tbl_zeitsperre LIMIT 1;'))
{
	$sql='	ALTER TABLE campus.tbl_zeitsperre ADD COLUMN freigabeamum Timestamp;
			ALTER TABLE campus.tbl_zeitsperre ADD COLUMN freigabevon varchar(16);';
	if (!@pg_query($conn,$sql))
		echo '<strong>campus.tbl_zeitsperre: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'freigabevon,freigabeamum wurde bei campus.tbl_zeitsperre hinzugefuegt!<BR>';
}

// ************** public.tbl_person.kompetenzen ************************
if (!@pg_query($conn,'SELECT kompetenzen FROM public.tbl_person LIMIT 1;'))
{
	$sql='	ALTER TABLE public.tbl_person ADD COLUMN kompetenzen text;';
	if (!@pg_query($conn,$sql))
		echo '<strong>public.tbl_person: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'kompetenzen wurde bei public.tbl_person hinzugefuegt!<BR>';
}

// ************** public.tbl_buchungstyp.standardbetrag ************************
if (!@pg_query($conn,'SELECT standardbetrag FROM public.tbl_buchungstyp LIMIT 1;'))
{
	$sql='	ALTER TABLE public.tbl_buchungstyp ADD COLUMN standardbetrag numeric(8,2);';
	if (!@pg_query($conn,$sql))
		echo '<strong>public.tbl_buchungstyp: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'standardbetrag wurde bei public.tbl_buchungstyp hinzugefuegt!<BR>';
}

// ************** public.tbl_buchungstyp.standardtext ************************
if (!@pg_query($conn,'SELECT standardtext FROM public.tbl_buchungstyp LIMIT 1;'))
{
	$sql='	ALTER TABLE public.tbl_buchungstyp ADD COLUMN standardtext varchar(256);';
	if (!@pg_query($conn,$sql))
		echo '<strong>public.tbl_buchungstyp: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'standardtext wurde bei public.tbl_buchungstyp hinzugefuegt!<BR>';
}

// ************** lehre.tbl_lehrveranstaltung.projektarbeit ************************
if (!@pg_query($conn,'SELECT projektarbeit FROM lehre.tbl_lehrveranstaltung LIMIT 1;'))
{
	$sql='	ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN projektarbeit boolean;
			UPDATE lehre.tbl_lehrveranstaltung SET projektarbeit=FALSE;
			ALTER TABLE lehre.tbl_lehrveranstaltung ALTER COLUMN projektarbeit SET DEFAULT TRUE;
			ALTER TABLE lehre.tbl_lehrveranstaltung ALTER COLUMN projektarbeit SET NOT NULL;';
	if (!@pg_query($conn,$sql))
		echo '<strong>lehre.tbl_lehrveranstaltung: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'projektarbeit wurde bei lehre.tbl_lehrveranstaltung hinzugefuegt!<BR>';
}

// ************** campus.vw_lehreinheit.lv_lehrform_kurzbz ************************
if (!@pg_query($conn,'SELECT lv_lehrform_kurzbz FROM campus.vw_lehreinheit LIMIT 1;'))
{
	$sql='	DROP VIEW campus.vw_lehreinheit;
			CREATE  OR REPLACE VIEW campus.vw_lehreinheit AS
				SELECT tbl_lehrveranstaltung.studiengang_kz AS lv_studiengang_kz, tbl_lehrveranstaltung.kurzbz AS lv_kurzbz, tbl_lehrveranstaltung.bezeichnung AS lv_bezeichnung,
					tbl_lehrveranstaltung.ects AS lv_ects, tbl_lehrveranstaltung.lehreverzeichnis AS lv_lehreverzeichnis, tbl_lehrveranstaltung.planfaktor AS lv_planfaktor,
					tbl_lehrveranstaltung.planlektoren AS lv_planlektoren, tbl_lehrveranstaltung.planpersonalkosten AS lv_planpersonalkosten,
					tbl_lehrveranstaltung.plankostenprolektor AS lv_plankostenprolektor, lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, tbl_lehreinheit.lehrform_kurzbz,
					stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, tbl_lehreinheit.lehre, unr, lvnr, lehrfunktion_kurzbz, tbl_lehreinheit.insertamum,
					tbl_lehreinheit.insertvon, tbl_lehreinheit.updateamum, tbl_lehreinheit.updatevon, lehrfach_id, fachbereich_kurzbz, tbl_lehrfach.kurzbz AS lehrfach,
					tbl_lehrfach.bezeichnung AS lehrfach_bez, tbl_lehrfach.farbe, tbl_lehrveranstaltung.aktiv, tbl_lehrfach.sprache, mitarbeiter_uid,
					tbl_lehreinheitmitarbeiter.semesterstunden AS semesterstunden, tbl_lehrveranstaltung.semesterstunden AS lv_semesterstunden, planstunden, tbl_lehreinheitmitarbeiter.stundensatz,
					faktor, tbl_lehreinheit.anmerkung, tbl_mitarbeiter.kurzbz AS lektor, tbl_lehreinheitgruppe.studiengang_kz, tbl_lehreinheitgruppe.semester, verband, gruppe,
					gruppe_kurzbz, tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang, tbl_studiengang.bezeichnung AS stg_bez,
					tbl_studiengang.typ AS stg_typ, tbl_lehreinheitmitarbeiter.anmerkung AS anmerkunglektor, tbl_lehrveranstaltung.lehrform_kurzbz AS lv_lehrform_kurzbz
				FROM ((((((lehre.tbl_lehreinheit JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)) JOIN lehre.tbl_lehrfach USING (lehrfach_id))
					JOIN lehre.tbl_lehreinheitmitarbeiter USING (lehreinheit_id)) JOIN tbl_mitarbeiter USING (mitarbeiter_uid)) JOIN lehre.tbl_lehreinheitgruppe USING (lehreinheit_id))
					JOIN tbl_studiengang ON ((tbl_lehreinheitgruppe.studiengang_kz = tbl_studiengang.studiengang_kz)));
			GRANT SELECT ON TABLE campus.vw_lehreinheit TO GROUP web;
			GRANT SELECT ON TABLE campus.vw_lehreinheit TO GROUP admin;';
	if (!@pg_query($conn,$sql))
		echo '<strong>campus.vw_lehreinheit: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'lv_lehrform_kurzbz wurde bei campus.vw_lehreinheit hinzugefuegt!<BR>';
}

// ************** lehre.tbl_abschlusspruefung.note ************************
if (!@pg_query($conn,'SELECT note FROM lehre.tbl_abschlusspruefung LIMIT 1;'))
{
	$sql="	ALTER TABLE lehre.tbl_abschlusspruefung ADD COLUMN note smallint;
			Comment on column lehre.tbl_abschlusspruefung.note Is 'Note der komm. Pruefung';
			Alter table lehre.tbl_abschlusspruefung add Constraint note_abschlusspruefung foreign key (note)
				references lehre.tbl_note (note) on update cascade on delete restrict;";
	if (!@pg_query($conn,$sql))
		echo '<strong>lehre.tbl_abschlusspruefung: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'note wurde bei lehre.tbl_abschlusspruefung hinzugefuegt!<BR>';
}

// ************** bis.tbl_bisio.ort,uni,lehreinheit_id ************************
if (!@pg_query($conn,'SELECT ort,universitaet,lehreinheit_id FROM bis.tbl_bisio LIMIT 1;'))
{
	$sql='	ALTER TABLE bis.tbl_bisio ADD COLUMN ort varchar(128);
			ALTER TABLE bis.tbl_bisio ADD COLUMN universitaet varchar(256);
			ALTER TABLE bis.tbl_bisio ADD COLUMN lehreinheit_id integer;
			ALTER TABLE bis.tbl_bisio ADD CONSTRAINT lehreinheit_bisio FOREIGN KEY (lehreinheit_id)
				REFERENCES lehre.tbl_lehreinheit (lehreinheit_id) on update cascade on delete restrict;';
	if (!@pg_query($conn,$sql))
		echo '<strong>bis.tbl_bisio: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'ort,uni,lehreinheit_id wurde bei bis.tbl_bisio hinzugefuegt!<BR>';
}

 // ************** lehre.tbl_lehrveranstaltung.bezeichnung_english ************************
if (!@pg_query($conn,'SELECT bezeichnung_english FROM lehre.tbl_lehrveranstaltung LIMIT 1;'))
{
	$sql="	ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN bezeichnung_english varchar(256);
			UPDATE lehre.tbl_lehrveranstaltung SET bezeichnung_english=titel FROM campus.tbl_lvinfo
				WHERE tbl_lvinfo.sprache='English' AND tbl_lvinfo.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id;";
	if (!@pg_query($conn,$sql))
		echo '<strong>lehre.tbl_lehrveranstaltung: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'bezeichnung_english wurde bei lehre.tbl_lehrveranstaltung hinzugefuegt!<BR>';
}

// ************** lehre.tbl_projektarbeit.titel_english ************************
if (!@pg_query($conn,'SELECT titel_english FROM lehre.tbl_projektarbeit LIMIT 1;'))
{
	$sql="	ALTER TABLE lehre.tbl_projektarbeit ADD COLUMN titel_english varchar(256);
			Comment on column lehre.tbl_projektarbeit.titel_english Is 'Englischer Titel';";
	if (!@pg_query($conn,$sql))
		echo '<strong>lehre.tbl_projektarbeit: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'titel_english wurde bei lehre.tbl_projektarbeit hinzugefuegt!<BR>';
}

// ************** public.tbl_studiengang.zusatzinfo_html ************************
if (!@pg_query($conn,'SELECT zusatzinfo_html FROM public.tbl_studiengang LIMIT 1;'))
{
	$sql="	ALTER TABLE public.tbl_studiengang ADD COLUMN zusatzinfo_html text;
			Comment on column public.tbl_studiengang.zusatzinfo_html Is 'Zusatzinfo fuers CIS in HTML';";
	if (!@pg_query($conn,$sql))
		echo '<strong>public.tbl_studiengang: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'zusatzinfo_html wurde bei public.tbl_studiengang hinzugefuegt!<BR>';
}

// ************** public.tbl_ort.stockwerk ************************
if (!@pg_query($conn,'SELECT stockwerk FROM public.tbl_ort LIMIT 1;'))
{
	$sql="	ALTER TABLE public.tbl_ort ADD COLUMN stockwerk smallint;";
	if (!@pg_query($conn,$sql))
		echo '<strong>public.tbl_ort: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'stockwerk wurde bei public.tbl_ort hinzugefuegt!<BR>';
}

// ************** campus.tbl_resturlaub.urlaubstageprojahr ************************
if (!@pg_query($conn,'SELECT urlaubstageprojahr FROM campus.tbl_resturlaub LIMIT 1;'))
{
	$sql='	ALTER TABLE campus.tbl_resturlaub ADD COLUMN urlaubstageprojahr smallint;
			ALTER TABLE campus.tbl_resturlaub ALTER COLUMN urlaubstageprojahr SET DEFAULT 25;
			UPDATE campus.tbl_resturlaub SET urlaubstageprojahr=25;
			ALTER TABLE campus.tbl_resturlaub ALTER COLUMN urlaubstageprojahr SET NOT NULL;
			ALTER TABLE campus.tbl_resturlaub ADD CONSTRAINT tbl_resturlaub_urlaubstageprojahr CHECK (urlaubstageprojahr>=0)';
	if (!@pg_query($conn,$sql))
		echo '<strong>campus.tbl_resturlaub: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'urlaubstageprojahr wurde bei campus.tbl_resturlaub hinzugefuegt!<BR>';
}

// ************** public.tbl_benutzer.updateaktivam,updateaktivvon ************************
if (!@pg_query($conn,'SELECT updateaktivam,updateaktivvon FROM public.tbl_benutzer LIMIT 1;'))
{
	$sql='	ALTER TABLE public.tbl_benutzer ADD COLUMN updateaktivam Date;
			ALTER TABLE public.tbl_benutzer ADD COLUMN updateaktivvon Varchar(16);';
	if (!@pg_query($conn,$sql))
		echo '<strong>public.tbl_benutzer: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'updateaktivam und updateaktivvon wurden bei public.tbl_benutzer hinzugefuegt!<BR>';
}

// ************** lehre.tbl_lehrveranstaltung.lehrform_kurzbz ************************
if (!@pg_query($conn,'SELECT lehrform_kurzbz FROM lehre.tbl_lehrveranstaltung LIMIT 1;'))
{
	$sql='	ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN lehrform_kurzbz varchar(8);
			Alter table lehre.tbl_lehrveranstaltung add Constraint "lehrform_lehrveranstaltung" foreign key ("lehrform_kurzbz") references lehre.tbl_lehrform ("lehrform_kurzbz") on update cascade on delete restrict;';
	if (!@pg_query($conn,$sql))
		echo '<strong>lehre.tbl_lehrveranstaltung: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'lehrform_kurzbz wurde bei lehre.tbl_lehrveranstaltung hinzugefuegt!<BR>';
}

// ************** bis.tbl_bundesland ************************
if (!@pg_query($conn,'SELECT * FROM bis.tbl_bundesland LIMIT 1;'))
{
	$sql='	CREATE TABLE bis.tbl_bundesland
			(
				bundesland_code Smallint NOT NULL,
				kurzbz Varchar(8) UNIQUE,
				bezeichnung Varchar(64),
				constraint "pk_tbl_bundesland" primary key ("bundesland_code")
			);
			ALTER TABLE public.tbl_person ADD COLUMN bundesland_code smallint;
			ALTER TABLE public.tbl_person add Constraint "bundesland_person" foreign key ("bundesland_code") references "bis"."tbl_bundesland" ("bundesland_code") on update cascade on delete restrict;
			GRANT select on bis.tbl_bundesland to group "admin";
			GRANT update on bis.tbl_bundesland to group "admin";
			GRANT delete on bis.tbl_bundesland to group "admin";
			GRANT insert on bis.tbl_bundesland to group "admin";
			GRANT select on bis.tbl_bundesland to group "web";';
	if (!@pg_query($conn,$sql))
		echo '<strong>bis.tbl_bundesland: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'Tabelle bis.tbl_bundesland wurde hinzugefuegt!<BR>';
}

// ************** public.tbl_preinteressent ************************
if (!@pg_query($conn,'SELECT * FROM public.tbl_preinteressent LIMIT 1;'))
{
	$sql='	Create table tbl_preinteressent
		(
			preinteressent_id Serial NOT NULL,
			person_id integer NOT NULL,
			studiensemester_kurzbz Varchar(16) NOT NULL,
			aufmerksamdurch_kurzbz Varchar(16) NOT NULL,
			firma_id integer NOT NULL,
			erfassungsdatum Date,
			einverstaendnis Boolean,
			absagedatum Timestamp,
			anmerkung Text,
			insertamum Timestamp,
			insertvon Varchar(16),
			updateamum Timestamp,
			updatevon Varchar(16),
			maturajahr Numeric(4,0),
			infozusendung Date,
		constraint pk_tbl_preinteressent primary key (preinteressent_id)
		);
		Create table tbl_preinteressentstudiengang
		(
			studiengang_kz integer NOT NULL,
			preinteressent_id integer NOT NULL,
			prioritaet Smallint,
			freigabedatum Timestamp,
			uebernahmedatum Timestamp,
			insertamum Timestamp,
			insertvon Varchar(16),
			updateamum Timestamp,
			updatevon Varchar(16),
		constraint pk_tbl_preinteressentstudiengang primary key (studiengang_kz,preinteressent_id)
		);
		Comment on column "tbl_preinteressent"."firma_id" Is \'Schule\';
		Comment on column "tbl_preinteressent"."einverstaendnis" Is \'Einverstaendniserklaerung\';
		Comment on column "tbl_preinteressentstudiengang"."prioritaet" Is \'1 .. normal, 2. .. mittel, 3 ...\';
		Alter table "tbl_preinteressentstudiengang" add Constraint "studiengang_preinteressentstudiengang" foreign key ("studiengang_kz") references "public"."tbl_studiengang" ("studiengang_kz") on update cascade on delete restrict;
		Alter table "tbl_preinteressent" add Constraint "studiensemester_preinteressent" foreign key ("studiensemester_kurzbz") references "public"."tbl_studiensemester" ("studiensemester_kurzbz") on update cascade on delete restrict;
		Alter table "tbl_preinteressent" add Constraint "person_preinteressent" foreign key ("person_id") references "public"."tbl_person" ("person_id") on update cascade on delete restrict;
		Alter table "tbl_preinteressent" add Constraint "firma_preinteressent" foreign key ("firma_id") references "public"."tbl_firma" ("firma_id") on update cascade on delete restrict;
		Alter table "tbl_preinteressent" add Constraint "aufmerksamdurch_preinteressent" foreign key ("aufmerksamdurch_kurzbz") references "public"."tbl_aufmerksamdurch" ("aufmerksamdurch_kurzbz") on update cascade on delete restrict;
		Alter table "tbl_preinteressentstudiengang" add Constraint "preinteressent_preinteressentstudiengang" foreign key ("preinteressent_id") references "tbl_preinteressent" ("preinteressent_id") on update cascade on delete restrict;
		Grant select on "tbl_preinteressent" to group "admin";
		Grant update on "tbl_preinteressent" to group "admin";
		Grant delete on "tbl_preinteressent" to group "admin";
		Grant insert on "tbl_preinteressent" to group "admin";
		Grant select on "tbl_preinteressentstudiengang" to group "admin";
		Grant update on "tbl_preinteressentstudiengang" to group "admin";
		Grant delete on "tbl_preinteressentstudiengang" to group "admin";
		Grant insert on "tbl_preinteressentstudiengang" to group "admin";
		Grant all on public.tbl_preinteressent_preinteressent_id_seq to group "admin";';

	if (!@pg_query($conn,$sql))
		echo '<strong>public.tbl_preinteressent: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'Tabelle public.tbl_preinteressent wurde hinzugefuegt!<BR>';
}


// ************** lehre.vw_stundenplandev_student_unr ************************
if (!@pg_query($conn,'SELECT * FROM lehre.vw_stundenplandev_student_unr WHERE unr=0 LIMIT 1;'))
{
	$sql="	CREATE OR REPLACE VIEW lehre.vw_stundenplandev_student_unr AS
				SELECT unr, datum, stunde, student_uid
				FROM
				(
					SELECT stpl.unr, stpl.datum, stpl.stunde, uid AS student_uid
					FROM lehre.tbl_stundenplandev stpl JOIN tbl_benutzergruppe USING (gruppe_kurzbz)
					WHERE studiensemester_kurzbz=(SELECT studiensemester_kurzbz FROM tbl_studiensemester WHERE stpl.datum<=ende AND stpl.datum>=start)
					GROUP BY stpl.unr, stpl.datum, stpl.stunde, uid
					UNION
					SELECT stpl.unr, stpl.datum, stpl.stunde, student_uid
					FROM lehre.tbl_stundenplandev stpl JOIN tbl_studentlehrverband
						ON 	(stpl.gruppe_kurzbz IS NULL AND stpl.studiengang_kz=tbl_studentlehrverband.studiengang_kz AND stpl.semester=tbl_studentlehrverband.semester
							AND (stpl.verband=tbl_studentlehrverband.verband OR (stpl.verband=' ' AND stpl.verband!=tbl_studentlehrverband.verband))
							AND (stpl.gruppe=tbl_studentlehrverband.gruppe OR (stpl.gruppe=' ' AND stpl.gruppe!=tbl_studentlehrverband.gruppe))
							)
					WHERE studiensemester_kurzbz=(SELECT studiensemester_kurzbz FROM tbl_studiensemester WHERE stpl.datum<=ende AND stpl.datum>=start)
					GROUP BY stpl.unr,stpl.datum,stpl.stunde,student_uid
				) AS sub_stpl_uid
				GROUP BY unr, datum, stunde, student_uid;
			GRANT select on lehre.vw_stundenplandev_student_unr to group admin;
			GRANT select on lehre.vw_stundenplandev_student_unr to group web;";
	if (!@pg_query($conn,$sql))
		echo '<strong>lehre.vw_stundenplandev_student_unr: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'VIEW lehre.vw_stundenplandev_student_unr wurde hinzugefuegt!<BR>';
}

// ************** Leserechte auf bis.tbl_orgform fuer web user *****************
if($result =pg_query($conn, "SELECT has_table_privilege('web','bis.tbl_orgform','select') as bool"))
{
	if($row = pg_fetch_object($result))
	{
		if($row->bool=='f')
		{
			$sql="GRANT SELECT ON bis.tbl_orgform TO GROUP web;";
			if (!@pg_query($conn,$sql))
				echo '<strong>bis.tbl_orgform Rechte: '.pg_last_error($conn).' </strong><BR>';
			else
				echo 'User web erhaelt Leserechte auf die Tabelle bis.tbl_orgform<BR>';
		}
	}
}

// ********************** Pruefungen
echo '<H2>Pruefe Tabellen und Attribute!</H2>';

$tabellen=array(
	"bis.tbl_ausbildung"  => array("ausbildungcode","ausbildungbez","ausbildungbeschreibung"),
	"bis.tbl_berufstaetigkeit"  => array("berufstaetigkeit_code","berufstaetigkeit_bez","berufstaetigkeit_kurzbz"),
	"bis.tbl_beschaeftigungsart1"  => array("ba1code","ba1bez","ba1kurzbz"),
	"bis.tbl_beschaeftigungsart2"  => array("ba2code","ba2bez"),
	"bis.tbl_beschaeftigungsausmass"  => array("beschausmasscode","beschausmassbez","min","max"),
	"bis.tbl_besqual"  => array("besqualcode","besqualbez"),
	"bis.tbl_bisfunktion"  => array("bisverwendung_id","studiengang_kz","sws","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"bis.tbl_bisio"  => array("bisio_id","mobilitaetsprogramm_code","nation_code","von","bis","zweck_code","student_uid","updateamum","updatevon","insertamum","insertvon","ext_id","ort","universitaet","lehreinheit_id"),
	"bis.tbl_bisverwendung"  => array("bisverwendung_id","ba1code","ba2code","vertragsstunden","beschausmasscode","verwendung_code","mitarbeiter_uid","hauptberufcode","hauptberuflich","habilitation","beginn","ende","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"bis.tbl_bundesland"  => array("bundesland_code","kurzbz","bezeichnung"),
	"bis.tbl_entwicklungsteam"  => array("mitarbeiter_uid","studiengang_kz","besqualcode","beginn","ende","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"bis.tbl_gemeinde"  => array("gemeinde_id","plz","name","ortschaftskennziffer","ortschaftsname","bulacode","bulabez","kennziffer"),
	"bis.tbl_hauptberuf"  => array("hauptberufcode","bezeichnung"),
	"bis.tbl_mobilitaetsprogramm"  => array("mobilitaetsprogramm_code","kurzbz","beschreibung"),
	"bis.tbl_nation"  => array("nation_code","entwicklungsstand","eu","ewr","kontinent","kurztext","langtext","engltext","sperre"),
	"bis.tbl_orgform"  => array("orgform_kurzbz","code","bezeichnung"),
	"bis.tbl_verwendung"  => array("verwendung_code","verwendungbez"),
	"bis.tbl_zgv"  => array("zgv_code","zgv_bez","zgv_kurzbz"),
	"bis.tbl_zgvmaster"  => array("zgvmas_code","zgvmas_bez","zgvmas_kurzbz"),
	"bis.tbl_zweck"  => array("zweck_code","kurzbz","bezeichnung"),
	"campus.tbl_abgabe"  => array("abgabe_id","abgabedatei","abgabezeit","anmerkung"),
	"campus.tbl_beispiel"  => array("beispiel_id","uebung_id","nummer","bezeichnung","punkte","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_benutzerlvstudiensemester"  => array("uid","studiensemester_kurzbz","lehrveranstaltung_id"),
	"campus.tbl_bmreservierung"  => array("bmreservierung_id","betriebsmittel_id","person_id","uid","datum","stunde","titel","beschreibung","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_erreichbarkeit"  => array("erreichbarkeit_kurzbz","beschreibung","farbe"),
	"campus.tbl_feedback"  => array("feedback_id","betreff","text","datum","uid","lehrveranstaltung_id","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_legesamtnote"  => array("student_uid","lehreinheit_id","note","benotungsdatum","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_lvgesamtnote"  => array("lehrveranstaltung_id","studiensemester_kurzbz","student_uid","note","mitarbeiter_uid","benotungsdatum","freigabedatum","freigabevon_uid","bemerkung","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_lvinfo"  => array("lehrveranstaltung_id","sprache","titel","lehrziele","lehrinhalte","methodik","voraussetzungen","unterlagen","pruefungsordnung","anmerkung","kurzbeschreibung","genehmigt","aktiv","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_news"  => array("news_id","uid","studiengang_kz","fachbereich_kurzbz","semester","betreff","text","datum","verfasser","updateamum","updatevon","insertamum","insertvon","datum_bis"),
	"campus.tbl_newssprache"  => array("sprache","news_id","betreff","text","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_notenschluessel"  => array("lehreinheit_id","note","punkte"),
	"campus.tbl_notenschluesseluebung"  => array("uebung_id","note","punkte"),
	"campus.tbl_reservierung"  => array("reservierung_id","ort_kurzbz","studiengang_kz","uid","stunde","datum","titel","beschreibung","semester","verband","gruppe","gruppe_kurzbz"),
	"campus.tbl_resturlaub"  => array("mitarbeiter_uid","resturlaubstage","mehrarbeitsstunden","updateamum","updatevon","insertamum","insertvon","urlaubstageprojahr"),
	"campus.tbl_studentbeispiel"  => array("student_uid","beispiel_id","vorbereitet","probleme","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_studentuebung"  => array("student_uid","mitarbeiter_uid","abgabe_id","uebung_id","note","mitarbeitspunkte","punkte","anmerkung","benotungsdatum","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_uebung"  => array("uebung_id","gewicht","punkte","angabedatei","freigabevon","freigabebis","abgabe","beispiele","statistik","bezeichnung","positiv","defaultbemerkung","lehreinheit_id","maxstd","maxbsp","liste_id","prozent","nummer","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_zeitaufzeichnung"  => array("zeitaufzeichnung_id","uid","aktivitaet_kurzbz","projekt_kurzbz","start","ende","beschreibung","studiengang_kz","fachbereich_kurzbz","insertamum","insertvon","updateamum","updatevon"),
	"campus.tbl_zeitsperre"  => array("zeitsperre_id","zeitsperretyp_kurzbz","mitarbeiter_uid","bezeichnung","vondatum","vonstunde","bisdatum","bisstunde","vertretung_uid","updateamum","updatevon","insertamum","insertvon","erreichbarkeit_kurzbz","freigabeamum","freigabevon"),
	"campus.tbl_zeitsperretyp"  => array("zeitsperretyp_kurzbz","beschreibung","farbe"),
	"campus.tbl_zeitwunsch"  => array("stunde","mitarbeiter_uid","tag","gewicht","updateamum","updatevon","insertamum","insertvon"),
	"fue.tbl_aktivitaet"  => array("aktivitaet_kurzbz","beschreibung"),
	"fue.tbl_projekt"  => array("projekt_kurzbz","nummer","titel","beschreibung","beginn","ende"),
	"fue.tbl_projektbenutzer"  => array("projektbenutzer_id","uid","funktion_kurzbz","projekt_kurzbz"),
	"kommune.tbl_match"  => array("match_id","team_sieger","wettbewerb_kurzbz","team_gefordert","team_forderer","gefordertvon","gefordertamum","matchdatumzeit","matchort","matchbestaetigtvon","matchbestaetigtamum","ergebniss","bestaetigtvon","bestaetigtamum"),
	"kommune.tbl_team"  => array("team_kurzbz","bezeichnung","beschreibung","logo"),
	"kommune.tbl_teambenutzer"  => array("uid","team_kurzbz"),
	"kommune.tbl_wettbewerb"  => array("wettbewerb_kurzbz","regeln","forderungstage","einzel","wbtyp_kurzbz","uid","icon"),
	"kommune.tbl_wettbewerbteam"  => array("team_kurzbz","wettbewerb_kurzbz","rang","punkte"),
	"kommune.tbl_wettbewerbtyp"  => array("wbtyp_kurzbz","bezeichnung","farbe"),
	"lehre.tbl_abschlussbeurteilung"  => array("abschlussbeurteilung_kurzbz","bezeichnung"),
	"lehre.tbl_abschlusspruefung"  => array("abschlusspruefung_id","student_uid","vorsitz","pruefer1","pruefer2","pruefer3","abschlussbeurteilung_kurzbz","akadgrad_id","pruefungstyp_kurzbz","datum","sponsion","anmerkung","updateamum","updatevon","insertamum","insertvon","ext_id","note"),
	"lehre.tbl_akadgrad"  => array("akadgrad_id","akadgrad_kurzbz","studiengang_kz","titel","geschlecht"),
	"lehre.tbl_betreuerart"  => array("betreuerart_kurzbz","beschreibung"),
	"lehre.tbl_ferien"  => array("bezeichnung","studiengang_kz","vondatum","bisdatum"),
	"lehre.tbl_lehreinheit"  => array("lehreinheit_id","lehrveranstaltung_id","studiensemester_kurzbz","lehrfach_id","lehrform_kurzbz","stundenblockung","wochenrythmus","start_kw","raumtyp","raumtypalternativ","sprache","lehre","anmerkung","unr","lvnr","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_lehreinheitgruppe"  => array("lehreinheitgruppe_id","lehreinheit_id","studiengang_kz","semester","verband","gruppe","gruppe_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_lehreinheitmitarbeiter"  => array("lehreinheit_id","mitarbeiter_uid","lehrfunktion_kurzbz","semesterstunden","planstunden","stundensatz","faktor","anmerkung","bismelden","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_lehrfach"  => array("lehrfach_id","studiengang_kz","fachbereich_kurzbz","kurzbz","bezeichnung","farbe","aktiv","semester","sprache","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_lehrform"  => array("lehrform_kurzbz","bezeichnung","verplanen"),
	"lehre.tbl_lehrfunktion"  => array("lehrfunktion_kurzbz","beschreibung","standardfaktor"),
	"lehre.tbl_lehrveranstaltung"  => array("lehrveranstaltung_id","kurzbz","bezeichnung","lehrform_kurzbz","studiengang_kz","semester","sprache","ects","semesterstunden","anmerkung","lehre","lehreverzeichnis","aktiv","planfaktor","planlektoren","planpersonalkosten","plankostenprolektor","koordinator","sort","zeugnis","projektarbeit","updateamum","updatevon","insertamum","insertvon","ext_id","bezeichnung_english","orgform_kurzbz"),
	"lehre.tbl_moodle"  => array("lehrveranstaltung_id","lehreinheit_id","moodle_id","mdl_course_id","studiensemester_kurzbz","gruppen","insertamum","insertvon"),
	"lehre.tbl_note"  => array("note","bezeichnung","anmerkung","farbe"),
	"lehre.tbl_projektarbeit"  => array("projektarbeit_id","projekttyp_kurzbz","titel","lehreinheit_id","student_uid","firma_id","note","punkte","beginn","ende","faktor","freigegeben","gesperrtbis","stundensatz","gesamtstunden","themenbereich","anmerkung","updateamum","updatevon","insertamum","insertvon","ext_id","titel_english"),
	"lehre.tbl_projektbetreuer"  => array("person_id","projektarbeit_id","betreuerart_kurzbz","note","faktor","name","punkte","stunden","stundensatz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_projekttyp"  => array("projekttyp_kurzbz","bezeichnung"),
	"lehre.tbl_pruefung"  => array("pruefung_id","lehreinheit_id","student_uid","mitarbeiter_uid","note","pruefungstyp_kurzbz","datum","anmerkung","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"lehre.tbl_pruefungstyp"  => array("pruefungstyp_kurzbz","beschreibung","abschluss"),
	"lehre.tbl_stunde"  => array("stunde","beginn","ende"),
	"lehre.tbl_stundenplan"  => array("stundenplan_id","unr","mitarbeiter_uid","datum","stunde","ort_kurzbz","gruppe_kurzbz","titel","anmerkung","lehreinheit_id","studiengang_kz","semester","verband","gruppe","fix","updateamum","updatevon","insertamum","insertvon"),
	"lehre.tbl_stundenplandev"  => array("stundenplandev_id","lehreinheit_id","unr","studiengang_kz","semester","verband","gruppe","gruppe_kurzbz","mitarbeiter_uid","ort_kurzbz","datum","stunde","titel","anmerkung","fix","updateamum","updatevon","insertamum","insertvon"),
	"lehre.tbl_zeitfenster"  => array("wochentag","stunde","ort_kurzbz","studiengang_kz","gewicht"),
	"lehre.tbl_zeugnis"  => array("zeugnis_id","student_uid","zeugnis","erstelltam","gedruckt","titel","bezeichnung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_zeugnisnote"  => array("lehrveranstaltung_id","student_uid","studiensemester_kurzbz","note","uebernahmedatum","benotungsdatum","bemerkung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_adresse"  => array("adresse_id","person_id","name","strasse","plz","ort","gemeinde","nation","typ","heimatadresse","zustelladresse","firma_id","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_akte"  => array("akte_id","person_id","dokument_kurzbz","uid","inhalt","mimetype","erstelltam","gedruckt","titel","bezeichnung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_aufmerksamdurch"  => array("aufmerksamdurch_kurzbz","beschreibung","ext_id"),
	"public.tbl_aufnahmeschluessel"  => array("aufnahmeschluessel"),
	"public.tbl_bankverbindung"  => array("bankverbindung_id","person_id","name","anschrift","bic","blz","iban","kontonr","typ","verrechnung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_benutzer"  => array("uid","person_id","aktiv","alias","insertamum","insertvon","updateamum","updatevon","ext_id","updateaktivvon","updateaktivam"),
	"public.tbl_benutzerberechtigung"  => array("benutzerberechtigung_id","art","fachbereich_kurzbz","studiengang_kz","berechtigung_kurzbz","uid","studiensemester_kurzbz","start","ende","updateamum","updatevon","insertamum","insertvon"),
	"public.tbl_benutzerfunktion"  => array("benutzerfunktion_id","fachbereich_kurzbz","uid","studiengang_kz","funktion_kurzbz","semester","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_benutzergruppe"  => array("uid","gruppe_kurzbz","studiensemester_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_berechtigung"  => array("berechtigung_kurzbz","beschreibung"),
	"public.tbl_betriebsmittel"  => array("betriebsmittel_id","beschreibung","betriebsmitteltyp","nummer","nummerintern","reservieren","ort_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_betriebsmittelperson"  => array("betriebsmittel_id","person_id","anmerkung","kaution","ausgegebenam","retouram","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_betriebsmitteltyp"  => array("betriebsmitteltyp","beschreibung","anzahl","kaution"),
	"public.tbl_buchungstyp"  => array("buchungstyp_kurzbz","beschreibung","standardbetrag","standardtext"),
	"public.tbl_dokument"  => array("dokument_kurzbz","bezeichnung","ext_id"),
	"public.tbl_dokumentprestudent"  => array("dokument_kurzbz","prestudent_id","mitarbeiter_uid","datum","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_dokumentstudiengang"  => array("dokument_kurzbz","studiengang_kz","ext_id"),
	"public.tbl_erhalter"  => array("erhalter_kz","kurzbz","bezeichnung","dvr","logo","zvr"),
	"public.tbl_fachbereich"  => array("fachbereich_kurzbz","bezeichnung","farbe","studiengang_kz","aktiv","ext_id"),
	"public.tbl_firma"  => array("firma_id","name","adresse","email","telefon","fax","anmerkung","firmentyp_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id","schule"),
	"public.tbl_firmentyp"  => array("firmentyp_kurzbz","beschreibung"),
	"public.tbl_funktion"  => array("funktion_kurzbz","beschreibung","aktiv"),
	"public.tbl_gruppe"  => array("gruppe_kurzbz","studiengang_kz","semester","bezeichnung","beschreibung","sichtbar","lehre","aktiv","sort","mailgrp","generiert","updateamum","updatevon","insertamum","insertvon","ext_id","orgform_kurzbz"),
	"public.tbl_kontakt"  => array("kontakt_id","person_id","firma_id","kontakttyp","anmerkung","kontakt","zustellung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_kontaktmedium"  => array("kontaktmedium_kurzbz","beschreibung"),
	"public.tbl_kontakttyp"  => array("kontakttyp","beschreibung"),
	"public.tbl_konto"  => array("buchungsnr","person_id","studiengang_kz","studiensemester_kurzbz","buchungstyp_kurzbz","buchungsnr_verweis","betrag","buchungsdatum","buchungstext","mahnspanne","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_lehrverband"  => array("studiengang_kz","semester","verband","gruppe","aktiv","bezeichnung","ext_id","orgform_kurzbz"),
	"public.tbl_log"  => array("log_id","executetime","mitarbeiter_uid","beschreibung","sql","sqlundo"),
	"public.tbl_mitarbeiter"  => array("mitarbeiter_uid","personalnummer","telefonklappe","kurzbz","lektor","fixangestellt","bismelden","stundensatz","ausbildungcode","ort_kurzbz","standort_kurzbz","anmerkung","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_ort"  => array("ort_kurzbz","bezeichnung","planbezeichnung","max_person","lehre","reservieren","aktiv","lageplan","dislozierung","kosten","ausstattung","updateamum","updatevon","insertamum","insertvon","ext_id","stockwerk","standort_kurzbz","telefonklappe"),
	"public.tbl_ortraumtyp"  => array("ort_kurzbz","hierarchie","raumtyp_kurzbz"),
	"public.tbl_person"  => array("person_id","staatsbuergerschaft","geburtsnation","sprache","anrede","titelpost","titelpre","nachname","vorname","vornamen","gebdatum","gebort","gebzeit","foto","anmerkung","homepage","svnr","ersatzkennzeichen","familienstand","geschlecht","anzahlkinder","aktiv","insertamum","insertvon","updateamum","updatevon","ext_id","bundesland_code","kompetenzen","kurzbeschreibung"),
	"public.tbl_personfunktionfirma"  => array("personfunktionfirma_id","funktion_kurzbz","person_id","firma_id","position","anrede"),
	"public.tbl_preinteressent"  => array("preinteressent_id","person_id","studiensemester_kurzbz","firma_id","erfassungsdatum","einverstaendnis","absagedatum","anmerkung","maturajahr","infozusendung","aufmerksamdurch_kurzbz","kontaktmedium_kurzbz","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_preinteressentstudiengang"  => array("studiengang_kz","preinteressent_id","freigabedatum","uebernahmedatum","prioritaet","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_prestudent"  => array("prestudent_id","aufmerksamdurch_kurzbz","person_id","studiengang_kz","berufstaetigkeit_code","ausbildungcode","zgv_code","zgvort","zgvdatum","zgvmas_code","zgvmaort","zgvmadatum","aufnahmeschluessel","facheinschlberuf","reihungstest_id","anmeldungreihungstest","reihungstestangetreten","punkte","bismelden","anmerkung","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_prestudentrolle"  => array("prestudent_id","rolle_kurzbz","studiensemester_kurzbz","ausbildungssemester","datum","orgform_kurzbz","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_raumtyp"  => array("raumtyp_kurzbz","beschreibung"),
	"public.tbl_reihungstest"  => array("reihungstest_id","studiengang_kz","ort_kurzbz","anmerkung","datum","uhrzeit","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_rolle"  => array("rolle_kurzbz","beschreibung","anmerkung","ext_id"),
	"public.tbl_semesterwochen"  => array("semester","studiengang_kz","wochen"),
	"public.tbl_sprache"  => array("sprache"),
	"public.tbl_standort"  => array("standort_kurzbz","adresse_id"),
	"public.tbl_student"  => array("student_uid","matrikelnr","prestudent_id","studiengang_kz","semester","verband","gruppe","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_studentlehrverband"  => array("student_uid","studiensemester_kurzbz","studiengang_kz","semester","verband","gruppe","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_studiengang"  => array("studiengang_kz","kurzbz","kurzbzlang","typ","bezeichnung","english","farbe","email","telefon","max_semester","max_verband","max_gruppe","erhalter_kz","bescheid","bescheidbgbl1","bescheidbgbl2","bescheidgz","bescheidvom","orgform_kurzbz","titelbescheidvom","aktiv","ext_id","zusatzinfo_html","organisationsform","moodle"),
	"public.tbl_studiensemester"  => array("studiensemester_kurzbz","bezeichnung","start","ende","ext_id"),
	"public.tbl_variable"  => array("name","uid","wert"),
	"public.tbl_vorlage"  => array("vorlage_kurzbz","bezeichnung","anmerkung"),
	"public.tbl_vorlagestudiengang"  => array("vorlage_kurzbz","studiengang_kz","version","text"),
	"sync.tbl_zutrittskarte"  => array("key","name","firstname","groupe","logaswnumber","physaswnumber","validstart","validend","text1","text2","text3","text4","text5","text6","pin"),
	"testtool.tbl_ablauf"  => array("gebiet_id","studiengang_kz","reihung","gewicht"),
	"testtool.tbl_antwort"  => array("antwort_id","frage_id","pruefling_id","antwort","begintime","endtime"),
	"testtool.tbl_frage"  => array("frage_id","kategorie_kurzbz","gebiet_id","gruppe_kurzbz","loesung","nummer","demo","text","bild"),
	"testtool.tbl_gebiet"  => array("gebiet_id","kurzbz","bezeichnung","beschreibung","zeit","abzug","kategorien"),
	"testtool.tbl_gruppe"  => array("gruppe_kurzbz"),
	"testtool.tbl_kategorie"  => array("kategorie_kurzbz","gebiet_id"),
	"testtool.tbl_kriterien"  => array("gebiet_id","kategorie_kurzbz","punkte","typ"),
	"testtool.tbl_pruefling"  => array("pruefling_id","prestudent_id","studiengang_kz","idnachweis","registriert","gruppe_kurzbz"),
	"testtool.tbl_vorschlag"  => array("vorschlag_id","frage_id","nummer","antwort","text","bild")
);

$tabs=array_keys($tabellen);
//print_r($tabs);
$i=0;
foreach ($tabellen AS $attribute)
{
	$sql_attr='';
	foreach($attribute AS $attr)
		$sql_attr.=$attr.',';
	$sql_attr=substr($sql_attr, 0, -1);

	if (!@pg_query($conn,'SELECT '.$sql_attr.' FROM '.$tabs[$i].' LIMIT 1;'))
		echo '<BR><strong>'.$tabs[$i].': '.pg_last_error($conn).' </strong><BR>';
	else
		echo $tabs[$i].': OK - ';
	flush();
	$i++;
}

echo '<H2>Gegenpruefung!</H2>';
$sql_query="SELECT schemaname,tablename FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema' AND schemaname != 'sync';";
if (!$result=@pg_query($conn,$sql_query))
		echo '<BR><strong>'.pg_last_error($conn).' </strong><BR>';
	else
		while ($row=pg_fetch_object($result))
		{
			$fulltablename=$row->schemaname.'.'.$row->tablename;
			if (!isset($tabellen[$fulltablename]))
				echo 'Tabelle '.$fulltablename.' existiert in der DB, aber nicht in diesem Skript!<BR>';
			else
				if (!$result_fields=@pg_query($conn,"SELECT * FROM $fulltablename LIMIT 1;"))
					echo '<BR><strong>'.pg_last_error($conn).' </strong><BR>';
				else
					for ($i=0; $i<pg_num_fields($result_fields); $i++)
					{
						$found=false;
						$fieldnameDB=pg_field_name($result_fields,$i);
						foreach ($tabellen[$fulltablename] AS $fieldnameARRAY)
							if ($fieldnameDB==$fieldnameARRAY)
							{
								$found=true;
								break;
							}
						if (!$found)
							echo 'Attribut '.$fulltablename.'.<strong>'.$fieldnameDB.'</strong> existiert in der DB, aber nicht in diesem Skript!<BR>';
					}
		}
?>
