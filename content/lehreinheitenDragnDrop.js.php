<?php
require('../vilesci/config.inc.php');
?>
netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

/**
 * Wird zu Beginn einer DragnDrop Session aufgerufen.
 * Hier werden die Flayvour und die zu uebertragenden Daten
 * festgelegt.
 */
function treeDragGesture(event)
{
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect")
    var tree = document.getElementById('tree-liste-lehreinheiten')
    var row = { }
    var col = { }
    var child = { }
    
    //Index der Quell-Row ermitteln
    tree.treeBoxObject.getCellAt(event.pageX, event.pageY, row, col, child)
    
    //Wenn es keine Row ist sondern ein Header oder Scrollbar dann das DnD abbrechen
    if (!col.value) 
       	return false;
       	
    //Lehreinheit_id ermitteln    
    col = tree.columns ? tree.columns["tree-liste-lehreinheiten-col-lehreinheit_id"] : "tree-liste-lehreinheiten-col-lehreinheit_id";
	lehreinheit_id=tree.view.getCellText(row.value,col);
           
    var ds = Components.classes["@mozilla.org/widget/dragservice;1"].getService(Components.interfaces.nsIDragService);
    var trans = Components.classes["@mozilla.org/widget/transferable;1"].createInstance(Components.interfaces.nsITransferable);
    
    //Flavour anhaengen
    trans.addDataFlavor("lva");
    var textWrapper = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
    
    //Daten anhaengen
    textWrapper.data = lehreinheit_id;
    trans.setTransferData("lva", textWrapper, textWrapper.data.length*2);
    var transArray = Components.classes["@mozilla.org/supports-array;1"].createInstance(Components.interfaces.nsISupportsArray);
    transArray.AppendElement(trans);
    
    // Actually start dragging
    ds.invokeDragSession(event.target, transArray, null, ds.DRAGDROP_ACTION_COPY + ds.DRAGDROP_ACTION_MOVE);
    event.stopPropagation();
}

/**
 * Wird aufgerufen wenn Drag Event von Ausserhalb des eigenen Windows kommt
 */
function treeDragEnter(event) 
{
    //Not implemented
}

/**
 * Drag ueber ein Element
 */
function DragOverContentArea ( event )
{
  var validFlavor = false;
  var dragSession = null;

  var targetNode = event.target 
  	  	
  netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
  var dragService = Components.classes["@mozilla.org/widget/dragservice;1"].
                    getService().QueryInterface(Components.interfaces.nsIDragService);
  
  if( dragService ) 
  {
    dragSession = dragService.getCurrentSession();
    
    if( dragSession ) 
    {
		if( dragSession.isDataFlavorSupported("moz/toolbaritem") )
    		validFlavor = true;
    	else if ( dragSession.isDataFlavorSupported("lva") )
        	validFlavor = true;
      
		if ( validFlavor ) 
		{
	        //Style action	        
			//targetNode.style.backgroundColor = "red";
		 	//targetNode.style.color = "red";
		  	//event.originalTarget.style.color = "red";
	        dragSession.canDrop = true;
	        event.stopPropagation();
      	}
    }
  }
}

function treeDragExit(event) 
{
	//Not implemented
}

var dragservice_ds;
/**
 * Holt die Daten aus der DragSession
 */
function getDragData(aFlavourSet)
{
	debug('getdragdata in');
	/*try
	{
		var ds = Components.classes["@mozilla.org/widget/dragservice;1"].getService(Components.interfaces.nsIDragService);
	}
	catch(e)
	{
		debug('getDragData() in lehreinheitenDragnDrop.js.php hat folgenden Fehler verursacht: '+e);
	}
	*/
	var ds = dragservice_ds;
	var ses = ds.getCurrentSession()
	
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	
	var supportsArray = Components.classes["@mozilla.org/supports-array;1"]
    	.createInstance(Components.interfaces.nsISupportsArray);

  	for (var i = 0; i < ses.numDropItems; ++i)
    {
      	var trans = nsTransferable.createTransferable();
      	for (var j = 0; j < aFlavourSet.flavours.length; ++j)
        	trans.addDataFlavor(aFlavourSet.flavours[j].contentType);
      	ses.getData(trans, i);
      	supportsArray.AppendElement(trans);
    }
    debug('getdragdata out');
  	return supportsArray;
}

/**
 * (Wenn gedroppt wird)
 * Speichert die Partizipierung
 */
function treeDragDrop(event) 
{   
    event.stopPropagation();
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect")   
    try {
        dragservice_ds = Components.classes["@mozilla.org/widget/dragservice;1"].getService(Components.interfaces.nsIDragService);
    }
    catch (e)
    {
    	debug('treeDragDrop: e');
    }
    debug('treeDragDrop in');
    var ds = dragservice_ds;
    var ses = ds.getCurrentSession()
    var sourceNode = ses.sourceNode
    var tree = document.getElementById('tree-liste-lehreinheiten')
    var row = { }
    var col = { }
    var child = { }
   
    tree.treeBoxObject.getCellAt(event.pageX, event.pageY, row, col, child)
    if(row.value!=-1) //Drop on Row
    {
	    //Ziel Lehreinheit holen
	    col = tree.columns ? tree.columns["tree-liste-lehreinheiten-col-lehreinheit_id"] : "tree-liste-lehreinheiten-col-lehreinheit_id";
		ziel_lehreinheit_id=tree.view.getCellText(row.value,col);
    }
    else
    	ziel_lehreinheit_id=-1; //Drop on Header or empty place
	
	//Quell Lehreinheit holen
	var flavourset = new FlavourSet();
    flavourset.appendFlavour("lva");
    var transferData = nsTransferable.get(flavourset, getDragData, true);
    quell_lehreinheit_id=transferData.first.first.data;
    
    if(quell_lehreinheit_id!=ziel_lehreinheit_id)
    {
	    //Pratizipierung Speichern
	    if(confirm('Wollen Sie diese Lehreinheit wirklich verschieben'))
	    {
	    	// Request absetzen
			var httpRequest = new XMLHttpRequest();
			var url = "<?php echo APP_ROOT; ?>rdf/fas/db_dml.rdf.php";
				
			httpRequest.open("POST", url, false, '','');
			httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
					
			var param = 'type=lva_partizipierung&ziel_lehreinheit_id='+ziel_lehreinheit_id+'&quell_lehreinheit_id='+quell_lehreinheit_id;
		
			//Parameter schicken
			httpRequest.send(param);
			
			// Bei status 4 ist sendung Ok
			switch(httpRequest.readyState)
			{
				case 1,2,3: alert('Bad Ready State: '+httpRequest.status);
					        return false;
				            break;
			
				case 4:		if(httpRequest.status !=200)
					        {
						        alert('The server respond with a bad status code: '+httpRequest.status);
						        return false;
					        }
					        else
					        {
						        var response = httpRequest.responseText;
					        }
				            break;
				 default:   //passiert hoffentlich nie
				 			alert("Fehler: DragnDrop Request Error");
				 			break;
			}
				
			// Returnwerte aus RDF abfragen
			var dsource=parseRDFString(response, 'http://www.technikum-wien.at/dbdml');
			
			var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
			               getService(Components.interfaces.nsIRDFService);
			var subject = rdfService.GetResource("http://www.technikum-wien.at/dbdml/0");
			
			var predicateNS = "http://www.technikum-wien.at/dbdml/rdf";
				
			var dbdml_return = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#return" ));
			var dbdml_errormsg = getTargetHelper(dsource, subject, rdfService.GetResource( predicateNS + "#errormsg" ));
				
			if(dbdml_return=='true')
				RefreshLehreinheitenTree();
			else
				alert(dbdml_errormsg);
	    }  
	}
	else
	{
		alert('Partizipierung von sich selbst ist nicht moeglich');
	}
}