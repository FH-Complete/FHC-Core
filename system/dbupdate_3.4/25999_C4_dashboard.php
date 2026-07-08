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
 * CIS 4.0 Dashboard
 */
if (! defined('DB_NAME')) exit('No direct script access allowed');

// Add widget: Hallo Welt
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_widget WHERE widget_kurzbz = 'hallowelt';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = <<<EOWHW
			INSERT INTO 
				dashboard.tbl_widget (
					widget_kurzbz, 
					beschreibung, 
					arguments, 
					setup
				) 
			VALUES (
				'hallowelt', 
				'Hallo Welt Widget', 
				'{"css": "d-flex justify-content-center align-items-center h-100", "title": "Hallo Welt"}'::jsonb,
				'{"file": "public/js/components/DashboardWidget/Default.js", "icon": "https://upload.wikimedia.org/wikipedia/commons/8/8a/Farben-Testbild.svg", "name": "Hallo Welt", "width": {"max": 99}, "height": {"max": 99}, "hideFooter": false}'::jsonb
			);
EOWHW;

		if(!$db->db_query($qry))
		{
			echo '<strong>dashboard.tbl_widget '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo 'dashboard.tbl_widget: Added Widget "Hallo Welt"<br>';
		}
	}
}

// Add widget: News
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_widget WHERE widget_kurzbz = 'news';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = <<<EOWHW
			INSERT INTO 
				dashboard.tbl_widget (
					widget_kurzbz, 
					beschreibung, 
					arguments, 
					setup
				) 
			VALUES (
				'news', 
				'News Widget', 
				'{}'::jsonb,
				'{"file": "public/js/components/DashboardWidget/News.js", "icon": "/skin/images/fh_technikum_wien_illustration_klein.png", "name": "News", "width": {"max": 4, "min": 1}, "height": {"max": 2, "min": 1}, "cis4link": "/CisVue/Cms/news", "hideFooter": false}'::jsonb
			);
EOWHW;

		if(!$db->db_query($qry))
		{
			echo '<strong>dashboard.tbl_widget '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo 'dashboard.tbl_widget: Added Widget "News"<br>';
		}
	}
}

// Add widget: Bookmark
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_widget WHERE widget_kurzbz = 'url';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = <<<EOWHW
			INSERT INTO 
				dashboard.tbl_widget (
					widget_kurzbz, 
					beschreibung, 
					arguments, 
					setup
				) 
			VALUES (
				'url', 
				'Bookmark Widget', 
				'{}'::jsonb,
				'{"file": "public/js/components/DashboardWidget/Url.js", "icon": "/skin/images/fh_technikum_wien_illustration_klein.png", "name": "Bookmark", "width": 1, "height": {"max": 2, "min": 1}, "hideFooter": true}'::jsonb
			);
EOWHW;

		if(!$db->db_query($qry))
		{
			echo '<strong>dashboard.tbl_widget '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo 'dashboard.tbl_widget: Added Widget "Bookmark"<br>';
		}
	}
}

// Add widget: Ampel
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_widget WHERE widget_kurzbz = 'ampel';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = <<<EOWHW
			INSERT INTO 
				dashboard.tbl_widget (
					widget_kurzbz, 
					beschreibung, 
					arguments, 
					setup
				) 
			VALUES (
				'ampel', 
				'Ampel Widget', 
				'{}'::jsonb,
				'{"file": "public/js/components/DashboardWidget/Ampel.js", "icon": "/skin/images/fh_technikum_wien_illustration_klein.png", "name": "Ampel", "width": 1, "height": {"max": 2, "min": 1}, "hideFooter": false}'::jsonb
			);
EOWHW;

		if(!$db->db_query($qry))
		{
			echo '<strong>dashboard.tbl_widget '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo 'dashboard.tbl_widget: Added Widget "Ampel"<br>';
		}
	}
}

// update stundenplan widget
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_widget WHERE widget_kurzbz = 'stundenplan';"))
{
	if($db->db_num_rows($result) > 0)
	{
		$qry = <<<EOWHW
			UPDATE 
				dashboard.tbl_widget 
			SET
				widget_kurzbz = 'lvplan', 
				beschreibung = 'LV-Plan Widget', 
				arguments = '{"bodyClass": "p-0"}'::jsonb, 
				setup = '{"file": "public/js/components/DashboardWidget/LvPlan.js", "icon": "/skin/images/fh_technikum_wien_illustration_klein.png", "name": "LV-Plan", "width": {"max": 4, "min": 1}, "height": {"max": 3, "min": 1}, "cis4link": "/Cis/LvPlan", "hideFooter": false}'::jsonb 
			WHERE
				widget_kurzbz = 'stundenplan';
EOWHW;

		if(!$db->db_query($qry))
		{
			echo '<strong>dashboard.tbl_widget '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo 'dashboard.tbl_widget: Renamed Widget "Stundenplan" to "LV-Plan"<br>';
		}
	}
}

// Add widget: LV-Plan
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_widget WHERE widget_kurzbz = 'lvplan';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = <<<EOWHW
			INSERT INTO 
				dashboard.tbl_widget (
					widget_kurzbz, 
					beschreibung, 
					arguments, 
					setup
				) 
			VALUES (
				'lvplan', 
				'LV-Plan Widget', 
				'{"bodyClass": "p-0"}'::jsonb,
				'{"file": "public/js/components/DashboardWidget/LvPlan.js", "icon": "/skin/images/fh_technikum_wien_illustration_klein.png", "name": "LV-Plan", "width": {"max": 4, "min": 1}, "height": {"max": 3, "min": 1}, "cis4link": "/Cis/LvPlan", "hideFooter": false}'::jsonb
			);
EOWHW;

		if(!$db->db_query($qry))
		{
			echo '<strong>dashboard.tbl_widget '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo 'dashboard.tbl_widget: Added Widget "LV-Plan"<br>';
		}
	}
}

// Add widget: Studiengang
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_widget WHERE widget_kurzbz = 'studiengang';"))
{
	if($db->db_num_rows($result) == 0)
	{
		$qry = <<<EOWHW
			INSERT INTO 
				dashboard.tbl_widget (
					widget_kurzbz, 
					beschreibung, 
					arguments, 
					setup
				) 
			VALUES (
				'studiengang', 
				'Das Studiengang-Widget enthält Informationen über den Studiengang eines Studenten.', 
				'{}'::jsonb,
				'{"file": "public/js/components/DashboardWidget/Studiengang.js", "icon": "/skin/images/fh_technikum_wien_illustration_klein.png", "name": "Studiengang", "width": {"max": 2, "min": 1}, "height": {"max": 4, "min": 1}, "hideFooter": true}'::jsonb
			);
EOWHW;

		if(!$db->db_query($qry))
		{
			echo '<strong>dashboard.tbl_widget '.$db->db_last_error().'</strong><br>';
		}
		else
		{
			echo 'dashboard.tbl_widget: Added Widget "Studiengang"<br>';
		}
	}
}

// Add dashboard: CIS
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_dashboard WHERE dashboard_kurzbz = 'CIS';"))
{
    if($db->db_num_rows($result) == 0)
    {
        $qry = "INSERT INTO dashboard.tbl_dashboard(dashboard_kurzbz, beschreibung) VALUES('CIS', 'CIS 4.0 Dashboard');";

        if(!$db->db_query($qry))
        {
            echo '<strong>dashboard.tbl_dashboard '.$db->db_last_error().'</strong><br>';
        }
        else
        {
            echo 'dashboard.tbl_dashboard: Added Dashboard "CIS"<br>';
        }

		$widgetmappingqry = <<<EOWMQ
			insert into 
				dashboard.tbl_dashboard_widget (
					dashboard_id, 
					widget_id
				) 
				(
					select 
						td.dashboard_id, tw.widget_id 
					from 
						dashboard.tbl_dashboard td 
					cross join 
						dashboard.tbl_widget tw 
					where 
						td.dashboard_kurzbz = 'CIS' 
						and 
						tw.widget_kurzbz in ('hallowelt', 'news', 'url', 'ampel', 'studiengang')
					order by td.dashboard_id, tw.widget_id
				) 
			ON CONFLICT (dashboard_id, widget_id) DO NOTHING
EOWMQ;

        if(!$db->db_query($widgetmappingqry))
        {
            echo '<strong>dashboard.tbl_dashboard_widget '.$db->db_last_error().'</strong><br>';
        }
        else
        {
            echo 'dashboard.tbl_dashboard_widget: Added WidgetMapping for Dashboard "CIS"<br>';
        }
    }
}

// Add general preset for dashboard: CIS
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_dashboard_preset WHERE funktion_kurzbz IS NULL AND dashboard_id = (select dashboard_id from dashboard.tbl_dashboard where dashboard_kurzbz = 'CIS');"))
{
    if($db->db_num_rows($result) == 0)
    {
        $qry = <<<EOGP
			insert into 
				dashboard.tbl_dashboard_preset (
					dashboard_id,
					funktion_kurzbz,
					preset
				)
				values (
					(select dashboard_id from dashboard.tbl_dashboard where dashboard_kurzbz = 'CIS'),
					NULL,
					(SELECT '{"general": {"widgets": {"2ad7f459a8218ae39f0b68201a9545a4": {"place": {"4": {"h": 2, "w": 3, "x": 0, "y": 0}}, "config": [], "preset": 1, "widget": ' || (select widget_id from dashboard.tbl_widget where widget_kurzbz = 'news')  || ', "widgetid": "2ad7f459a8218ae39f0b68201a9545a4"}, "4590326a80c27e61d861b18b46858084": {"place": {"4": {"h": 1, "w": 1, "x": 3, "y": 2}}, "config": [], "preset": 1, "widget": ' || (select widget_id from dashboard.tbl_widget where widget_kurzbz = 'lvplan')  || ', "widgetid": "4590326a80c27e61d861b18b46858084"}, "8ec3f441d4adc9dd2bc5cc14a663eae9": {"place": {"4": {"h": 1, "w": 1, "x": 0, "y": 2}}, "config": [], "preset": 1, "widget": ' || (select widget_id from dashboard.tbl_widget where widget_kurzbz = 'url')  || ', "widgetid": "8ec3f441d4adc9dd2bc5cc14a663eae9"}, "e4214d3f2bed8740d4c9cede165ccd07": {"place": {"4": {"h": 2, "w": 1, "x": 3, "y": 0}}, "config": [], "preset": 1, "widget": ' || (select widget_id from dashboard.tbl_widget where widget_kurzbz = 'ampel')  || ', "widgetid": "e4214d3f2bed8740d4c9cede165ccd07"}}}}')::jsonb
				)
EOGP;

        if(!$db->db_query($qry))
        {
            echo '<strong>dashboard.tbl_dashboard_preset '.$db->db_last_error().'</strong><br>';
        }
        else
        {
            echo 'dashboard.tbl_dashboard_preset: Added General Preset for Dashboard "CIS"<br>';
        }
	}
}

// Add general preset for dashboard: CIS
if($result = @$db->db_query("SELECT 1 FROM dashboard.tbl_dashboard_preset WHERE funktion_kurzbz = 'Student' AND dashboard_id = (select dashboard_id from dashboard.tbl_dashboard where dashboard_kurzbz = 'CIS');"))
{
    if($db->db_num_rows($result) == 0)
    {
        $qry = <<<EOGP
			insert into 
				dashboard.tbl_dashboard_preset (
					dashboard_id,
					funktion_kurzbz,
					preset
				)
				values (
					(select dashboard_id from dashboard.tbl_dashboard where dashboard_kurzbz = 'CIS'),
					'Student',
					(SELECT '{"Student": {"widgets": {"3c448b2a774ff361bf59516ef0b841ef": {"place": {"4": {"h": 1, "w": 1, "x": 3, "y": 0}}, "config": [], "preset": 1, "widget": 6, "widgetid": "3c448b2a774ff361bf59516ef0b841ef"}, "83808e47ccf95db1afb4fe014d221ca9": {"place": {"4": {"h": 1, "w": 1, "x": 0, "y": 0}}, "config": {}, "preset": 1, "widget": ' || (select widget_id from dashboard.tbl_widget where widget_kurzbz = 'studiengang')  || ', "widgetid": "83808e47ccf95db1afb4fe014d221ca9"}}}}')::jsonb
				)
EOGP;

        if(!$db->db_query($qry))
        {
            echo '<strong>dashboard.tbl_dashboard_preset '.$db->db_last_error().'</strong><br>';
        }
        else
        {
            echo 'dashboard.tbl_dashboard_preset: Added Student Preset for Dashboard "CIS"<br>';
        }
	}
}
