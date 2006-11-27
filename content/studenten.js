// aktueller Student, der angezeigt wird
var currentStudent;

function studentAuswahl() {
	var tree = document.getElementById('treeStudenten');
	var items =	tree.selectedItems;
	
	//alert(tree.view.getCellText(tree.currentIndex,"uid"));
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
	dsource=dsource.QueryInterface(Components.interfaces.nsIRDFDataSource);	
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);
	var subject = rdfService.GetResource("http://www.technikum-wien.at/tempus/studenten/" + tree.view.getCellText(tree.currentIndex,"uid"));

	// zum debuggen; zeigt predicates an, die in resource zu finden sind
	/*
	var targets = dsource.ArcLabelsOut(subject);
	while (targets.hasMoreElements()){
	  var predicate = targets.getNext();
	  if (predicate instanceof Components.interfaces.nsIRDFResource){
		    var target = dsource.GetTarget(subject, predicate, true);

		    if (target instanceof Components.interfaces.nsIRDFResource){
		      alert("Resource is: " + target.Value);
		    }
		    else if (target instanceof Components.interfaces.nsIRDFLiteral){
		      alert("Literal is: " + target.Value + " predi="+ predicate.Value+ "subject="+subject.Value);
		    }
	  }
	}
	*/

	
	//
	var predicateNS = "http://www.technikum-wien.at/tempus/studenten/rdf";
	//var predicate = rdfService.GetResource( predicateNS + "#vornamen" );
	//var target = dsource.GetTarget(subject, predicate, true);
	var student = new Student();

	student.uid = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#uid" ));	
	student.titel = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#titel" ));	
	student.vornamen = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#vornamen" ));
	student.nachname = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#nachname" ));	
	student.geburtsdatum = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#geburtsdatum" ));	
	student.geburtsort = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#geburtsort" ));	
	student.geburtszeit = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#geburtszeit" ));	
	student.homepage = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#homepage" ));	
	student.email = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#email" ));	
	student.matrikelnummer = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#matrikelnummer" ));
	student.semester = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#semester" ));
	student.verband = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#verband" ));
	student.gruppe = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#gruppe" ));
	student.studiengang_kz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#studiengang_kz" ));
	student.stg_bezeichnung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#stg_bezeichnung" ));
	student.aktiv = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#aktiv" ));	
	
    student.show();
	currentStudent = student;

	/*
	if (dsource.hasArcOut(subject, predicate))  {		
		if (target instanceof Components.interfaces.nsIRDFLiteral) {
		      alert("Literal is: " + target.Value + " predi="+ predicate.Value+ "subject="+subject.Value);
		      student.vornamen = target.Value;
		      student.show();
		}
	} */	
	document.getElementById('std-label-anzahl').value="Anzahl: "+tree.view.rowCount;
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
 * Student Value Object
 */
function Student() {
	this.uid=null;
	this.matrikelnummer=null;
	this.titel=null;	
	this.vornamen=null;
	this.nachname=null;
	this.geburtsdatum=null;
	this.geburtsort=null;
	this.geburtszeit=null;
	this.homepage=null;
	this.email=null;
	this.semester=null;
	this.verband=null;
	this.gruppe=null;
	this.stg_bezeichnung=null;
	this.studiengang_kz=null;
	this.aktiv=null;
	// flag, welches angibt, ob Daten verändert wurden
	this.dataChanged=false;
}

/**
 * Daten aus Formular holen und evt. speichern
 */
Student.prototype.updateData = function() {
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
Student.prototype.show = function() {
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