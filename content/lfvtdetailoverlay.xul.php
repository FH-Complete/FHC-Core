<?php
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");
require_once('../vilesci/config.inc.php');
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes" ?>';
echo "<?xml-stylesheet href=\"".APP_ROOT."content/lfvt.css\" type=\"text/css\" ?>";

?>

<overlay id="LFVTDetailOverlay"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	>
<!-- *************************** LEHREINHEIT DETAILS ************************* -->
<!--<script src="chrome://global/content/nsTransferable.js"/>-->
<vbox id="lfvt-detail" class="lvaDetail"  style="margin:0px;" >
<popupset>
	<popup id="lfvt_detail_gruppe_tree_popup">
		<menuitem label="Entfernen" oncommand="lfvt_LehreinheitGruppeDel();" />
	</popup>
</popupset>
<hbox style="background:#eeeeee;margin:0px;padding:2px">
			<label value="Details" style="font-size:12pt;font-weight:bold;margin-top:5px;"  flex="1" />
			<spacer flex="1" />
			<button id="lfvt_detail_button_save" label="speichern" oncommand="lfvtDetailSave();" disabled="true"/>
		</hbox>
		<checkbox id="lfvt_detail_checkbox_new" hidden="true"/>
		<textbox id="lfvt_detail_textbox_lehreinheit_id" hidden="true"/>
		<grid id="gridLFVT" flex="1" datasources="rdf:null"
			ref="http://www.technikum-wien.at/tempus/lva/liste"
			style="padding:5px;"
			>
  			<columns  >
				<column  />
				<column style="min-width:240px" />
				<column />
				<column style="min-width:240px" />
  			</columns>
  			<rows>
			<!-- fehlt hier die eindeutige ID ? -->
				<row >
  						<label value="LVNR" />
  						<textbox id="lfvt_detail_textbox_lvnr" maxlength="20" disabled="true" />

  						<label value="UNR" />
  	    				<textbox id="lfvt_detail_textbox_unr" disabled="true"/>
				</row>
				<row>
  						<label value="Sprache" />
						<menulist id="lfvt_detail_menulist_sprache" 
						          datasources="<?php echo APP_ROOT; ?>rdf/sprache.rdf.php" flex="1"
								  ref="http://www.technikum-wien.at/sprachen/liste" disabled="true">
							<template>
								<menupopup>
									<menuitem value="rdf:http://www.technikum-wien.at/sprachen/rdf#bezeichnung"
									          label="rdf:http://www.technikum-wien.at/sprachen/rdf#bezeichnung"
											  uri="rdf:*"/>
									</menupopup>
							</template>
						</menulist>
  						<label value="Lehrveranstaltung" />
  						<textbox id="lfvt_detail_textbox_lehrveranstaltung" maxlength="20" disabled="true"/>
				</row>
				<row>
  						<label value="Lehrfach" />
						<menulist id="lfvt_detail_menulist_lehrfach" disabled="true"
						          datasources="rdf:null" flex="1"
						          ref="http://www.technikum-wien.at/lehrfach/liste"  >
				  			<template>
								<menupopup>
									<menuitem value="rdf:http://www.technikum-wien.at/lehrfach/rdf#lehrfach_id"
								              label="rdf:http://www.technikum-wien.at/lehrfach/rdf#bezeichnung"
											  uri="rdf:*"/>
									</menupopup>
							</template>
						</menulist>

						<label value="Lehrform" />
						<menulist id="lfvt_detail_menulist_lehrform" disabled="true"
						          datasources="<?php echo APP_ROOT ?>rdf/lehrform.rdf.php" flex="1"
		                          ref="http://www.technikum-wien.at/lehrform/liste" >
							<template>
								<menupopup>
									<menuitem value="rdf:http://www.technikum-wien.at/lehrform/rdf#kurzbz"
							        		      label="rdf:http://www.technikum-wien.at/lehrform/rdf#kurzbz"
										  		  uri="rdf:*"/>
								</menupopup>
							</template>
						</menulist>
  	  			</row>
				<row>
  	    				<label value="Raumtyp" />
  						<menulist id="lfvt_detail_menulist_raumtyp" disabled="true"
  						          datasources="<?php echo APP_ROOT ?>rdf/raumtyp.rdf.php" flex="1"
					              ref="http://www.technikum-wien.at/raumtyp/liste" >
			  				<template>
								<menupopup>										
										<menuitem value="rdf:http://www.technikum-wien.at/raumtyp/rdf#kurzbz"
										          label="rdf:http://www.technikum-wien.at/raumtyp/rdf#kurzbz"
												  uri="rdf:*"/>
								</menupopup>
							</template>
						</menulist>

  						<label value="Raumtyp alternativ" />
  						<menulist id="lfvt_detail_menulist_raumtypalternativ" disabled="true"
  								  datasources="<?php echo APP_ROOT ?>rdf/raumtyp.rdf.php" flex="1"
					              ref="http://www.technikum-wien.at/raumtyp/liste" >
			  				<template>
								<menupopup>										
										<menuitem value="rdf:http://www.technikum-wien.at/raumtyp/rdf#kurzbz"
										          label="rdf:http://www.technikum-wien.at/raumtyp/rdf#kurzbz"
												  uri="rdf:*"/>
								</menupopup>
							</template>
						</menulist>
  				</row>
				<row>
   	   					<label value="Lehre" />
						<checkbox id="lfvt_detail_checkbox_lehre" disabled="true"/>
						
  						<label value="Stundenblockung" />
  						<textbox id="lfvt_detail_textbox_stundenblockung" disabled="true" />
  				</row>
				<row>
  						<label value="Wochenrythmus" />
  						<textbox id="lfvt_detail_textbox_wochenrythmus" disabled="true"/>

  						<label value="Start KW" />
  						<textbox id="lfvt_detail_textbox_startkw" disabled="true"/>
  				</row>
				<row>
  						<label value="Studiensemester" />
  						<vbox>							
							<menulist id="lfvt_detail_menulist_studiensemester" disabled="true"
									  datasources="<?php echo APP_ROOT ?>rdf/studiensemester.rdf.php" flex="0"
							          ref="http://www.technikum-wien.at/studiensemester/liste" >
								<template>
									<menupopup>
										<menuitem value="rdf:http://www.technikum-wien.at/studiensemester/rdf#kurzbz"
										          label="rdf:http://www.technikum-wien.at/studiensemester/rdf#kurzbz"
												  uri="rdf:*"/>
									</menupopup>
								</template>
							</menulist>
							<spacer flex="1"/>
						</vbox>
						<label value="Anmerkung" />
  						<textbox id="lfvt_detail_textbox_anmerkung" rows="2" multiline="true" disabled="true"/>
				</row>
				<row>					
					<!-- ************* GRUPPEN ************** -->
					<label value="Gruppen" />
					<vbox height="150" flex="1">
						<hbox flex="1">
							<tree id="lfvt_detail_tree_lehreinheitgruppe" seltype="single" hidecolumnpicker="false" flex="1" disabled="true"
									datasources="rdf:null"
									ref="http://www.technikum-wien.at/lehreinheitgruppe/liste"
									flags="dont-build-content"
									style="border: 1px solid black;"
        							ondragdrop="lfvt_detail_gruppe_dragdrop(event);"
							        ondragover="return lfvt_detail_gruppe_dragover(event);" 
									contextmenu="lfvt_detail_gruppe_tree_popup"
									ondragexit="debug('ondragexit');" 
							>
								<treecols>
									<treecol id="lfvt_detail_tree_lehreinheitgruppe-col-bezeichnung" label="Bezeichnung" flex="2" hidden="false"
								    	class="sortDirectionIndicator"
								    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#bezeichnung" />
								    <splitter class="tree-splitter"/>
									<treecol id="lfvt_detail_tree_lehreinheitgruppe-col-lehreinheitgruppe_id" label="ID" flex="2" hidden="true"
								    	class="sortDirectionIndicator"
								    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#lehreinheitgruppe_id" />
								</treecols>
								<template>
									<rule>
										<treechildren>
											<treeitem uri="rdf:*">
												<treerow>
													<treecell label="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#bezeichnung"   />
													<treecell label="rdf:http://www.technikum-wien.at/lehreinheitgruppe/rdf#lehreinheitgruppe_id"   />
								 				</treerow>
								 			</treeitem>
								 		</treechildren>
								 	</rule>
							  	</template>							
							</tree>
							<spacer />
						</hbox>
						
						<hbox>
							<!--<button label="+" id="lfvt_detail_gruppe_button_add" oncommand="lfvt_LehreinheitGruppeAdd()" style="max-width: 30px;" disabled="true"/>-->
							<!--<button label="-" id="lfvt_detail_gruppe_button_del" oncommand="lfvt_LehreinheitGruppeDel()" style="max-width: 30px;" disabled="true"/>-->
						</hbox>
						
					</vbox>
				</row>
 			</rows>
		</grid>	
		
</vbox>

<!-- ************************** LEKTORZUTEILUNG ********************** -->
<vbox id="lfvt-lektorzuteilung">								
	<hbox flex="1" style="padding: 10px">
		<vbox width="250">
			<hbox flex="1">
				<tree id="lfvt_detail_tree_lehreinheitmitarbeiter" seltype="single" hidecolumnpicker="false" flex="1"
						datasources="rdf:null"
						ref="http://www.technikum-wien.at/lehreinheitmitarbeiter/liste"
						onselect="lfvt_LehreinheitMitarbeiterAuswahl();" flags="dont-build-content"
						style="border: 1px solid black;"
				>
					<treecols>
						<treecol id="lfvt_detail_tree_lehreinheitmitarbeiter-col-nachname" label="Nachname" flex="2" hidden="false"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#nachname" onclick="LehreinheitenTreeSort()"/>
					    <splitter class="tree-splitter"/>
						<treecol id="lfvt_detail_tree_lehreinheitmitarbeiter-col-vorname" label="Vorname" flex="2" hidden="false"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#vorname" onclick="LehreinheitenTreeSort()"/>
					    <splitter class="tree-splitter"/>		
					    <treecol id="lfvt_detail_tree_lehreinheitmitarbeiter-col-mitarbeiter_uid" label="MitarbeiterLehreinheitID" flex="2" hidden="true"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#mitarbeiter_uid" onclick="LehreinheitenTreeSort()"/>
					    <splitter class="tree-splitter"/>	
					    <treecol id="lfvt_detail_tree_lehreinheitmitarbeiter-col-lehreinheit_id" label="MitarbeiterLehreinheitID" flex="2" hidden="true"
					    	class="sortDirectionIndicator"
					    	sort="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#lehreinheit_id" onclick="LehreinheitenTreeSort()"/>
					    <splitter class="tree-splitter"/>					
					</treecols>
					<template>
						<rule>
							<treechildren>
								<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#nachname"   />
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#vorname"   />
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#mitarbeiter_uid"   />
										<treecell label="rdf:http://www.technikum-wien.at/lehreinheitmitarbeiter/rdf#lehreinheit_id"   />
					 				</treerow>
					 			</treeitem>
					 		</treechildren>
					 	</rule>
				  	</template>							
				</tree>
				<spacer />
			</hbox>
			
			<hbox>
				<button label="+" id="lfvt_lehreinheitmitarbeiter_button_add" oncommand="lfvt_LehreinheitMitarbeiterAdd()" style="max-width: 30px;" disabled="true"/>
				<button label="-" id="lfvt_lehreinheitmitarbeiter_button_del" oncommand="lfvt_LehreinheitMitarbeiterDel()" style="max-width: 30px;" disabled="true"/>
			</hbox>
			
		</vbox>
		
		<vbox>
		<hbox>
		<groupbox>
			<caption label="Lektorendaten" />
			<vbox flex="1">
			<textbox id="lfvt_lehreinheitmitarbeiter_textbox_lehreinheit_id" hidden="true"/>
			<checkbox id="lfvt_lehreinheitmitarbeiter_checkbox_new" hidden="true"/>
			<grid align="end" flex="1"
					 flags="dont-build-content"
					enableColumnDrag="true"
					>
				<columns>
					<column />
					<column flex="1"/>
					<column />
					<column flex="1"/>
				</columns>

				<rows>
					<row>
						<label align="end" control="lfvt_lehreinheitmitarbeiter_menulist_lehrfunktion_kurzbz" value="Lehrfunktion:"/>
						<menulist id="lfvt_lehreinheitmitarbeiter_menulist_lehrfunktion_kurzbz" disabled="true" oncommand="lfvt_LehreinheitMitarbeiterValueChanged();"
		    		          datasources="<?php echo APP_ROOT; ?>rdf/lehrfunktion.rdf.php"
				              ref="http://www.technikum-wien.at/lehrfunktion/liste" flex="1">
					         <template>
					            <menupopup>
					               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/lehrfunktion/rdf#lehrfunktion_kurzbz"
					                         value="rdf:http://www.technikum-wien.at/lehrfunktion/rdf#lehrfunktion_kurzbz"/>
					            </menupopup>
					         </template>
				   		 </menulist>
				   		 <label align="end" control="lfvt_lehreinheitmitarbeiter_menulist_lektor" value="Lektor:"/>
						<menulist id="lfvt_lehreinheitmitarbeiter_menulist_lektor" disabled="true" oncommand="lfvt_LehreinheitMitarbeiterValueChanged();"
	    		          datasources="<?php echo APP_ROOT; ?>rdf/mitarbeiter.rdf.php"
			              ref="http://www.technikum-wien.at/mitarbeiter/alle" flex="1">
				         <template>
				            <menupopup>
				               <menuitem uri="rdf:*" label="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#nachname rdf:http://www.technikum-wien.at/mitarbeiter/rdf#vorname"
				                         value="rdf:http://www.technikum-wien.at/mitarbeiter/rdf#uid"/>
				            </menupopup>
				         </template>
			   		 	</menulist>
					</row>											
			    	<row>
    					<label control="lfvt_lehreinheitmitarbeiter_textbox_semesterstunden" value="Semesterstunden: "/>
    					<textbox id="lfvt_lehreinheitmitarbeiter_textbox_semesterstunden" disabled="true" maxlength="3" flex="1" oninput="lfvt_LehreinheitMitarbeiterValueChanged();"/>
    					<label control="lfvt_lehreinheitmitarbeiter_textbox_planstunden" value="Planstunden: "/>
    					<textbox id="lfvt_lehreinheitmitarbeiter_textbox_planstunden" disabled="true" maxlength="3" flex="1" oninput="lfvt_LehreinheitMitarbeiterValueChanged();"/>
    				</row>
			    	<row>
			    		<label control="lfvt_lehreinheitmitarbeiter_textbox_stundensatz" value="Stundensatz: "/>
    					<textbox id="lfvt_lehreinheitmitarbeiter_textbox_stundensatz" disabled="true" maxlength="6" flex="1" oninput="lfvt_LehreinheitMitarbeiterValueChanged();"/>
    					<label control="lfvt_lehreinheitmitarbeiter_textbox_faktor" value="Faktor: "/>
    					<textbox id="lfvt_lehreinheitmitarbeiter_textbox_faktor" disabled="true" maxlength="3" flex="1" oninput="lfvt_LehreinheitMitarbeiterValueChanged();"/>
					</row>
					<row>
			    		<label control="lfvt_lehreinheitmitarbeiter_textbox_anmerkung" value="Anmerkung: "/>
    					<textbox id="lfvt_lehreinheitmitarbeiter_textbox_anmerkung" disabled="true" maxlength="256" flex="1" oninput="lfvt_LehreinheitMitarbeiterValueChanged();"/>
    					<label control="lfvt_lehreinheitmitarbeiter_checkbox_bismelden" value="BIS-Melden: "/>
    					<checkbox id="lfvt_lehreinheitmitarbeiter_checkbox_bismelden" disabled="true" flex="1" oninput="lfvt_LehreinheitMitarbeiterValueChanged();"/>
					</row>
    			</rows>
    			</grid>
    			<hbox flex="1">
    				<spacer flex="1" />
					<button label="Speichern" disabled="true" id="lfvt_lehreinheitmitarbeiter_save" oncommand="lfvt_LehreinheitMitarbeiterSave();"/>
				</hbox>
			</vbox>
		</groupbox>
		</hbox>
		</vbox>
		<spacer flex="1" />
	</hbox>		
</vbox>

</overlay>