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

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';

if(isset($_GET['person_id']) && is_numeric($_GET['person_id']))
	$person_id = $_GET['person_id'];
else
	die('Parameter person_id muss uebergeben werden');

$uid = get_uid();
?>

<window id="kontakt-window" title="Kontakt"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="loadKontakte(<?php echo $person_id; ?>);"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/kontakt.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/fasoverlay.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />

<vbox>
 	<!-- ADRESSEN -->
	<groupbox id="kontakt-groupbox-adresse">
		<caption label="Adressen" />
		<hbox>
			<tree id="kontakt-adressen-tree" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/adresse/liste"
				ondblclick="KontaktAdresseBearbeiten()"
				flags="dont-build-content"
				enableColumnDrag="true"
				style="margin-left:10px;margin-right:10px;margin-bottom:5px;" height="100"
				persist="hidden, height"
			>
				<treecols>
					<treecol id="kontakt-adressen-treecol-typ" label="Typ" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#typ_name" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-strasse" label="Strasse" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#strasse" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-plz" label="Plz" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#plz" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-ort" label="Ort" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#ort" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-gemeinde" label="Gemeinde" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#gemeinde" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-nation" label="Nation" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#nation" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-heimatadresse" label="Heimatadresse" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#heimatadresse" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-zustelladresse" label="Zustelladresse" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#zustelladresse" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
                    <treecol id="kontakt-adressen-treecol-co_name" label="Abweich.Empf.(c/o)" flex="1" hidden="false"
                             class="sortDirectionIndicator"
                             sort="rdf:http://www.technikum-wien.at/adresse/rdf#co_name" onclick="KontaktAdresseTreeSort()"/>
                    <splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-firma_id" label="Firma_id" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#firma_id" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-adresse_id" label="Adresse_id" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#adresse_id" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-person_id" label="Person_id" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#person_id" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-name" label="Name" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#name" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-updateamum" label="letzte Aenderung" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#updateamum" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-firma_name" label="Firma" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#firma_name" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-rechnungsadresse" label="Rechnungsadresse" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#rechnungsadresse" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-adressen-treecol-anmerkung" label="Anmerkung" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/adresse/rdf#anmerkung" onclick="KontaktAdresseTreeSort()"/>
					<splitter class="tree-splitter"/>
				</treecols>

				<template>
					<rule>
						<treechildren>
							<treeitem uri="rdf:*">
								<treerow>
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#typ_name" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#strasse" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#plz" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#ort" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#gemeinde" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#nation" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#heimatadresse" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#zustelladresse" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#co_name" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#firma_id" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#adresse_id" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#person_id" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#name" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#updateamum" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#firma_name" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#rechnungsadresse" />
									<treecell label="rdf:http://www.technikum-wien.at/adresse/rdf#anmerkung" />
								</treerow>
							</treeitem>
						</treechildren>
					</rule>
				</template>
			</tree>
			<vbox>
				<button id='kontakt-adressen-neu' label='Neu' oncommand='KontaktAdresseNeu()' />
				<button id='kontakt-adressen-bearbeiten' label='Bearbeiten' oncommand='KontaktAdresseBearbeiten()' />
				<button id='kontakt-adressen-loeschen' label='Loeschen' oncommand='KontaktAdresseDelete()' />
			</vbox>
		</hbox>
	</groupbox>

	<groupbox id="kontakt-groupbox-adresse">
		<caption label="Kontakt" />
		<hbox>
			<tree id="kontakt-kontakt-tree" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/kontakt/liste"
				ondblclick="KontaktKontaktBearbeiten();"
				flags="dont-build-content"
				enableColumnDrag="true"
				style="margin-left:10px;margin-right:10px;margin-bottom:5px;" height="100"
				persist="hidden, height"
			>
				<treecols>
					<treecol id="kontakt-kontakt-treecol-kontakttyp" label="Typ" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/kontakt/rdf#kontakttyp" onclick="KontaktKontaktTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-kontakt-treecol-kontakt" label="Kontakt" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/kontakt/rdf#kontakt" onclick="KontaktKontaktTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-kontakt-treecol-zustellung" label="Zustellung" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/kontakt/rdf#zustellung" onclick="KontaktKontaktTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-kontakt-treecol-anmerkung" label="Anmerkung" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/kontakt/rdf#anmerkung" onclick="KontaktKontaktTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-kontakt-treecol-firma_name" label="Firma" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/kontakt/rdf#firma_name" onclick="KontaktKontaktTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-kontakt-treecol-firma_id" label="Firma_id" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/kontakt/rdf#firma_id" onclick="KontaktKontaktTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-kontakt-treecol-person_id" label="Person_id" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/kontakt/rdf#person_id" onclick="KontaktKontaktTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-kontakt-treecol-kontakt_id" label="Kontakt_id" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/kontakt/rdf#kontakt_id" onclick="KontaktKontaktTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-kontakt-treecol-updateamum" label="letzte Aenderung" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/kontakt/rdf#updateamum" onclick="KontaktKontaktTreeSort()"/>
					<splitter class="tree-splitter"/>
				</treecols>

				<template>
					<rule>
						<treechildren>
							<treeitem uri="rdf:*">
								<treerow>
									<treecell label="rdf:http://www.technikum-wien.at/kontakt/rdf#kontakttyp" />
									<treecell label="rdf:http://www.technikum-wien.at/kontakt/rdf#kontakt" />
									<treecell label="rdf:http://www.technikum-wien.at/kontakt/rdf#zustellung" />
									<treecell label="rdf:http://www.technikum-wien.at/kontakt/rdf#anmerkung" />
									<treecell label="rdf:http://www.technikum-wien.at/kontakt/rdf#firma_name" />
									<treecell label="rdf:http://www.technikum-wien.at/kontakt/rdf#firma_id" />
									<treecell label="rdf:http://www.technikum-wien.at/kontakt/rdf#person_id" />
									<treecell label="rdf:http://www.technikum-wien.at/kontakt/rdf#kontakt_id" />
									<treecell label="rdf:http://www.technikum-wien.at/kontakt/rdf#updateamum" />
								</treerow>
							</treeitem>
						</treechildren>
					</rule>
				</template>
			</tree>
			<vbox>
				<button id="kontakt-kontakt-neu" label="Neu" oncommand="KontaktKontaktNeu()" />
				<button id="kontakt-kontakt-bearbeiten" label="Bearbeiten" oncommand="KontaktKontaktBearbeiten()" />
				<button id="kontakt-kontakt-loeschen" label="Loeschen" oncommand="KontaktKontaktDelete()" />
			</vbox>
		</hbox>
	</groupbox>
<?php
$recht = new benutzerberechtigung();
$recht->getBerechtigungen($uid);
if($recht->isberechtigt('mitarbeiter/bankdaten') || $recht->isberechtigt('student/bankdaten'))
echo '
	<groupbox id="kontakt-groupbox-bankverbindung">
		<caption label="Bankverbindungen" />
		<hbox>
			<tree id="kontakt-bankverbindung-tree" seltype="single" hidecolumnpicker="false" flex="1"
				datasources="rdf:null" ref="http://www.technikum-wien.at/bankverbindung/liste"
				ondblclick="KontaktBankverbindungBearbeiten()"
				flags="dont-build-content"
				enableColumnDrag="true"
				style="margin-left:10px;margin-right:10px;margin-bottom:5px;" height="100"
				persist="hidden, height"
			>
				<treecols>
					<treecol id="kontakt-bankverbindung-treecol-name" label="Name" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/bankverbindung/rdf#name" onclick="KontaktBankverbindungTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-bankverbindung-treecol-anschrift" label="Anschrift" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/bankverbindung/rdf#anschrift" onclick="KontaktBankverbindungTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-bankverbindung-treecol-bic" label="BIC" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/bankverbindung/rdf#bic" onclick="KontaktBankverbindungTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-bankverbindung-treecol-blz" label="BLZ" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/bankverbindung/rdf#blz" onclick="KontaktBankverbindungTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-bankverbindung-treecol-iban" label="IBAN" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/bankverbindung/rdf#iban" onclick="KontaktBankverbindungTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-bankverbindung-treecol-kontonr" label="Kontonummer" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/bankverbindung/rdf#kontonr" onclick="KontaktBankverbindungTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-bankverbindung-treecol-typ_bezeichnung" label="Typ" flex="1" hidden="false"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/bankverbindung/rdf#typ_bezeichnung" onclick="KontaktBankverbindungTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-bankverbindung-treecol-verrechnung" label="Verrechnung" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/bankverbindung/rdf#verrechnung" onclick="KontaktBankverbindungTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-bankverbindung-treecol-person_id" label="Person_id" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/bankverbindung/rdf#person_id" onclick="KontaktBankverbindungTreeSort()"/>
					<splitter class="tree-splitter"/>
					<treecol id="kontakt-bankverbindung-treecol-bankverbindung_id" label="Bankverbindung_id" flex="1" hidden="true"
						class="sortDirectionIndicator"
						sort="rdf:http://www.technikum-wien.at/bankverbindung/rdf#bankverbindung_id" onclick="KontaktBankverbindungTreeSort()"/>
					<splitter class="tree-splitter"/>
				</treecols>

				<template>
					<rule>
						<treechildren>
							<treeitem uri="rdf:*">
								<treerow>
									<treecell label="rdf:http://www.technikum-wien.at/bankverbindung/rdf#name" />
									<treecell label="rdf:http://www.technikum-wien.at/bankverbindung/rdf#anschrift" />
									<treecell label="rdf:http://www.technikum-wien.at/bankverbindung/rdf#bic" />
									<treecell label="rdf:http://www.technikum-wien.at/bankverbindung/rdf#blz" />
									<treecell label="rdf:http://www.technikum-wien.at/bankverbindung/rdf#iban" />
									<treecell label="rdf:http://www.technikum-wien.at/bankverbindung/rdf#kontonr" />
									<treecell label="rdf:http://www.technikum-wien.at/bankverbindung/rdf#typ_bezeichnung" />
									<treecell label="rdf:http://www.technikum-wien.at/bankverbindung/rdf#verrechnung" />
									<treecell label="rdf:http://www.technikum-wien.at/bankverbindung/rdf#person_id" />
									<treecell label="rdf:http://www.technikum-wien.at/bankverbindung/rdf#bankverbindung_id" />

								</treerow>
							</treeitem>
						</treechildren>
					</rule>
				</template>
			</tree>
			<vbox>
				<button id="kontakt-bankverbindung-neu" label="Neu" oncommand="KontaktBankverbindungNeu()" />
				<button id="kontakt-bankverbindung-bearbeiten" label="Bearbeiten" oncommand="KontaktBankverbindungBearbeiten()" />
				<button id="kontakt-bankverbindung-loeschen" label="Loeschen" oncommand="KontaktBankverbindungDelete()" />
			</vbox>
		</hbox>
	</groupbox>';
?>
</vbox>
</window>
