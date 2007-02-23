
function getDropDownValue(obj) {
	//var list = document.getElementById(obj.name);
	//var selectedText = list.selectedItem.label;
	//alert(selectedText);
	return obj.name;
}

function listElementHandlers(aObj) {
	if(!aObj)
       return null;
    for(var list in aObj)
       if(list.match(/^on/))
         dump(list+'\n');
}

/**
 * neue LVA anlegen
 */
function lvaNeu() {
	var lvaDetail=document.getElementById('lvaDetail');
	lvaDetail.reset();
	var lva = new Lehrveranstaltung();
	lva.studiengang=currentAuswahl.stg_kz;
	lva.semester=currentAuswahl.sem;
	lva.verband=currentAuswahl.ver;
	lva.gruppe=currentAuswahl.grp;
	lva.einheit=currentAuswahl.einheit;
	lvaDetail.setLVA(lva);
	lvaDetail.isNew=true;
	//alert('stg_kz='+lva.stg_kz);
}

/**
 * neue LVA löschen
 */
function lvaDelete() {

	// id holen
	var lvaDetail=document.getElementById('lvaDetail');
	var id=lvaDetail.currentLVA.id;
	var lvnr=lvaDetail.currentLVA.lvnr;

	if (confirm('LVA '+lvnr+' wirklich löschen?')) {

		var details = document.getElementById('lvaDetail');
		details.reset();
		var req = new phpRequest('lfvtCUD.php','pam','pam');
		req.add('do','delete');
		req.add('lehrveranstaltung_id',id);
		var response = req.execute();
		if (response!='ok') alert(response);

		currentLVA_id=id;

		// RDF aktualisieren (=Datensatz aus Tree entfernen)
		var tree=document.getElementById('treeLFVT');

		if (tree.currentIndex==-1) return;


		/*
		// aus dem RDF loeschen

		// Datasource holen
		var dsource;
		// Trick 17	(sonst gibt's ein Permission denied)
		try {
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		} catch(e) {
			alert(e);
			return;
		}


		var sources=tree.database.GetDataSources();
		if (sources.hasMoreElements()){
    		dsource=sources.getNext();
		}

		*/

		// refresh
		/* funktioniert zwar, ist aber unpraktisch, weil langsam und außerdem werden die Aeste vom Tree geschlossen
		dsource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource).Refresh(true);
		tree.builder.rebuild( );
		return;
		*/

		/*
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

		var subRes = rdfService.GetResource("http://www.technikum-wien.at/tempus/lva/" + id);

		var rootSubject = rdfService.GetResource("http://www.technikum-wien.at/tempus/lva/liste");


		var container = Components.classes["@mozilla.org/rdf/container;1"].
                  createInstance(Components.interfaces.nsIRDFContainer);
		try {
			container.Init(dsource, rootSubject);
			//alert(container);
			//container.AppendElement(christaRes);
		}
		catch (ex){}

		var rdfContainerUtils = Components.classes["@mozilla.org/rdf/container-utils;1"].
                          getService(Components.interfaces.nsIRDFContainerUtils);


		if (rdfContainerUtils.IsContainer(dsource,subRes)) {
			alert(' is container');
		} else {

			// -> partizipierende LVA
			// parent suchen

			var parent = null;

			var arcsIn = dsource.ArcLabelsIn(subRes);
			while (arcsIn.hasMoreElements()){
				var arc = arcsIn.getNext();
				if (arc instanceof Components.interfaces.nsIRDFResource){
					if (rdfContainerUtils.IsOrdinalProperty(arc)){
						parent = dsource.GetSource(arc, subRes, true);
						break;
					}
				}
			}

			if (rdfContainerUtils.IsContainer(dsource,parent)) {
				alert('parent is container '+parent.Value);

				var idx = rdfContainerUtils.indexOf(dsource,parent,subRes);
				alert('index='+idx);

				var container = Components.classes["@mozilla.org/rdf/container;1"].
                  createInstance(Components.interfaces.nsIRDFContainer);

				try {
					container.Init(dsource, parent);
					var twoRes = rdfContainerUtils.IndexToOrdinalResource(2);
					alert("count="+container.GetCount()+"; twoRes="+twoRes.Value);
					// geht nicht bei remote RDF
					//container.RemoveElementAt(2,true);
				} catch(ex) {alert(ex);}


			}

		}




		//predicate = rdfService.GetResource( "id" ); // RDF.GetResource('http://books.mozdev.org/rdf#chapters');
		//object = dsource.GetTarget(rootSubject,predicate,true);




		//datasource.Mark(rootSubject,predicate,object,true);
		//datasource.Sweep( );

		*/

		// löscht nur aus dem view!
		try {
			//alert('currentIndex'+tree.currentIndex);
			var selected = tree.treeBoxObject.view.getItemAtIndex(tree.currentIndex);
			var cells = selected.getElementsByTagName( "treerow" );
			var id = cells[ 0 ].getAttribute( "dbID" );
			// ids müssten identisch sein (ist nur ein Sicherheitscheck)
			if (id==currentLVA_id) {
				var parent = tree.view.getItemAtIndex(tree.currentIndex).parentNode;
				parent.removeChild(selected);
			}

		} catch(e) {
			alert(e);
			return false;
		}



		// Datasource holen
		var dsource;
		// Trick 17	(sonst gibt's ein Permission denied)
		try {
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		} catch(e) {
			alert(e);
			return;
		}

	}
}

/**
 * neue LVA als Partizipierung anlegen
 */
function lvaNeuPart() {
	// ausgewählte LVA holen
	var tree = document.getElementById('treeLFVT');

	if (tree.currentIndex==-1) return;
	try {
		var unr = tree.view.getCellText(tree.currentIndex, "lvaUnr")
		//alert('currentIndex'+tree.currentIndex);
		// level (0 oder 1?)
        var level=tree.view.getLevel(tree.currentIndex);
		//alert("unr="+unr+"; level="+tree.view.getLevel(tree.currentIndex));
		if (level==1) {
			var parent = tree.view.getItemAtIndex(c).parentNode;
			parent.addChild();
		}
		tree.builder.rebuild( );
        //parent.removeChild(selected);

	} catch(e) {
		alert(e);
		return false;
	}
	// unr holen

	// Datensatz anlegen
	var details = document.getElementById('lvaDetail');
	details.reset();
}

/**
 * Gibt eine Message auf die Javascript Console aus
 */
function debug(msg)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	 var consoleService = Components.classes["@mozilla.org/consoleservice;1"]
                                 .getService(Components.interfaces.nsIConsoleService);
    consoleService.logStringMessage(msg);
}

function lvaAuswahl() 
{
	var tree = document.getElementById('treeLFVT');

	if (tree.currentIndex==-1) return;
	try 
	{
        var col = tree.columns ? tree.columns["lva_lehreinheit_id"] : "lva_lehreinheit_id";
		var lehreinheit_id=tree.view.getCellText(tree.currentIndex,col);
		if(lehreinheit_id=='')
			return false;
		var col = tree.columns ? tree.columns["lva_lehrveranstaltung_id"] : "lva_lehrveranstaltung_id";
		var lehrveranstaltung_id=tree.view.getCellText(tree.currentIndex,col);

		if(lehrveranstaltung_id=='')
			return false;

	}
	catch(e) 
	{
		alert(e);
		return false;
	}

	// Datasource holen
	var dsource;
	
	// Trick 17	(sonst gibt's ein Permission denied)
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	var sources=tree.database.GetDataSources();
	if (sources.hasMoreElements())
	{
    	dsource=sources.getNext();
	}
	
	dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/tempus/lva/"+lehrveranstaltung_id+"/" + lehreinheit_id);


	//
	var predicateNS = "http://www.technikum-wien.at/tempus/lva/rdf";

	// debug

	var predicate = rdfService.GetResource( predicateNS + "#lvnr" );

	//
	var lva = new Lehrveranstaltung();

	//lva.id = getTargetHelper(dsource,subject,rdfService.GetResource( "lehrveranstaltung_id" ));
	lva.unr = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#unr" ));
	lva.lvnr=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lvnr" ));
	lva.sprache=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#sprache" ));
	lva.lehrveranstaltung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehrveranstaltung_id" ));
	lva.lehrfach=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehrfach_id" ));
	lva.raumtyp=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#raumtyp" ));
	lva.raumtyp_alt=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#raumtypalternativ" ));
	lva.lehre=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehre" ));
	lva.stundenblockung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#stundenblockung" ));
	lva.wochenrythmus=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#wochenrythmus" ));
	lva.start_kw=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#startkw" ));
	lva.anmerkung=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#anmerkung" ));
	lva.studiensemester=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiensemester_kurzbz" ));
	lva.lehrform=getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#lehrform_kurzbz" ));

	currentLVA = lva;
	
	var lvaDetail=document.getElementById('lvaDetail');
	lvaDetail.setLVA(lva);
	lvaDetail.isNew=false;
}

function getTargetHelper(dsource,subj,predi) {
	if (dsource.hasArcOut(subj, predi))  {
		var target = dsource.GetTarget(subj, predi, true);
		if (target instanceof Components.interfaces.nsIRDFLiteral) {
			return target.Value;
		}
	}
	return "";
}




/**
 * Lehrveranstaltung Value Object
 */
function Lehrveranstaltung() {
	this.id=null;   // =lehrveranstaltung_id
	this.lvnr=null;
	this.unr=null;
	this.einheit=null;
	this.lektor=null;
	this.lehrfach=null;
	this.studiengang=null;
	this.fachbereich=null;
	this.semester=null;
	this.verband=null;
	this.gruppe=null;
	this.raumtyp=null;
	this.raumtyp_alt=null;
	this.semesterstunden=null;
	this.stundenblockung=null;
	this.wochenrythmus=null;
	this.start_kw=null;
	this.studiensemester=null;
	this.lehrform=null;
	this.ects=null;
	// flag, welches angibt, ob Daten verändert wurden
	this.dataChanged=false;
}

/**
 * Daten aus Formular holen und evt. speichern
 */
Lehrveranstaltung.prototype.updateData = function() {
	if (document.getElementById('gridStudentenUID').value!=this.uid) {
		this.uid = document.getElementById('gridStudentenUID').value;
		this.dataChanged = true;
	};
	if (document.getElementById('gridStudentenTitel').value!=this.titel) {
		this.titel = document.getElementById('gridStudentenTitel').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenVornamen').value!=this.vornamen) {
		this.vornamen = document.getElementById('gridStudentenVornamen').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenNachname').value!=this.nachname) {
		this.nachname = document.getElementById('gridStudentenNachname').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenMatrikelnummer').value!=this.matrikelnummer)  {
		this.matrikelnummer = document.getElementById('gridStudentenMatrikelnummer').value;
		this.dataChanged = true;
	}
	//alert(document.getElementById('gridStudentenMatrikelnummer').value);
	if (document.getElementById('gridStudentenGeburtsdatum').value!=this.geburtsdatum) {
		// todo validation
		this.geburtsdatum = document.getElementById('gridStudentenGeburtsdatum').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenGeburtsort').value!=this.geburtsort) {
		this.geburtsort = document.getElementById('gridStudentenGeburtsort').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenGeburtszeit').value!=this.geburtszeit) {
		// todo validation
		this.geburtszeit = document.getElementById('gridStudentenGeburtszeit').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenHomepage').value!=this.homepage) {
		this.homepage = document.getElementById('gridStudentenHomepage').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenEmail').value!=this.email) {
		this.email = document.getElementById('gridStudentenEmail').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenSemester').value!=this.semester) {
		this.semester = document.getElementById('gridStudentenSemester').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenVerband').value!=this.verband) {
		this.verband = document.getElementById('gridStudentenVerband').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridStudentenGruppe').value!=this.gruppe) {
		this.gruppe = document.getElementById('gridStudentenGruppe').value;
		this.dataChanged = true;
	}
	if (document.getElementById('gridLehrform').value!=this.lehrform) {
		this.lehrform = document.getElementById('gridLehrform').value;
		this.dataChanged = true;
	}
	if (!((document.getElementById('gridStudentenAktiv').checked && this.aktiv=='True') ||
		(!document.getElementById('gridStudentenAktiv').checked && this.aktiv=='False'))) {
		this.aktiv = document.getElementById('gridStudentenAktiv').checked?'True':'False';
		this.dataChanged = true;
	}
	alert(this.dataChanged?'dataChanged':'nix changed');
}

/**
 * Student anzeigen
 */
Lehrveranstaltung.prototype.show = function() {
	document.getElementById('gridStudentenUID').value = this.uid;
	document.getElementById('gridStudentenTitel').value = this.titel;
	document.getElementById('gridStudentenVornamen').value = this.vornamen;
	document.getElementById('gridStudentenNachname').value = this.nachname;
	document.getElementById('gridStudentenMatrikelnummer').value = this.matrikelnummer;
	document.getElementById('gridStudentenGeburtsdatum').value = this.geburtsdatum;
	document.getElementById('gridStudentenGeburtsort').value = this.geburtsort;
	document.getElementById('gridStudentenGeburtszeit').value = this.geburtszeit;
	document.getElementById('gridStudentenHomepage').value = this.homepage;
	document.getElementById('gridStudentenEmail').value = this.email;
	document.getElementById('gridStudentenSemester').value = this.semester;
	document.getElementById('gridStudentenVerband').value = this.verband;
	document.getElementById('gridStudentenGruppe').value = this.gruppe;
	document.getElementById('gridStudentenStgBezeichnung').value = this.stg_bezeichnung;
	document.getElementById('gridStudentenAktiv').checked = (this.aktiv=='True'?true:false);
}



