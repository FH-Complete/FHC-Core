<?php
/* Copyright (C) 2017 fhcomplete.org
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
 * Authors: Harald Bamberger <harald.bamberger@technikum-wien.at>,
 *
 * Beschreibung:
 * Dashboard DB Aenderungen
 */
if (! defined('DB_NAME')) exit('No direct script access allowed');

if (($result = $db->db_query("SELECT schema_name FROM information_schema.schemata WHERE schema_name='dashboard'")))
{
	if ($db->db_num_rows($result) == 0)
	{// TODO(chris): Rechte "Web"?
		$qry = <<<EODASHBOARDSQL
		
--
-- Name: dashboard; Type: SCHEMA; Schema: -; Owner: fhcomplete
--

CREATE SCHEMA dashboard;


ALTER SCHEMA dashboard OWNER TO fhcomplete;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: tbl_dashboard; Type: TABLE; Schema: dashboard; Owner: fhcomplete
--

CREATE TABLE dashboard.tbl_dashboard (
    dashboard_id integer NOT NULL,
    dashboard_kurzbz character varying(32) NOT NULL,
    beschreibung text
);


ALTER TABLE dashboard.tbl_dashboard OWNER TO fhcomplete;

--
-- Name: tbl_dashboard_benutzer_override; Type: TABLE; Schema: dashboard; Owner: fhcomplete
--

CREATE TABLE dashboard.tbl_dashboard_benutzer_override (
    override_id integer NOT NULL,
    dashboard_id integer NOT NULL,
    uid character varying(32) NOT NULL,
    override jsonb NOT NULL
);


ALTER TABLE dashboard.tbl_dashboard_benutzer_override OWNER TO fhcomplete;

--
-- Name: tbl_dashboard_benutzer_override_override_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: fhcomplete
--

CREATE SEQUENCE dashboard.tbl_dashboard_benutzer_override_override_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dashboard.tbl_dashboard_benutzer_override_override_id_seq OWNER TO fhcomplete;

--
-- Name: tbl_dashboard_benutzer_override_override_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: fhcomplete
--

ALTER SEQUENCE dashboard.tbl_dashboard_benutzer_override_override_id_seq OWNED BY dashboard.tbl_dashboard_benutzer_override.override_id;


--
-- Name: tbl_dashboard_dashboard_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: fhcomplete
--

CREATE SEQUENCE dashboard.tbl_dashboard_dashboard_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dashboard.tbl_dashboard_dashboard_id_seq OWNER TO fhcomplete;

--
-- Name: tbl_dashboard_dashboard_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: fhcomplete
--

ALTER SEQUENCE dashboard.tbl_dashboard_dashboard_id_seq OWNED BY dashboard.tbl_dashboard.dashboard_id;


--
-- Name: tbl_dashboard_preset; Type: TABLE; Schema: dashboard; Owner: fhcomplete
--

CREATE TABLE dashboard.tbl_dashboard_preset (
    preset_id integer NOT NULL,
    dashboard_id integer NOT NULL,
    funktion_kurzbz character varying(16),
    preset jsonb NOT NULL
);


ALTER TABLE dashboard.tbl_dashboard_preset OWNER TO fhcomplete;

--
-- Name: tbl_dashboard_preset_preset_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: fhcomplete
--

CREATE SEQUENCE dashboard.tbl_dashboard_preset_preset_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dashboard.tbl_dashboard_preset_preset_id_seq OWNER TO fhcomplete;

--
-- Name: tbl_dashboard_preset_preset_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: fhcomplete
--

ALTER SEQUENCE dashboard.tbl_dashboard_preset_preset_id_seq OWNED BY dashboard.tbl_dashboard_preset.preset_id;


--
-- Name: tbl_dashboard_widget; Type: TABLE; Schema: dashboard; Owner: fhcomplete
--

CREATE TABLE dashboard.tbl_dashboard_widget (
    dashboard_id integer NOT NULL,
    widget_id integer NOT NULL
);


ALTER TABLE dashboard.tbl_dashboard_widget OWNER TO fhcomplete;

--
-- Name: tbl_widget; Type: TABLE; Schema: dashboard; Owner: fhcomplete
--

CREATE TABLE dashboard.tbl_widget (
    widget_id integer NOT NULL,
    widget_kurzbz character varying(32) NOT NULL,
    beschreibung text,
    arguments jsonb NOT NULL,
    setup jsonb NOT NULL
);


ALTER TABLE dashboard.tbl_widget OWNER TO fhcomplete;

--
-- Name: tbl_widget_widget_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: fhcomplete
--

CREATE SEQUENCE dashboard.tbl_widget_widget_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dashboard.tbl_widget_widget_id_seq OWNER TO fhcomplete;

--
-- Name: tbl_widget_widget_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: fhcomplete
--

ALTER SEQUENCE dashboard.tbl_widget_widget_id_seq OWNED BY dashboard.tbl_widget.widget_id;


--
-- Name: tbl_dashboard dashboard_id; Type: DEFAULT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard ALTER COLUMN dashboard_id SET DEFAULT nextval('dashboard.tbl_dashboard_dashboard_id_seq'::regclass);


--
-- Name: tbl_dashboard_benutzer_override override_id; Type: DEFAULT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard_benutzer_override ALTER COLUMN override_id SET DEFAULT nextval('dashboard.tbl_dashboard_benutzer_override_override_id_seq'::regclass);


--
-- Name: tbl_dashboard_preset preset_id; Type: DEFAULT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard_preset ALTER COLUMN preset_id SET DEFAULT nextval('dashboard.tbl_dashboard_preset_preset_id_seq'::regclass);


--
-- Name: tbl_widget widget_id; Type: DEFAULT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_widget ALTER COLUMN widget_id SET DEFAULT nextval('dashboard.tbl_widget_widget_id_seq'::regclass);


--
-- Name: tbl_dashboard_benutzer_override tbl_dashboard_benutzer_override_pkey; Type: CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard_benutzer_override
    ADD CONSTRAINT tbl_dashboard_benutzer_override_pkey PRIMARY KEY (override_id);


--
-- Name: tbl_dashboard_benutzer_override tbl_dashboard_benutzer_override_uk1; Type: CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard_benutzer_override
    ADD CONSTRAINT tbl_dashboard_benutzer_override_uk1 UNIQUE (dashboard_id, uid);


--
-- Name: tbl_dashboard tbl_dashboard_kurz_bz_key; Type: CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard
    ADD CONSTRAINT tbl_dashboard_kurz_bz_key UNIQUE (dashboard_kurzbz);


--
-- Name: tbl_dashboard tbl_dashboard_pkey; Type: CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard
    ADD CONSTRAINT tbl_dashboard_pkey PRIMARY KEY (dashboard_id);


--
-- Name: tbl_dashboard_preset tbl_dashboard_preset_pkey; Type: CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard_preset
    ADD CONSTRAINT tbl_dashboard_preset_pkey PRIMARY KEY (preset_id);


--
-- Name: tbl_dashboard_preset tbl_dashboard_preset_uk1; Type: CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard_preset
    ADD CONSTRAINT tbl_dashboard_preset_uk1 UNIQUE (dashboard_id, funktion_kurzbz);


--
-- Name: tbl_dashboard_widget tbl_dashboard_widget_pkey; Type: CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard_widget
    ADD CONSTRAINT tbl_dashboard_widget_pkey PRIMARY KEY (dashboard_id, widget_id);


--
-- Name: tbl_widget tbl_widget_pkey; Type: CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_widget
    ADD CONSTRAINT tbl_widget_pkey PRIMARY KEY (widget_id);


--
-- Name: tbl_widget tbl_widget_widget_kurzbz_key; Type: CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_widget
    ADD CONSTRAINT tbl_widget_widget_kurzbz_key UNIQUE (widget_kurzbz);


--
-- Name: tbl_dashboard_benutzer_override tbl_dashboard_benutzer_override_fk1; Type: FK CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard_benutzer_override
    ADD CONSTRAINT tbl_dashboard_benutzer_override_fk1 FOREIGN KEY (dashboard_id) REFERENCES dashboard.tbl_dashboard(dashboard_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tbl_dashboard_benutzer_override tbl_dashboard_benutzer_override_fk2; Type: FK CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard_benutzer_override
    ADD CONSTRAINT tbl_dashboard_benutzer_override_fk2 FOREIGN KEY (uid) REFERENCES public.tbl_benutzer(uid) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tbl_dashboard_preset tbl_dashboard_preset_fk1; Type: FK CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard_preset
    ADD CONSTRAINT tbl_dashboard_preset_fk1 FOREIGN KEY (dashboard_id) REFERENCES dashboard.tbl_dashboard(dashboard_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tbl_dashboard_preset tbl_dashboard_preset_fk2; Type: FK CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard_preset
    ADD CONSTRAINT tbl_dashboard_preset_fk2 FOREIGN KEY (funktion_kurzbz) REFERENCES public.tbl_funktion(funktion_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tbl_dashboard_widget tbl_dashboard_widget_fk1; Type: FK CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard_widget
    ADD CONSTRAINT tbl_dashboard_widget_fk1 FOREIGN KEY (dashboard_id) REFERENCES dashboard.tbl_dashboard(dashboard_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tbl_dashboard_widget tbl_dashboard_widget_fk2; Type: FK CONSTRAINT; Schema: dashboard; Owner: fhcomplete
--

ALTER TABLE ONLY dashboard.tbl_dashboard_widget
    ADD CONSTRAINT tbl_dashboard_widget_fk2 FOREIGN KEY (widget_id) REFERENCES dashboard.tbl_widget(widget_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: SCHEMA dashboard; Type: ACL; Schema: -; Owner: fhcomplete
--

GRANT USAGE ON SCHEMA dashboard TO web;
GRANT USAGE ON SCHEMA dashboard TO vilesci;


--
-- Name: TABLE tbl_dashboard; Type: ACL; Schema: dashboard; Owner: fhcomplete
--

GRANT SELECT ON TABLE dashboard.tbl_dashboard TO web;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE dashboard.tbl_dashboard TO vilesci;


--
-- Name: TABLE tbl_dashboard_benutzer_override; Type: ACL; Schema: dashboard; Owner: fhcomplete
--

GRANT SELECT,INSERT,UPDATE ON TABLE dashboard.tbl_dashboard_benutzer_override TO web;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE dashboard.tbl_dashboard_benutzer_override TO vilesci;


--
-- Name: SEQUENCE tbl_dashboard_benutzer_override_override_id_seq; Type: ACL; Schema: dashboard; Owner: fhcomplete
--

GRANT ALL ON SEQUENCE dashboard.tbl_dashboard_benutzer_override_override_id_seq TO web;
GRANT ALL ON SEQUENCE dashboard.tbl_dashboard_benutzer_override_override_id_seq TO vilesci;


--
-- Name: SEQUENCE tbl_dashboard_dashboard_id_seq; Type: ACL; Schema: dashboard; Owner: fhcomplete
--

GRANT ALL ON SEQUENCE dashboard.tbl_dashboard_dashboard_id_seq TO vilesci;


--
-- Name: TABLE tbl_dashboard_preset; Type: ACL; Schema: dashboard; Owner: fhcomplete
--

GRANT SELECT ON TABLE dashboard.tbl_dashboard_preset TO web;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE dashboard.tbl_dashboard_preset TO vilesci;


--
-- Name: SEQUENCE tbl_dashboard_preset_preset_id_seq; Type: ACL; Schema: dashboard; Owner: fhcomplete
--

GRANT ALL ON SEQUENCE dashboard.tbl_dashboard_preset_preset_id_seq TO vilesci;


--
-- Name: TABLE tbl_dashboard_widget; Type: ACL; Schema: dashboard; Owner: fhcomplete
--

GRANT SELECT ON TABLE dashboard.tbl_dashboard_widget TO web;
GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE dashboard.tbl_dashboard_widget TO vilesci;


--
-- Name: TABLE tbl_widget; Type: ACL; Schema: dashboard; Owner: fhcomplete
--

GRANT SELECT ON TABLE dashboard.tbl_widget TO web;
GRANT SELECT,INSERT,UPDATE ON TABLE dashboard.tbl_widget TO vilesci;


--
-- Name: SEQUENCE tbl_widget_widget_id_seq; Type: ACL; Schema: dashboard; Owner: fhcomplete
--

GRANT ALL ON SEQUENCE dashboard.tbl_widget_widget_id_seq TO vilesci;
		
EODASHBOARDSQL;
	
		if (!$db->db_query($qry))
		{
			echo '<strong>Schema Dashboard: '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo '<br>Neues Schema dashboard hinzugefuegt';
		}
	}
}

// Add permission: dashboard/admin
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'dashboard/admin';"))
{
    if($db->db_num_rows($result) == 0)
    {
        $qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('dashboard/admin', 'Adminberechtigung');";

        if(!$db->db_query($qry))
        {
            echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
        }
        else
        {
            echo 'system.tbl_berechtigung: Added permission for dashboard/admin<br>';
        }
    }
}

// Add permission: dashboard/benutzer
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz = 'dashboard/benutzer';"))
{
    if($db->db_num_rows($result) == 0)
    {
        $qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES('dashboard/benutzer', 'Benutzerberechtigung');";

        if(!$db->db_query($qry))
        {
            echo '<strong>system.tbl_berechtigung '.$db->db_last_error().'</strong><br>';
        }
        else
        {
            echo 'system.tbl_berechtigung: Added permission for dashboard/benutzer<br>';
        }
    }
}

