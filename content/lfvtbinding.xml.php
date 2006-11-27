<?php
	include('../vilesci/config.inc.php');
	header("Content-type: application/vnd.mozilla.xul+xml");
	echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';
?>
<bindings xmlns="http://www.mozilla.org/xbl"
          xmlns:xul="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
		  xmlns:xbl="http://www.mozilla.org/xbl"
		  xmlns:html="http://www.w3.org/1999/xhtml"
		  >


  <binding id="stplDetail">
    <content>

	<xul:vbox style="margin:0px;padding:0px;" flex="1">

		<xul:hbox style="background:#eeeeee;margin:0px;padding:2px" flex="0">
			<xul:label value="Details" style="font-size:12pt;font-weight:bold;margin-top:5px;"  flex="1" />
			<xul:button id="btnLFVTSave" label="speichern" oncommand="parentNode.parentNode.parentNode.saveData();"/>
		</xul:hbox>



		<xul:grid id="gridLFVT" flex="1" datasources="rdf:null"
			ref="http://www.technikum-wien.at/tempus/lva/liste"
			style="padding:0px;"
			>
  			<xul:columns  >
				<xul:column  />
  			</xul:columns>
  			<xul:rows>
			    <xul:row>
				<xul:box id="stplDetailRow1" class="stplDetailRow" />
			    </xul:row>
			    <xul:row>
				<xul:box id="stplDetailRow2" class="stplDetailRow" />
			    </xul:row>
  			</xul:rows>
	    </xul:grid>

	  </xul:vbox>

	</content>
  </binding>



  <binding id="stplDetailRow">
    <resources>
    	<stylesheet src="lfvt.css" />
    </resources>

    <content>

		<xul:grid id="gridLFVT" flex="1" datasources="rdf:null"
			ref="http://www.technikum-wien.at/tempus/lva/liste"
			style="padding:0px;border:1px solid #000000"
			>
  			<xul:columns  >
				<xul:column  />
				<xul:column style="min-width:240px" />
				<xul:column />
				<xul:column style="min-width:240px" />
  			</xul:columns>
  			<xul:rows>
			<!-- fehlt hier die eindeutige ID ? -->
				<xul:row >
  						<xul:label value="Datum" />
  						<xul:textbox id="gridSTPLDatum" maxlength="20"  onchange="document.getBindingParent(this).lvnr=this.value"/>

  						<xul:label value="Stunde" />
  	    				<xul:textbox id="gridSTPLStunde" onchange="document.getBindingParent(this).unr=this.value;" />
				</xul:row>


				<xul:row >
  						<xul:label value="LVNR" />
  						<xul:textbox id="gridSTPLLVNR" maxlength="20"  onchange="document.getBindingParent(this).lvnr=this.value"/>

  						<xul:label value="UNR" />
  	    				<xul:textbox id="gridSTPLNR" onchange="document.getBindingParent(this).unr=this.value;" />
				</xul:row>
				<xul:row>
  						<xul:label value="Einheit" />
						<xul:customMenulist id="gridSTPLEinheit" class="einheit" oncommand="document.getBindingParent(this).einheit=document.getAnonymousNodes(this)[0].value" />

  						<xul:label value="Lektor" />
						<xul:customMenulist id="gridSTPLLektor" class="lektor" flex="1" oncommand="document.getBindingParent(this).lektor=document.getAnonymousNodes(this)[0].value" />
				</xul:row>
				<xul:row>
  						<xul:label value="Lehrfach" />
						<xul:customMenulist id="gridSTPLLehrfach" class="lehrfach" oncommand="document.getBindingParent(this).lehrfach=document.getAnonymousNodes(this)[0].value" />

  						<xul:label value="Studiengang" />
  						<!--<xul:menulist id="gridLFVTStudiengang" name="stgListe" class="studiengang" maxwidth="100px"  /> -->
						<xul:customMenulist id="gridSTPLStudiengang" class="studiengang" oncommand="document.getBindingParent(this).studiengang=document.getAnonymousNodes(this)[0].value" />
  	  			</xul:row>
				<xul:row>
  						<xul:label value="Fachbereich" />
						<xul:customMenulist id="gridSTPLFachbereich" class="fachbereich" oncommand="document.getBindingParent(this).fachbereich=document.getAnonymousNodes(this)[0].value" />

  						<xul:label value="Semester" />
  						<xul:textbox id="gridSTPLSemester" flex="1" onchange="document.getBindingParent(this).semester=this.value" />
				</xul:row>
				<xul:row>
  						<xul:label value="Verband" />
  	    				<xul:textbox id="gridSTPLVerband" flex="1" onchange="document.getBindingParent(this).verband=this.value" />

  						<xul:label value="Gruppe" />
  						<xul:textbox id="gridSTPLGruppe" flex="1" onchange="document.getBindingParent(this).gruppe=this.value" />
    			</xul:row>
				<xul:row>
  	    				<xul:label value="Raum" />
  						<xul:customMenulist id="gridSTPLRaum" class="raum" flex="1" oncommand="document.getBindingParent(this).raumtyp=document.getAnonymousNodes(this)[0].value" />


  				</xul:row>

  			</xul:rows>
		</xul:grid>



    </content>
  </binding>





  <binding id="lfvtDetail">
    <content>

	<xul:vbox style="margin:0px;padding:0px;" flex="1">

		<xul:hbox style="background:#eeeeee;margin:0px;padding:2px" flex="1">
			<xul:label value="Details" style="font-size:12pt;font-weight:bold;margin-top:5px;"  flex="1" />
			<xul:button id="btnLFVTSave" label="speichern" oncommand="parentNode.parentNode.parentNode.saveData();"/>
		</xul:hbox>

		<xul:grid id="gridLFVT" flex="1" datasources="rdf:null"
			ref="http://www.technikum-wien.at/tempus/lva/liste"
			style="padding:5px;"
			>
  			<xul:columns  >
				<xul:column  />
				<xul:column style="min-width:240px" />
				<xul:column />
				<xul:column style="min-width:240px" />
  			</xul:columns>
  			<xul:rows>
			<!-- fehlt hier die eindeutige ID ? -->
				<xul:row >
  						<xul:label value="LVNR" />
  						<xul:textbox id="gridLFVTLVNR" maxlength="20"  onchange="document.getBindingParent(this).lvnr=this.value"/>

  						<xul:label value="UNR" />
  	    				<xul:textbox id="gridLFVTUNR" onchange="document.getBindingParent(this).unr=this.value;" />
				</xul:row>
				<xul:row>
  						<xul:label value="Einheit" />
						<xul:customMenulist id="gridLFVTEinheit" class="einheit" oncommand="document.getBindingParent(this).einheit=document.getAnonymousNodes(this)[0].value" />

  						<xul:label value="Lektor" />
						<xul:customMenulist id="gridLFVTLektor" class="lektor" flex="1" oncommand="document.getBindingParent(this).lektor=document.getAnonymousNodes(this)[0].value" />
				</xul:row>
				<xul:row>
  						<xul:label value="Lehrfach" />
						<xul:customMenulist id="gridLFVTLehrfach" class="lehrfach" oncommand="document.getBindingParent(this).lehrfach=document.getAnonymousNodes(this)[0].value" />

  						<xul:label value="Studiengang" />
  						<!-- <xul:menulist id="gridLFVTStudiengang" name="stgListe" class="studiengang" maxwidth="100px"  /> -->
						<xul:customMenulist id="gridLFVTStudiengang" class="studiengang" oncommand="document.getBindingParent(this).studiengang=document.getAnonymousNodes(this)[0].value" />
  	  			</xul:row>
				<xul:row>
  						<xul:label value="Fachbereich" />
						<xul:customMenulist id="gridLFVTFachbereich" class="fachbereich" oncommand="document.getBindingParent(this).fachbereich=document.getAnonymousNodes(this)[0].value" />

  						<xul:label value="Semester" />
  						<xul:textbox id="gridLFVTSemester" flex="1" onchange="document.getBindingParent(this).semester=this.value" />
				</xul:row>
				<xul:row>
  						<xul:label value="Verband" />
  	    				<xul:textbox id="gridLFVTVerband" flex="1" onchange="document.getBindingParent(this).verband=this.value" />

  						<xul:label value="Gruppe" />
  						<xul:textbox id="gridLFVTGruppe" flex="1" onchange="document.getBindingParent(this).gruppe=this.value" />
    			</xul:row>
				<xul:row>
  	    				<xul:label value="Raumtyp" />
  						<xul:customMenulist id="gridLFVTRaumtyp" class="raumtyp" flex="1" oncommand="document.getBindingParent(this).raumtyp=document.getAnonymousNodes(this)[0].value" />

  						<xul:label value="Raumtyp alternativ" />
  						<xul:customMenulist id="gridLFVTRaumtypAlt" class="raumtyp" flex="1" oncommand="document.getBindingParent(this).raumtyp_alt=document.getAnonymousNodes(this)[0].value" />

  				</xul:row>
				<xul:row>

   	   					<xul:label value="Semesterstunden" />
  						<xul:textbox id="gridLFVTSemesterstunden" onchange="document.getBindingParent(this).semesterstunden=this.value"/>

  						<xul:label value="Stundenblockung" />
  						<xul:textbox id="gridLFVTStundenblockung" onchange="document.getBindingParent(this).stundenblockung=this.value" />
  				</xul:row>
				<xul:row>
  						<xul:label value="Wochenrythmus" />
  						<xul:textbox id="gridLFVTWochenrythmus" onchange="document.getBindingParent(this).wochenrythmus=this.value" />

  						<xul:label value="Start KW" />
  						<xul:textbox id="gridLFVTStart_kw" onchange="document.getBindingParent(this).start_kw=this.value" />
  				</xul:row>
				<xul:row>
  						<xul:label value="Studiensemester" />
  						<xul:textbox id="gridLFVTStudiensemester" onchange="document.getBindingParent(this).studiensemester=this.value" />

						<xul:label value="Anmerkung" />
  						<xul:textbox id="gridLFVTAnmerkung" onchange="document.getBindingParent(this).anmerkung=this.value" />
  						<!--
  						<xul:label value="ECTS" />
  						<xul:textbox id="gridLFVTECTS" onchange="parentNode.parentNode.parentNode.parentNode.ects=this.value" />
  						-->
				</xul:row>
				<xul:row>
  	    				<xul:label value="Lehrform" />
  						<xul:customMenulist id="gridLFVTLehrform" class="lehrform" flex="1" oncommand="document.getBindingParent(this).lehrform=document.getAnonymousNodes(this)[0].value" />
  				</xul:row>
  			</xul:rows>
		</xul:grid>

	</xul:vbox>

	</content>
	<implementation>
		<constructor>
			this.gridLFVTLVNR=document.getElementById('gridLFVTLVNR');
			this.gridLFVTUNR=document.getElementById('gridLFVTUNR');
			this.gridLFVTEinheit=document.getElementById('gridLFVTEinheit');
			this.gridLFVTLektor=document.getElementById('gridLFVTLektor');
			this.gridLFVTLehrfach=document.getElementById('gridLFVTLehrfach');
			this.gridLFVTStudiengang=document.getElementById('gridLFVTStudiengang');
			this.gridLFVTFachbereich=document.getElementById('gridLFVTFachbereich');
			this.gridLFVTSemester=document.getElementById('gridLFVTSemester');
			this.gridLFVTVerband=document.getElementById('gridLFVTVerband');
			this.gridLFVTGruppe=document.getElementById('gridLFVTGruppe');
			this.gridLFVTRaumtyp=document.getElementById('gridLFVTRaumtyp');
			this.gridLFVTRaumtypAlt=document.getElementById('gridLFVTRaumtypAlt');
			this.gridLFVTSemesterstunden=document.getElementById('gridLFVTSemesterstunden');
			this.gridLFVTStundenblockung=document.getElementById('gridLFVTStundenblockung');
			this.gridLFVTWochenrythmus=document.getElementById('gridLFVTWochenrythmus');
			this.gridLFVTStart_kw=document.getElementById('gridLFVTStart_kw');
			this.gridLFVTAnmerkung=document.getElementById('gridLFVTAnmerkung');
			this.gridLFVTStudiensemester=document.getElementById('gridLFVTStudiensemester');
			this.gridLFVTLehrform=document.getElementById('gridLFVTLehrform');
			<!-- this.gridLFVTECTS=document.getElementById('gridLFVTECTS'); -->
		</constructor>
		<!-- Grid-Felder -->
		<field name="gridLFVTLVNR">null</field>
		<field name="gridLFVTUNR">null</field>
		<field name="gridLFVTEinheit">null</field>
		<field name="gridLFVTLektor">null</field>
		<field name="gridLFVTLehrfach">null</field>
		<field name="gridLFVTStudiengang">null</field>
		<field name="gridLFVTFachbereich">null</field>
		<field name="gridLFVTSemester">null</field>
		<field name="gridLFVTVerband">null</field>
		<field name="gridLFVTGruppe">null</field>
		<field name="gridLFVTRaumtyp">null</field>
		<field name="gridLFVTRaumtypAlt">null</field>
		<field name="gridLFVTSemesterstunden">null</field>
		<field name="gridLFVTStundenblockung">null</field>
		<field name="gridLFVTWochenrythmus">null</field>
		<field name="gridLFVTStart_kw">null</field>
		<field name="gridLFVTAnmerkung">null</field>
		<field name="gridLFVTStudiensemester">null</field>
		<field name="gridLFVTLehrform">null</field>
		<!-- <field name="gridLFVTECTS">null</field> -->
		<!-- neuer Datensatz -->
		<field name="_new">false</field>
		<!-- aktuelles Value Objekt der Lehrveranstaltung -->
		<field name="currentLVA">null</field>
		<!-- neuer Datensatz -->
		<property name="isNew" onget="return this._new" onset="this._new=val;return val;" />
		<!-- LVA-Felder -->
		<property name="lvnr" onget="return this.currentLVA.lvnr;" >
			<setter>
				if (isNaN(val)) {
					alert('LVNR muss eine Zahl sein!');
					if (this.currentLVA!=null)
						this.currentLVA.lvnr=null;
				} else {
					if (this.currentLVA!=null)
						this.currentLVA.lvnr=val;
				}

				return val;
			</setter>
		</property>
		<property name="unr" onget="return this.currentLVA.unr;" >
			<setter>
				if (isNaN(val)) {
					alert('UNR muss eine Zahl sein!');
					if (this.currentLVA!=null)
						this.currentLVA.unr=null;
				} else {
					if (this.currentLVA!=null)
						this.currentLVA.unr=val;
				}
					alert('unr:'+val);
				return val;
			</setter>
		</property>
		<!-- einheit -->
		<property name="einheit" onget="return this.currentLVA.einheit;" >
			<setter>
				//alert('property-setter: val='+val);
				if (this.currentLVA!=null)
						this.currentLVA.einheit=val;
				//this.showCurrentLVA();

				return val;
			</setter>
		</property>
		<!-- lektor -->
		<property name="lektor" onget="return this.currentLVA.lektor;" >
			<setter>
				//alert('property-setter: val='+val);
				if (this.currentLVA!=null)
						this.currentLVA.lektor=val;
				//this.showCurrentLVA();

				return val;
			</setter>
		</property>
		<!-- lehrfach -->
		<property name="lehrfach" onget="return this.currentLVA.lehrfach;" >
			<setter>
				//alert('property-setter: val='+val);
				if (this.currentLVA!=null)
						this.currentLVA.lehrfach=val;
				//this.showCurrentLVA();

				return val;
			</setter>
		</property>
		<!-- studiengang -->
		<property name="studiengang" onget="return this.currentLVA.studiengang;" >
			<setter>
				//alert('property-setter: val='+val);
				if (this.currentLVA!=null)
						this.currentLVA.studiengang=val;
				//this.showCurrentLVA();

				return val;
			</setter>
		</property>
		<!-- fachbereich -->
		<property name="fachbereich" onget="return this.currentLVA.fachbereich;" >
			<setter>
				//alert('fachbereich-setter: val='+val);
				if (this.currentLVA!=null)
						this.currentLVA.fachbereich=val;
				//this.showCurrentLVA();

				return val;
			</setter>
		</property>
		<!-- raumtyp -->
		<property name="raumtyp" onget="return this.currentLVA.raumtyp;" >
			<setter>
				//alert('property-setter: val='+val);
				if (this.currentLVA!=null)
						this.currentLVA.raumtyp=val;
				//this.showCurrentLVA();
				
				return val;
			</setter>
		</property>
		<!-- raumtyp_alt -->
		<property name="raumtyp_alt" onget="return this.currentLVA.raumtyp_alt;" >
			<setter>
				//alert('property-setter: val='+val);
				if (this.currentLVA!=null)
						this.currentLVA.raumtyp_alt=val;
				//this.showCurrentLVA();

				return val;
			</setter>
		</property>

		<property name="semester" onget="return this.currentLVA.semester;" >
			<setter>
				if (isNaN(val)) {
					alert('Semester muss eine Zahl sein!');
					if (this.currentLVA!=null)
						this.currentLVA.unr=null;
				} else {
					if (this.currentLVA!=null)
						this.currentLVA.semester=val;
				}

				return val;
			</setter>
		</property>
		<property name="verband" onget="return this.currentLVA.verband;" >
			<setter>
				if (this.currentLVA!=null)
						this.currentLVA.verband=val;
				}

				return val;
			</setter>
		</property>
		<property name="gruppe" onget="return this.currentLVA.gruppe;" >
			<setter>
				if (isNaN(val)) {
					alert('Gruppe muss eine Zahl sein!');
					if (this.currentLVA!=null)
						this.currentLVA.gruppe=null;
				} else {
					if (this.currentLVA!=null)
						this.currentLVA.gruppe=val;
				}

				return val;
			</setter>
		</property>
		<property name="semesterstunden" onget="return this.currentLVA.semesterstunden;" >
			<setter>
				if (isNaN(val)) {
					alert('Semesterstunden muss eine Zahl sein!');
					if (this.currentLVA!=null)
						this.currentLVA.semesterstunden=null;
				} else {
					if (this.currentLVA!=null)
						this.currentLVA.semesterstunden=val;
				}

				return val;
			</setter>
		</property>
		<property name="stundenblockung" onget="return this.currentLVA.stundenblockung;" >
			<setter>
				if (isNaN(val)) {
					alert('Stundenblockung muss eine Zahl sein!');
					if (this.currentLVA!=null)
						this.currentLVA.stundenblockung=null;
				} else {
					if (this.currentLVA!=null)
						this.currentLVA.stundenblockung=val;
				}
				return val;
			</setter>
		</property>
		<property name="wochenrythmus" onget="return this.currentLVA.wochenrythmus;" >
			<setter>
				if (isNaN(val)) {
					alert('Wochenrythmus muss eine Zahl sein!');
					if (this.currentLVA!=null)
						this.currentLVA.wochenrythmus=null;
				} else {
					if (this.currentLVA!=null)
						this.currentLVA.wochenrythmus=val;
				}
				return val;
			</setter>
		</property>
		<property name="start_kw" onget="return this.currentLVA.start_kw;" >
			<setter>
				if (isNaN(val)) {
					alert('start_kw muss eine Zahl sein!');
					if (this.currentLVA!=null)
						this.currentLVA.start_kw=null;
				} else {
					if (this.currentLVA!=null)
						this.currentLVA.start_kw=val;
				}

				return val;
			</setter>
		</property>
		<property name="studiensemester" onget="return this.currentLVA.studiensemester;" >
			<setter>
				// todo: Validation Check
				
				if (this.currentLVA!=null)
						this.currentLVA.studiensemester=val;
				//this.showCurrentLVA();

				return val;
			</setter>
		</property>
		<property name="lehrform" onget="return this.currentLVA.lehrform;" >
			<setter>
				// todo: Validation Check
				
				if (this.currentLVA!=null)
						this.currentLVA.lehrform=val;
				return val;
			</setter>
		</property>
		<property name="anmerkung" onget="return this.currentLVA.anmerkung;" >
			<setter>
				if (this.currentLVA!=null)
						this.currentLVA.anmerkung=val;

				return val;
			</setter>
		</property>
		<!-- Daten speichern -->
		<method name="saveData">
			<body><![CDATA[
				var req = new phpRequest('lfvtCUD.php','pam','pam');
				if (this.isNew) {
					req.add('do','create');
				} else  {
					req.add('do','update');
				}
				req.add('lehrveranstaltung_id',this.currentLVA.id);
				if (this.currentLVA.unr!=null) req.add('unr',this.currentLVA.unr);
				if (this.currentLVA.lvnr!=null) req.add('lvnr',this.currentLVA.lvnr);
				if (this.currentLVA.einheit!=null) req.add('einheit_kurzbz',this.currentLVA.einheit);
				if (this.currentLVA.lektor!=null) req.add('lektor',this.currentLVA.lektor);
				if (this.currentLVA.lehrfach!=null) req.add('lehrfach_nr',this.currentLVA.lehrfach);
				if (this.currentLVA.studiengang!=null) req.add('studiengang_kz',this.currentLVA.studiengang);
				if (this.currentLVA.fachbereich!=null) req.add('fachbereich_id',this.currentLVA.fachbereich);
				if (this.currentLVA.semester!=null) req.add('semester',this.currentLVA.semester);
				if (this.currentLVA.verband!=null) req.add('verband',this.currentLVA.verband);
				if (this.currentLVA.gruppe!=null) req.add('gruppe',this.currentLVA.gruppe);
				if (this.currentLVA.raumtyp!=null) req.add('raumtyp',this.currentLVA.raumtyp);
				if (this.currentLVA.raumtyp_alt!=null) req.add('raumtypalternativ',this.currentLVA.raumtyp_alt);
				if (this.currentLVA.semesterstunden!=null) req.add('semesterstunden',this.currentLVA.semesterstunden);
				if (this.currentLVA.stundenblockung!=null) req.add('stundenblockung',this.currentLVA.stundenblockung);
				if (this.currentLVA.wochenrythmus!=null) req.add('wochenrythmus',this.currentLVA.wochenrythmus);
				if (this.currentLVA.start_kw!=null) req.add('start_kw',this.currentLVA.start_kw);
				if (this.currentLVA.studiensemester!=null) req.add('studiensemester_kurzbz',this.currentLVA.studiensemester);
				if (this.currentLVA.lehrform!=null) req.add('lehrform',this.currentLVA.lehrform);
				
				var response = req.execute();
				if (response!='ok') {
					alert(response);
				} else {
					if (this.currentLVA.isNew) this.currentLVA.isNew=false;
				}
				]]>
			</body>
		</method>
		<method name="showCurrentLVA">
			<body>
				alert('CurrentLVA: studiensemester='+this.currentLVA.studiensemester+'; einheit='+this.currentLVA.einheit);
			</body>
		</method>
		<!-- Felder zuruecksetzen -->
		<method name="reset">
			<body><![CDATA[
				this.gridLFVTLVNR.value=null;
				this.gridLFVTUNR.value=null;
				this.gridLFVTEinheit.currentValue=null;
				this.gridLFVTLektor.currentValue=null;
				this.gridLFVTLehrfach.currentValue=null;
				this.gridLFVTStudiengang.currentValue=null;
				this.gridLFVTFachbereich.currentValue=null;
				this.gridLFVTSemester.value=null;
				this.gridLFVTVerband.value=null;
				this.gridLFVTGruppe.value=null;
				this.gridLFVTRaumtyp.currentValue=null;
				this.gridLFVTRaumtypAlt.currentValue=null;
				this.gridLFVTSemesterstunden.value=null;
				this.gridLFVTStundenblockung.value=null;
				this.gridLFVTWochenrythmus.value=null;
				this.gridLFVTStart_kw.value=null;
				this.gridLFVTAnmerkung.value=null;
				this.gridLFVTStudiensemester.value=null;
				this.gridLFVTLehrform.value=null;
				this.currentLVA=null;
				]]>
			</body>
		</method>
		<!-- Value Objekt -->
		<method name="setLVA">
			<parameter name="lva"/>
			<body><![CDATA[
				// Value Object
				this.currentLVA=lva;
				//alert('unr='+this.currentLVA.unr);
				// LVNR
				this.gridLFVTLVNR.value=this.currentLVA.lvnr;
				// UNR
				this.gridLFVTUNR.value=this.currentLVA.unr;
				// Einheit
				this.gridLFVTEinheit.currentValue=this.currentLVA.einheit;
				// Lektor setzen
				this.gridLFVTLektor.currentValue=this.currentLVA.lektor;
				// Lehrfach
				this.gridLFVTLehrfach.currentValue=this.currentLVA.lehrfach;
				//alert(this.currentLVA.lehrfach);
				// Studiengang
				this.gridLFVTStudiengang.currentValue=this.currentLVA.studiengang;
				//alert('stg='+this.currentLVA.studiengang);
				// Fachbereich
				this.gridLFVTFachbereich.currentValue=this.currentLVA.fachbereich;
				// Semester
				this.gridLFVTSemester.value=this.currentLVA.semester;
				// Verband
				this.gridLFVTVerband.value=this.currentLVA.verband;
				// Gruppe
				this.gridLFVTGruppe.value=this.currentLVA.gruppe;
				// Raumtyp
				this.gridLFVTRaumtyp.currentValue=this.currentLVA.raumtyp;
				// Raumtyp alternativ
				this.gridLFVTRaumtypAlt.currentValue=this.currentLVA.raumtyp_alt;
				//alert('alt:'+this.currentLVA.raumtyp_alt);
				// semesterstunden
				this.gridLFVTSemesterstunden.value=this.currentLVA.semesterstunden;
				// stundenblockung
				this.gridLFVTStundenblockung.value=this.currentLVA.stundenblockung;
				// Wochenrythmus
				this.gridLFVTWochenrythmus.value=this.currentLVA.wochenrythmus;
				// Start KW
				this.gridLFVTStart_kw.value=this.currentLVA.start_kw;
				// Studiensemester
				this.gridLFVTStudiensemester.value=this.currentLVA.studiensemester;
				// Lehrform
				this.gridLFVTLehrform.currentValue=this.currentLVA.lehrform;
				
				
				
				// ECTS
				// ist bei lehrfach
				]]>
			</body>
		</method>
	</implementation>
  </binding>

  <binding id="studentDetail">
    <content>
		<xul:hbox flex="1">
			<xul:grid id="gridStudenten" style="overflow:auto;margin:4px;" flex="1" datasources="rdf:null" ref="http://www.technikum-wien.at/tempus/studenten/liste">
				  	<xul:columns  >
    					<xul:column flex="1"/>
    					<xul:column flex="5"/>
    					<xul:column flex="3"/>
  					</xul:columns>
  					<xul:rows>
    					<xul:row>
      						<xul:label value="UID" />
      						<xul:textbox id="gridStudentenUID" />
    					</xul:row>
    					<xul:row>
      						<xul:label value="Matrikelnummer" />
      						<xul:textbox id="gridStudentenMatrikelnummer" onchange="currentStudent.updateData();" />
    					</xul:row>
    					<xul:row>
      						<xul:label value="Titel" />
      						<xul:textbox id="gridStudentenTitel" />
    					</xul:row>
    					<xul:row>
      						<xul:label value="Vorname" />
      						<xul:textbox id="gridStudentenVornamen"  />
    					</xul:row>
    					<xul:row>
      						<xul:label value="Nachname" />
      						<xul:textbox id="gridStudentenNachname" />
    					</xul:row>
    					<xul:row>
      						<xul:label value="Geburtsdatum" />
      						<xul:textbox id="gridStudentenGeburtsdatum" />
    					</xul:row>
    					<xul:row>
      						<xul:label value="Geburtsort" />
      						<xul:textbox id="gridStudentenGeburtsort" />
    					</xul:row>
    					<xul:row>
      						<xul:label value="Geburtszeit" />
      						<xul:textbox id="gridStudentenGeburtszeit" />
    					</xul:row>
    					<xul:row>
      						<xul:label value="Homepage" />
      						<xul:textbox id="gridStudentenHomepage" />
    					</xul:row>
    					<xul:row>
      						<xul:label value="Email" />
      						<xul:textbox id="gridStudentenEmail" />
    					</xul:row>
    					<xul:row>
      						<xul:label value="Semester" />
      						<xul:textbox id="gridStudentenSemester" />
      						<xul:spacer flex="5" />
    					</xul:row>
    					<xul:row>
      						<xul:label value="Verband" />
      						<xul:textbox id="gridStudentenVerband" />
      						<xul:spacer flex="5" />
    					</xul:row>
    					<xul:row>
      						<xul:label value="Gruppe" />
      						<xul:textbox id="gridStudentenGruppe" />
      						<xul:spacer flex="5" />
    					</xul:row>
    					<xul:row>
      						<xul:label value="Studiengang" />
							<xul:customMenulist id="gridStudentenStgBezeichnung" class="studiengang" />
      						<xul:spacer flex="5" />
    					</xul:row>
    					<xul:row>
      						<xul:label value="Aktiv" />
      						<xul:checkbox id="gridStudentenAktiv" checked="true" />
      						<xul:spacer flex="5" />
    					</xul:row>
  					</xul:rows>

			</xul:grid>
		</xul:hbox>
	</content>

  </binding>


  <binding id="lfvtTree">
  	<content>
		<children/>

	</content>
  </binding>


  <!-- DropDownList fuer Fachbereiche -->

  <binding id="fachbereichListe"  extends="lfvtbinding.xml.php#customMenulist-base" >
  	<content>
		<xul:menulist datasources="fachbereich.rdf.php" flex="1"
		              ref="http://www.technikum-wien.at/tempus/fachbereich/liste" >
				<xul:template>
					<xul:menupopup>
							<!--<xul:menuitem value="null" label="-" />-->
							<xul:menuitem value="rdf:http://www.technikum-wien.at/tempus/fachbereich/rdf#id"
							              label="rdf:http://www.technikum-wien.at/tempus/fachbereich/rdf#bezeichnung"
										  uri="rdf:*"/>
					</xul:menupopup>
				</xul:template>
		</xul:menulist>
	</content>
	<handlers>
		<handler event="command" action="this.currentValue=document.getAnonymousNodes(this)[0].value;" />
	</handlers>
  </binding>
  
  <!-- DropDownList fuer Lehrform -->

  <binding id="lehrformListe"  extends="lfvtbinding.xml.php#customMenulist-base" >
  	<content>
		<xul:menulist datasources="../rdf/lehrform.rdf.php" flex="1"
		              ref="http://www.technikum-wien.at/lehrform/liste" >
				<xul:template>
					<xul:menupopup>
							<!--<xul:menuitem value="null" label="-" />-->
							<xul:menuitem value="rdf:http://www.technikum-wien.at/lehrform/rdf#kurzbz"
							              label="rdf:http://www.technikum-wien.at/lehrform/rdf#kurzbz"
										  uri="rdf:*"/>
					</xul:menupopup>
				</xul:template>
		</xul:menulist>
	</content>
	<handlers>
		<handler event="command" action="this.currentValue=document.getAnonymousNodes(this)[0].value;" />
	</handlers>
  </binding>
   <!-- DropDownList fuer Raumtyp -->

  <binding id="raumtypListe"  extends="lfvtbinding.xml.php#customMenulist-base" >
  	<content>
		<xul:menulist datasources="raumtyp.rdf.php" flex="1"
		              ref="http://www.technikum-wien.at/tempus/raumtyp/liste" >
  			<xul:template>
					<xul:menupopup>
							<!--<xul:menuitem value="null" label="-" />-->
							<xul:menuitem value="rdf:http://www.technikum-wien.at/tempus/raumtyp/rdf#kurzbz"
							              label="rdf:http://www.technikum-wien.at/tempus/raumtyp/rdf#kurzbz"
										  uri="rdf:*"/>
					</xul:menupopup>
				</xul:template>
		</xul:menulist>
	</content>
	<handlers>
		<handler event="command" action="this.currentValue=document.getAnonymousNodes(this)[0].value;" />
	</handlers>
  </binding>

  <!-- DropDownList fuer Orte -->

  <binding id="raumListe" extends="lfvtbinding.xml.php#customMenulist-base" >
  	<content>
		<xul:menulist datasources="<?php echo APP_ROOT; ?>rdf/ort.rdf.php" flex="1"
		              ref="http://www.technikum-wien.at/tempus/ort/alle-orte" >
  			<xul:template>
					<xul:menupopup>
							<!--<xul:menuitem value="null" label="-" />-->
							<xul:menuitem value="rdf:http://www.technikum-wien.at/tempus/ort/rdf#raumtyp"
							              label="rdf:http://www.technikum-wien.at/tempus/ort/rdf#raumtyp"
					  					  uri="rdf:*"/>
					</xul:menupopup>
				</xul:template>
		</xul:menulist>
	</content>
	<handlers>
		<handler event="command" action="this.currentValue=document.getAnonymousNodes(this)[0].value;" />
	</handlers>
  </binding>

<!-- DropDownList fuer Lektoren -->

  <binding id="lektorenListe"  extends="lfvtbinding.xml.php#customMenulist-base" >
  	<content>
		<xul:menulist datasources="mitarbeiter.rdf.php?lektor=1" flex="1"
		              ref="http://www.technikum-wien.at/tempus/mitarbeiter/alle" >
  			<xul:template>
					<xul:menupopup>
							<!--<xul:menuitem value="null" label="-" />-->
							<xul:menuitem value="rdf:http://www.technikum-wien.at/tempus/mitarbeiter/rdf#uid"
							              label="rdf:http://www.technikum-wien.at/tempus/mitarbeiter/rdf#kurzbz"
										  uri="rdf:*"/>
					</xul:menupopup>
				</xul:template>
		</xul:menulist>
	</content>
	<handlers>
		<handler event="command" action="this.currentValue=document.getAnonymousNodes(this)[0].value;" />
	</handlers>
	<!--
	<implementation>
		<property name="currentValue" onget="return this.getAttribute('currentValue');">
		   <setter><![CDATA[
		   	 this.setAttribute('currentValue',val);
			 var menulist=document.getAnonymousNodes(this)[0];
			 // auszuwaehlenden Datensatz suchen (stammt aus original Source Code)
			 var arr=menulist.menupopup.getElementsByAttribute('value',val);
			 //alert('anzahl arr='+arr.length+'; val='+val);
			 if (arr.item(0)) {
			 	menulist.selectedItem=arr[0];
			 } else {
			 	menulist.selectedIndex=0;
			 }
			 return val;
			 ]]>
		   </setter>
		 </property>
		<property name="selectedIndex"
		  onget="return document.getAnonymousNodes(this)[0].selectedIndex;"
		  onset="alert('val='+val);return document.getAnonymousNodes(this)[0].selectedIndex=val;"
		  />
	</implementation>
	<handlers>
		<handler event="command" action="this.currentValue=document.getAnonymousNodes(this)[0].getAttribute('value');" />
	</handlers>
	-->
  </binding>


<!-- DropDownList fuer Einheiten -->

  <binding id="einheitenListe" extends="lfvtbinding.xml.php#customMenulist-base" >
  	<content>

		<xul:menulist datasources="<?php echo APP_ROOT; ?>rdf/einheiten.rdf.php" flex="1"
		              ref="http://www.technikum-wien.at/tempus/einheiten/liste"
					   >
  			<xul:template>
					<xul:menupopup>
							<!--<xul:menuitem value="null" label="-" />-->
							<xul:menuitem value="rdf:http://www.technikum-wien.at/tempus/einheiten/rdf#kurzbz"
							              label="rdf:http://www.technikum-wien.at/tempus/einheiten/rdf#kurzbz"
										  uri="rdf:*"/>
					</xul:menupopup>
				</xul:template>
		</xul:menulist>
	</content>
	<handlers>
		<handler event="command" action="this.currentValue=document.getAnonymousNodes(this)[0].value;" />
	</handlers>
  </binding>


<!-- DropDownList fuer Lehrfaecher -->

  <binding id="lehrfaecherListe" extends="lfvtbinding.xml.php#customMenulist-base" >
  	<content>
		<xul:menulist datasources="lehrfach.rdf.php" flex="1"
		              ref="http://www.technikum-wien.at/tempus/lehrfach/liste"  >
  			<xul:template>
					<xul:menupopup>
							<!--<xul:menuitem value="null" label="-" />-->
							<xul:menuitem value="rdf:http://www.technikum-wien.at/tempus/lehrfach/rdf#lehrfach_nr"
							              label="rdf:http://www.technikum-wien.at/tempus/lehrfach/rdf#bezeichnung"
										  uri="rdf:*"/>
					</xul:menupopup>
				</xul:template>
		</xul:menulist>
	</content>
	<handlers>
		<handler event="command" action="this.currentValue=document.getAnonymousNodes(this)[0].value;" />
	</handlers>
  </binding>


   <!-- DropDownList fuer Studiengaenge -->

  <!--
     / extends="chrome://global/content/bindings/menulist.xml#menulist"
	 -->

  <binding id="stgListe"  extends="lfvtbinding.xml.php#customMenulist-base" >
  	<content>
		<xul:menulist id="stg" flex="1" datasources="studiengang.rdf.php"
		                       ref="http://www.technikum-wien.at/tempus/studiengang/liste" >
  			<xul:template>
					<xul:menupopup>
							<!--<xul:menuitem value="null" label="-" />-->
							<xul:menuitem value="rdf:http://www.technikum-wien.at/tempus/studiengang/rdf#studiengang_kz"
							              label="rdf:http://www.technikum-wien.at/tempus/studiengang/rdf#bezeichnung"
										  uri="rdf:*"/>
					</xul:menupopup>
			</xul:template>
		</xul:menulist>
	</content>
	<handlers>
		<handler event="command" action="this.currentValue=document.getAnonymousNodes(this)[0].value;" />
	</handlers>
  </binding>

  <!-- ************************************************************************* -->
  <!-- customMenulist-base                                                       -->
  <!-- 		- f�gt property currentValue hinzu, welcher dem value der internen   -->
  <!--	      Menulist entspricht, au�erdem wird durch setzen von currentValue   -->
  <!--        auch der entsprechende Datensatz selektiert                        -->
  <!--		- forward f�r selectedIndex   	                                     -->
  <!-- ************************************************************************* -->
  <binding id="customMenulist-base" >
  	<content />

	<implementation>
		<property name="currentValue" onget="return this.getAttribute('currentValue');">
		   <setter><![CDATA[
		   	 this.setAttribute('currentValue',val);
			 var menulist=document.getAnonymousNodes(this)[0];
			 // auszuwaehlenden Datensatz suchen (stammt aus original Source Code)
			 var arr=menulist.menupopup.getElementsByAttribute('value',val);
			 //alert('anzahl arr='+arr.length+'; val='+val);
			 if (arr.item(0)) {
			 	menulist.selectedItem=arr[0];
			 } else {
			 	menulist.selectedIndex=0;
			 }
			 return val;
			 ]]>
		   </setter>
		 </property>
		<property name="selectedIndex"
		  onget="return document.getAnonymousNodes(this)[0].selectedIndex;"
		  onset="return document.getAnonymousNodes(this)[0].selectedIndex=val;"
		  />
	</implementation>

  </binding>



</bindings>
