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
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

echo '<?xul-overlay href="'.APP_ROOT.'content/student/studentenoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/lvplanung/lehrveranstaltungoverlay.xul.php"?>';
echo '<?xul-overlay href="'.APP_ROOT.'content/mitarbeiter/mitarbeiteroverlay.xul.php"?>';

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
	seltype="single" hidecolumnpicker="false" flex="1" flags="dont-build-content"
	enableColumnDrag="true"
    ondraggesture="nsDragAndDrop.startDrag(event,lvbgrpDDObserver);"
	datasources="../rdf/lehrverbandsgruppe.rdf.php" ref="http://www.technikum-wien.at/lehrverbandsgruppe/alle-verbaende"
	ondragdrop="nsDragAndDrop.drop(event,verbandtreeDDObserver)"
	ondrop="nsDragAndDrop.drop(event,verbandtreeDDObserver)"
	ondragover="nsDragAndDrop.dragOver(event,verbandtreeDDObserver)"
	ondragenter="nsDragAndDrop.dragEnter(event,verbandtreeDDObserver)"
	ondragexit="nsDragAndDrop.dragExit(event,verbandtreeDDObserver)"
	>
	<treecols>
	    <treecol id="bez" label="Bezeichnung" persist="hidden, width, ordinal" flex="15" primary="true" />
	    <splitter class="tree-splitter"/>
	    <treecol id="stg" label="STG" flex="2" persist="hidden, width, ordinal" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="stg_kz" label="KZ" flex="2" persist="hidden, width, ordinal" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="sem" label="Sem" flex="1" persist="hidden, width, ordinal" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="ver" label="Ver" flex="1" persist="hidden, width, ordinal" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="grp" label="Grp" flex="1" persist="hidden, width, ordinal" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="gruppe" label="SpzGruppe" persist="hidden, width, ordinal" flex="1" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="typ" label="Typ" flex="1" persist="hidden, width, ordinal" hidden="true"/>
		<splitter class="tree-splitter"/>
	    <treecol id="stsem" label="StSem" flex="1" persist="hidden, width, ordinal" hidden="true"/>
	    <splitter class="tree-splitter"/>
	    <treecol id="tree-verband-col-orgform" label="orgform" flex="1" persist="hidden, width, ordinal" hidden="true"/>
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
	           <treecell label="rdf:http://www.technikum-wien.at/lehrverbandsgruppe/rdf#orgform"/>
	         </treerow>
	       </treeitem>
	      </treechildren>
	    </rule>
  </template>
</tree>

<vbox id="vbox-organisationseinheit">
	<tree id="tree-organisationseinheit" onmouseup="onOrganisationseinheitSelect(event);"
		seltype="single" hidecolumnpicker="false" flex="1"
		datasources="../rdf/organisationseinheit_menue.rdf.php" ref="http://www.technikum-wien.at/organisationseinheit">
		<treecols>
		    <treecol id="organisationseinheit-treecol-typ" label="Typ" flex="2" hidden="false"/>
		    <splitter class="tree-splitter"/>
		    <treecol id="organisationseinheit-treecol-bezeichnung" label="Bezeichnung" flex="3"/>
		    <splitter class="tree-splitter"/>
		    <treecol id="organisationseinheit-treecol-oe_kurzbz" label="oe_kurzbz" hidden="true" flex="1"/>
		</treecols>

		<template>
		    <rule>
		      <treechildren>
		       <treeitem uri="rdf:*">
		         <treerow>
		           <treecell label="rdf:http://www.technikum-wien.at/organisationseinheit/rdf#typ"/>
		           <treecell label="rdf:http://www.technikum-wien.at/organisationseinheit/rdf#bezeichnung"/>
		           <treecell label="rdf:http://www.technikum-wien.at/organisationseinheit/rdf#oe_kurzbz"/>
		         </treerow>
		       </treeitem>
		      </treechildren>
		    </rule>
	  </template>
	</tree>
</vbox>

<vbox id="vbox-lektor">
	<hbox>
		<!--		<spacer flex="1" />-->
		<toolbox>
			<toolbar id="toolbarLektorTreeFilter" tbautostretch="always" persist="collapsed">
				<toolbarbutton id="toolbarbuttonLektorTreeRefresh"
							   image="../skin/images/refresh.png"
							   oncommand="onLektorRefresh();"
							   tooltiptext="Neu laden"
				/>
				<textbox id="fas-lektor-filter" size="30" oninput="onLektorFilter()" flex="1"/>
			</toolbar>
		</toolbox>
		<!--		<spacer flex="1" />-->
	</hbox>
	<tree id="tree-lektor" onmouseup="onLektorSelect(event);"
		seltype="multi" hidecolumnpicker="false" flex="1"
		enableColumnDrag="true"
		ondraggesture="nsDragAndDrop.startDrag(event,mitarbeiterDDObserver);"
		ondrop="nsDragAndDrop.drop(event,LektorFunktionDDObserver)"
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
			<splitter class="tree-splitter"/>
			<treecol id="tree-lektor-fixangestellt" label="Fixangestellt" flex="1" hidden="true"/>
		</treecols>

		<template>
			<rule>
			<treechildren>
				<treeitem uri="rdf:*">
					<treerow>
						<treecell properties="Lektor_rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#kurzbz"/>
						<treecell properties="Lektor_rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname"/>
						<treecell properties="Lektor_rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname"/>
						<treecell properties="Lektor_rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#titelpre"/>
						<treecell properties="Lektor_rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"/>
						<treecell properties="Lektor_rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#studiengang_kz"/>
						<treecell properties="Lektor_rdf:http://www.technikum-wien.at/mitarbeiter/rdf#aktiv" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#fixangestellt"/>
					</treerow>
				</treeitem>
			</treechildren>
			</rule>
		</template>
	</tree>
</vbox>

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
					   	<treecell label="StudiengangsleiterIn"/>
					   	<treecell label="Studiengangsleiter"/>
					</treerow>
				</treeitem>
				<treeitem>
					<treerow>
					   	<treecell label="InstitutsleiterIn"/>
					   	<treecell label="Fachbereichsleiter"/>
					</treerow>
				</treeitem>
				<treeitem>
					<treerow>
					   	<treecell label="Karenziert"/>
					   	<treecell label="Karenziert"/>
					</treerow>
				</treeitem>
				<treeitem>
					<treerow>
					   	<treecell label="ohne Verwendung"/>
					   	<treecell label="ohneVerwendung"/>
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
					   	<treecell label="mit Verwendung"/>
					   	<treecell label="mitVerwendung"/>
					</treerow>
				</treeitem>
			</treechildren>
	    </treeitem>
		<?php
		if($rechte->isBerechtigt('vertrag/mitarbeiter'))
		{
			echo '
			<treeitem container="true" open="true">
				<treerow>
				   	<treecell label="Vertrag"/>
				   	<treecell label="Vertrag"/>
				</treerow>
				<treechildren>
					<treeitem>
						<treerow>
						   	<treecell label="noch nicht retourniert"/>
						   	<treecell label="VertragNochNichtRetour"/>
						</treerow>
					</treeitem>
					<treeitem>
						<treerow>
							<treecell label="Habilitiert"/>
							<treecell label="VertragHabilitiert"/>
						</treerow>
					</treeitem>
					<treeitem>
						<treerow>
							<treecell label="nicht Habilitiert"/>
							<treecell label="VertragNichtHabilitiert"/>
						</treerow>
					</treeitem>
					<treeitem>
						<treerow>
							<treecell label="noch nicht gedruckt"/>
							<treecell label="VertragNichtGedruckt"/>
						</treerow>
					</treeitem>
				</treechildren>
		    </treeitem>
			';
		}
		?>
	</treechildren>
</tree>

<vbox id="vbox-main">
<popupset>
		<menupopup id="fasoverlay-lektor-tree-popup">
			<menuitem label="EMail senden (intern)" oncommand="LektorFunktionMail();" />
			<menuitem label="EMail senden (privat)" oncommand="LektorFunktionMailPrivat();" />
			<menuseparator />
			<menuitem label="Entfernen" oncommand="LektorFunktionDel();" />
		</menupopup>
</popupset>
	<tabbox id="tabbox-main" flex="3" orient="vertical">
		<tabs id="main-content-tabs" orient="horizontal">
		<?php
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('assistenz'))
			{
				echo '<tab id="tab-studenten" label="Studierende" onclick="ChangeTabsToVerband()"/>';
				echo '<tab id="tab-lfvt" label="Lehrveranstaltungen" onclick="ChangeTabsToVerband()"/>';
			}
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('mitarbeiter'))
			{
				echo '<tab id="tab-mitarbeiter" label="MitarbeiterInnen" onclick="document.getElementById(\'menu-content-tabs\').selectedItem=document.getElementById(\'tab-menu-mitarbeiter\');" />';
			}
		?>
			<tab id="tab-notizen" label="Meine Notizen" />
		</tabs>
		<tabpanels id="tabpanels-main" flex="1">
		<?php
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('assistenz'))
			{
				echo '
				<!--  Studenten  -->
				<vbox id="studentenEditor" />
				<!-- Lehrfachverteilung -->
	            <vbox id="LehrveranstaltungEditor" />
	            ';
			}
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('mitarbeiter'))
			{
				 echo '<vbox id="MitarbeiterEditor" />';
			}

		?>
		<vbox id="box-notiz">
			<box class="Notiz" flex="1" id="box-notizen"/>
		</vbox>
		</tabpanels>
	</tabbox>
</vbox>

</overlay>
