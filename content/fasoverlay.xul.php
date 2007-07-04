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
 */
header("Content-type: application/vnd.mozilla.xul+xml");
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';

require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Herstellen der DB Verbindung');

$user = get_uid();

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentenoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/lehrveranstaltungoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/student/interessentenoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/mitarbeiter/mitarbeiteroverlay.xul.php"?>';
/*echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/stpl-week-overlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/stpl-semester-overlay.xul.php"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';*/

?>

<!DOCTYPE overlay >
<!-- [<?php require_once("../locale/de-AT/tempus.dtd"); ?>] -->

<overlay id="FasOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasoverlay.js.php" />
<script type="application/x-javascript" src="chrome://global/content/nsTransferable.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/DragAndDrop.js"/>
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/dragboard.js.php"/>

<tree id="tree-verband" onmouseup="onVerbandSelect(event);"
	seltype="single" hidecolumnpicker="false" flex="1"
	enableColumnDrag="true"
    ondraggesture="nsDragAndDrop.startDrag(event,lvbgrpDDObserver);"
	datasources="../rdf/lehrverbandsgruppe.rdf.php" ref="http://www.technikum-wien.at/lehrverbandsgruppe/alle-verbaende"
	ondragdrop="nsDragAndDrop.drop(event,verbandtreeDDObserver)"
	ondragover="nsDragAndDrop.dragOver(event,verbandtreeDDObserver)"
	ondragenter="nsDragAndDrop.dragEnter(event,verbandtreeDDObserver)"
	ondragexit="nsDragAndDrop.dragExit(event,verbandtreeDDObserver)"
	>
	<treecols>
	    <treecol id="bez" label="Bezeichnung" flex="15" primary="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="stg" label="STG" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="stg_kz" label="KZ" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="sem" label="Sem" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="ver" label="Ver" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="grp" label="Grp" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="gruppe" label="SpzGruppe" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="typ" label="Typ" flex="1" hidden="true"/>
		<splitter class="tree-splitter"/>
	    <treecol id="stsem" label="StSem" flex="1" hidden="true"/>
	</treecols>

	<template>
	    <rule>
	      <treechildren>
	       <treeitem uri="rdf:*">
	         <treerow>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#name"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#stg"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#stg_kz"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#sem"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#ver"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#grp"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#gruppe"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#typ"/>
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#stsem"/>
	         </treerow>
	       </treeitem>
	      </treechildren>
	    </rule>
  </template>
</tree>

<tree id="tree-fachbereich" onmouseup="onFachbereichSelect(event);"
	seltype="single" hidecolumnpicker="false" flex="1"
	datasources="../rdf/fachbereich.rdf.php" ref="http://www.technikum-wien.at/fachbereich/liste">
	<treecols>
	    <treecol id="bezeichnung" label="Bezeichnung" flex="3" primary="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="kurzbz" label="Kurzbz" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="farbe" label="Farbe" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	     <treecol id="stg_kz" label="Stg_kz" flex="1" hidden="true"/>
	</treecols>

	<template>
	    <rule>
	      <treechildren>
	       <treeitem uri="rdf:*">
	         <treerow>
	           <treecell label="rdf:http://www.technikum-wien.at/fachbereich/rdf#bezeichnung"/>
	           <treecell label="rdf:http://www.technikum-wien.at/fachbereich/rdf#kurzbz"/>
	           <treecell label="rdf:http://www.technikum-wien.at/fachbereich/rdf#farbe"/>
	           <treecell label="rdf:http://www.technikum-wien.at/fachbereich/rdf#studiengang_kz"/>
	         </treerow>
	       </treeitem>
	      </treechildren>
	    </rule>
  </template>
</tree>

<tree id="tree-lektor" onmouseup="onLektorSelect(event);"
	seltype="single" hidecolumnpicker="false" flex="1"
	enableColumnDrag="true"
    ondraggesture="nsDragAndDrop.startDrag(event,mitarbeiterDDObserver);"
	ondragdrop="nsDragAndDrop.drop(event,LektorFunktionDDObserver)"
	ondragover="nsDragAndDrop.dragOver(event,LektorFunktionDDObserver)"
	ondragenter="nsDragAndDrop.dragEnter(event,LektorFunktionDDObserver)"
	ondragexit="nsDragAndDrop.dragExit(event,LektorFunktionDDObserver)"
	datasources="rdf:null" ref="http://www.technikum-wien.at/mitarbeiter/liste"
	context="fasoverlay-lektor-tree-popup"
	>
	<treecols>
	    <treecol id="kurzbz" label="Kuerzel" flex="2" primary="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="nachname" label="Nachname" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="vorname" label="Vorname" flex="2" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="titel" label="Titel" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="uid" label="UID" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="studiengang_kz" label="Studiengangkz" flex="1" hidden="true"/>
	</treecols>

	<template>
		<rule>
		<treechildren>
	       	<treeitem uri="rdf:*">
	         	<treerow>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#kurzbz"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpre"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"/>
	           		<treecell label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#studiengang_kz"/>
	         	</treerow>
	    	</treeitem>
	    </treechildren>
	    </rule>
  	</template>
</tree>

<tree id="tree-menu-mitarbeiter" onselect="onMitarbeiterSelect();"
	seltype="single" hidecolumnpicker="true" flex="1"
	>
	<treecols>
	    <treecol id="tree-menu-mitarbeiter-col-name" label="Filter" primary="true" flex="1"/>
	    <treecol id="tree-menu-mitarbeiter-col-filter" label="ColFilter" hidden="true" flex="1"/>
	</treecols>

    <treechildren>
	    <treeitem>
	        <treerow>
	        	<treecell label="Alle"/>
	        	<treecell label="Alle"/>
	        </treerow>
	    </treeitem>
	    <treeitem>
			<treerow>
	        	<treecell label="FixAngestellte"/>
	        	<treecell label="FixAngestellteAlle"/>
	        </treerow>
	    </treeitem>
	    <treeitem>
			<treerow>
	        	<treecell label="FreiAngestellte"/>
	        	<treecell label="FreiAngestellteAlle"/>
	        </treerow>
	    </treeitem>

	    <treeitem container="true" open="true">
			<treerow>
			   	<treecell label="Aktive"/>
			   	<treecell label="Aktive"/>
			</treerow>
			<treechildren>
				<treeitem>
					<treerow>
					   	<treecell label="FixAngestellte"/>
					   	<treecell label="FixAngestellte"/>
					</treerow>
				</treeitem>
				<treeitem>
					<treerow>
					   	<treecell label="FreiAngestellte"/>
					   	<treecell label="FreiAngestellte"/>
					</treerow>
				</treeitem>
				<treeitem>
					<treerow>
					   	<treecell label="Studiengangsleiter"/>
					   	<treecell label="Studiengangsleiter"/>
					</treerow>
				</treeitem>
				<treeitem>
					<treerow>
					   	<treecell label="Fachbereichsleiter"/>
					   	<treecell label="Fachbereichsleiter"/>
					</treerow>
				</treeitem>
			</treechildren>
	    </treeitem>

	    <treeitem container="true" open="true">
			<treerow>
			   	<treecell label="Inaktive"/>
			   	<treecell label="Inaktive"/>
			</treerow>
			<treechildren>
				<treeitem>
					<treerow>
					   	<treecell label="Karenziert"/>
					   	<treecell label="Karenziert"/>
					</treerow>
				</treeitem>
				<treeitem>
					<treerow>
					   	<treecell label="Ausgeschieden"/>
					   	<treecell label="Ausgeschieden"/>
					</treerow>
				</treeitem>
			</treechildren>
	    </treeitem>
	</treechildren>
</tree>

<vbox id="vbox-main">
<popupset>
		<popup id="fasoverlay-lektor-tree-popup">
			<menuitem label="Entfernen" oncommand="LektorFunktionDel();" />
		</popup>
</popupset>
	<tabbox id="tabbox-main" flex="3" orient="vertical">
		<tabs id="main-content-tabs" orient="horizontal">
		<?php
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('lva-verwaltung'))
			{
				echo '<tab id="tab-interessenten" label="PreStudent"/>';
				echo '<tab id="tab-studenten" label="Studenten"/>';
				echo '<tab id="tab-lfvt" label="Lehrveranstaltungen" />';
			}
			if($rechte->isBerechtigt('admin','0') || $rechte->isBerechtigt('mitarbeiter'))
			{
				echo '<tab id="tab-mitarbeiter" label="Mitarbeiter" onclick="document.getElementById(\'menu-content-tabs\').selectedItem=document.getElementById(\'tab-menu-mitarbeiter\');" />';
			}
		?>
		</tabs>
		<tabpanels id="tabpanels-main" flex="1">
		<?php
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('lva-verwaltung'))
			{
				echo '
				<!--  Interessenten  -->
				<vbox id="InteressentenEditor" />
				<!--  Studenten  -->
				<vbox id="studentenEditor" />
				<!-- Lehrfachverteilung -->
	            <vbox id="LehrveranstaltungEditor" />
	            ';
			}
			if($rechte->isBerechtigt('admin','0') || $rechte->isBerechtigt('mitarbeiter'))
			{
				 echo '<vbox id="MitarbeiterEditor" />';
			}
		?>
		</tabpanels>
	</tabbox>
</vbox>

</overlay>