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
include('../vilesci/config.inc.php');
?>
function lehrstunde(id,idList)
{
	this.id=id;
	this.idList=idList;
}
var lehrstunden=new Array();

/***** Drag Observer fuer Lehrveranstaltungen *****/
//zum verplanen der LVAs im Stundenplan
var lvaObserver=
{
	onDragStart: function (evt,transferData,action)
	{
		var idList=evt.target.getAttribute("idList");
    	var aktion=evt.target.getAttribute("aktion");
    	aktion+="_set";
    	var paramList="?aktion="+aktion+"&lva_ids="+idList;
    	transferData.data=new TransferData();
    	transferData.data.addDataForFlavour("application/tempus-lehrveranstaltung",paramList);
    	//alert("test");
  	}
};

/***** Drag Observer fuer Gruppen *****/
var lvbgrpDDObserver=
{
	onDragStart: function (evt,transferData,action)
	{
		var tree = document.getElementById('tree-verband')
	    var row = { }
	    var col = { }
	    var child = { }

	    //Index der Quell-Row ermitteln
	    tree.treeBoxObject.getCellAt(evt.pageX, evt.pageY, row, col, child)

	    //Beim Scrollen soll kein DnD gemacht werden
	    if(col.value==null)
	    	return false;

	    //Daten ermitteln
	    col = tree.columns ? tree.columns["stg_kz"] : "stg_kz";
		stg_kz=tree.view.getCellText(row.value,col);

		col = tree.columns ? tree.columns["sem"] : "sem";
		sem=tree.view.getCellText(row.value,col);

		col = tree.columns ? tree.columns["ver"] : "ver";
		ver=tree.view.getCellText(row.value,col);

		col = tree.columns ? tree.columns["grp"] : "grp";
		grp=tree.view.getCellText(row.value,col);

		col = tree.columns ? tree.columns["gruppe"] : "gruppe";
		gruppe=tree.view.getCellText(row.value,col);

		var paramList= stg_kz+'&'+sem+'&'+ver+'&'+grp+'&'+gruppe;
		//debug('param:'+paramList);
		transferData.data=new TransferData();
		transferData.data.addDataForFlavour("application/tempus-lvbgruppe",paramList);
  	}
};

/***** Drag Observer fuer Studenten *****/
var studentDDObserver=
{
	onDragStart: function (evt,transferData,action)
	{
	
		var tree = document.getElementById('student-tree')
	    var row = { }
	    var col = { }
	    var child = { }

	    //Index der Quell-Row ermitteln
	    tree.treeBoxObject.getCellAt(evt.pageX, evt.pageY, row, col, child)

	    //Beim Scrollen soll kein DnD gemacht werden
	    if(col.value==null)
	    	return false;

		var start = new Object();
		var end = new Object();
		var numRanges = tree.view.selection.getRangeCount();
		var paramList= '';
		
		for (var t = 0; t < numRanges; t++)
		{
	  		tree.view.selection.getRangeAt(t,start,end);
  			for (var v = start.value; v <= end.value; v++)
  			{
    			col = tree.columns ? tree.columns["student-treecol-uid"] : "student-treecol-uid";
				uid = tree.view.getCellText(v,col);
				if(uid=='')
				{
					alert('Es koennen nur Personen mit UID (Studenten/Mitarbeiter) verschoben werden');
					return false;
				}
				paramList += ';'+uid;
  			}
		}
		
		transferData.data=new TransferData();
		transferData.data.addDataForFlavour("application/tempus-student",paramList);
  	}
};

// ****
// * Observer fuer den Gruppen Tree im Lehreinheiten-Modul
// ****
var LeLvbgrpDDObserver=
{
	getSupportedFlavours : function ()
	{
  	  	var flavours = new FlavourSet();
  	  	flavours.appendFlavour("application/tempus-lvbgruppe");
  	  	return flavours;
  	},
  	onDragEnter: function (evt,flavour,session)
	{
	},
	onDragExit: function (evt,flavour,session)
	{
  	},
  	onDragOver: function(evt,flavour,session)
  	{
  	},
  	onDrop: function (evt,dropdata,session)
  	{
	    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	    try
	    {
	        dragservice_ds = Components.classes["@mozilla.org/widget/dragservice;1"].getService(Components.interfaces.nsIDragService);
	    }
	    catch (e)
	    {
	    	debug('treeDragDrop: e');
	    }

	    var ds = dragservice_ds;

	    var ses = ds.getCurrentSession()
	    var sourceNode = ses.sourceNode
	    var lehreinheit_id = document.getElementById('lehrveranstaltung-detail-textbox-lehreinheit_id').value;
	    var row = { }
	    var col = { }
	    var child = { }

	    if(lehreinheit_id=='')
	    	return false;

	    quell_gruppe=dropdata.data;
	    var arr = quell_gruppe.split("&");

	    var stg_kz = arr[0];
	    var sem = arr[1];
	    var ver = arr[2];
	    var grp = arr[3];
	    var gruppe = arr[4];
	    //alert("stg: "+stg_kz+" sem: "+sem+" ver: "+ver+" grp: "+grp+" gruppe: "+gruppe+" TO Lehreinheit:"+lehreinheit_id);

	    var req = new phpRequest('lvplanung/lehrveranstaltungDBDML.php','','');
		neu = document.getElementById('lehrveranstaltung-detail-checkbox-new').checked;

		req.add('type','lehreinheit_gruppe_add');

		req.add('lehreinheit_id', lehreinheit_id);
		req.add('studiengang_kz', stg_kz);
		req.add('semester', sem);
		req.add('verband', ver);
		req.add('gruppe', grp);
		req.add('gruppe_kurzbz', gruppe);

		var response = req.executePOST();

		var val =  new ParseReturnValue(response)

		if (!val.dbdml_return)
		{
			alert(val.dbdml_errormsg);
		}
		else
		{
			//GruppenTree Refreshen
			LeDetailGruppeTreeRefresh();
			LvTreeRefresh();
		}
  	}
};

/***** Drag Observer fuer Lektoren *****/
var mitarbeiterDDObserver=
{
	onDragStart: function (evt,transferData,action)
	{
		var tree = document.getElementById('tree-lektor')
	    var row = { }
	    var col = { }
	    var child = { }

	    //Index der Quell-Row ermitteln
	    tree.treeBoxObject.getCellAt(evt.pageX, evt.pageY, row, col, child)

	    //Beim Scrollen soll kein DnD gemacht werden
	    if(col.value==null)
	    	return false;

	    //Daten ermitteln
	    col = tree.columns ? tree.columns["uid"] : "uid";
		uid=tree.view.getCellText(row.value,col);

		var paramList= uid;
		transferData.data=new TransferData();
		transferData.data.addDataForFlavour("application/tempus-mitarbeiter",paramList);
  	}
};

// ****
// * Observer fuer Drop eines Lektors auf einen Studiengang
// ****
var LektorFunktionDDObserver=
{
	getSupportedFlavours : function ()
	{
  	  	var flavours = new FlavourSet();
  	  	flavours.appendFlavour("application/tempus-mitarbeiter");
  	  	return flavours;
  	},
  	onDragEnter: function (evt,flavour,session)
	{
	},
	onDragExit: function (evt,flavour,session)
	{
  	},
  	onDragOver: function(evt,flavour,session)
  	{
  	},
  	onDrop: function (evt,dropdata,session)
  	{
	    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	    try
	    {
	        dragservice_ds = Components.classes["@mozilla.org/widget/dragservice;1"].getService(Components.interfaces.nsIDragService);
	    }
	    catch (e)
	    {
	    	debug('treeDragDrop: e');
	    }

	    var ds = dragservice_ds;

		var tree = document.getElementById('tree-lektor')
	    var row = { }
	    var col = { }
	    var child = { }

	    //Index der Quell-Row ermitteln
	    tree.treeBoxObject.getCellAt(evt.pageX, evt.pageY, row, col, child)

	    //Beim Scrollen soll kein DnD gemacht werden
	    if(col.value==null)
	    	return false;

	    //Daten ermitteln
	    col = tree.columns ? tree.columns["studiengang_kz"] : "studiengang_kz";
		var stg=tree.view.getCellText(row.value,col);

	    uid=dropdata.data;

	    var req = new phpRequest('tempusDBDML.php','','');

	    req.add('type', 'addFunktionToMitarbeiter');
		req.add('uid', uid);
		req.add('studiengang_kz', stg);

		var response = req.executePOST();

		var val =  new ParseReturnValue(response)

		if (!val.dbdml_return)
		{
			alert(val.dbdml_errormsg)
		}
		else
		{
			//Tree Refreshen
			//keine Ahnung warum ich da ein setTimeout brauche
			//aber wenns nicht da ist dann stuerzt Mozilla ab?!
			//mit seamonkey funktionierts auch ohne!
			LektorTreeOpenStudiengang = stg;
			window.setTimeout(RefreshLektorTree,10);
		}
  	}
};

// ****
// * Observer fuer Lektor-Tree bei Lehreinheit-Modul
// * Bei OnDrop eines mitarbeiters wird dieser der
// * Lehreinheit zugeordnet
// ****
var LeLektorDDObserver=
{
	getSupportedFlavours : function ()
	{
  	  	var flavours = new FlavourSet();
  	  	flavours.appendFlavour("application/tempus-mitarbeiter");
  	  	return flavours;
  	},
  	onDragEnter: function (evt,flavour,session)
	{

	},
	onDragExit: function (evt,flavour,session)
	{
  	},
  	onDragOver: function(evt,flavour,session)
  	{
  	},
  	onDrop: function (evt,dropdata,session)
  	{
	    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	    try
	    {
	        dragservice_ds = Components.classes["@mozilla.org/widget/dragservice;1"].getService(Components.interfaces.nsIDragService);
	    }
	    catch (e)
	    {
	    	debug('treeDragDrop: e');
	    }

	    var ds = dragservice_ds;

	    var ses = ds.getCurrentSession()
	    var sourceNode = ses.sourceNode
	    var lehreinheit_id = document.getElementById('lehrveranstaltung-detail-textbox-lehreinheit_id').value;
	    var row = { }
	    var col = { }
	    var child = { }

	    if(lehreinheit_id=='')
	    	return false;

	    uid=dropdata.data;
	    //alert("uid: "+uid);

	    var req = new phpRequest('lvplanung/lehrveranstaltungDBDML.php','','');
		neu = document.getElementById('lehrveranstaltung-detail-checkbox-new').checked;

		req.add('type','lehreinheit_mitarbeiter_add');

		req.add('lehreinheit_id', lehreinheit_id);
		req.add('mitarbeiter_uid', uid);

		var response = req.executePOST();

		var val =  new ParseReturnValue(response)

		if (!val.dbdml_return)
		{
			alert(val.dbdml_errormsg)
		}
		else
		{
			//LektorTree Refreshen
			LeLektorTreeRefresh();
		}
  	}
};
/***** Drag Observer fuer STPL-Verschiebung *****/
var listObserver=
{
	onDragStart: function (evt,transferData,action)
	{
    		var type=evt.target.getAttribute("stpltype");
    		var dragdatum=evt.target.getAttribute("datum");
    		var pers_uid=evt.target.getAttribute("pers_uid");
    		var idList=evt.target.getAttribute("idList");
    		var stg_kz=evt.target.getAttribute("stg_kz");
    		var sem=evt.target.getAttribute("sem");
    		var ver=evt.target.getAttribute("ver");
    		var grp=evt.target.getAttribute("grp");
    		var einheit=evt.target.getAttribute("einheit");
    		var old_ort=evt.target.getAttribute("ort");
    		var aktion=evt.target.getAttribute("aktion");
    		aktion+="_set";
    		var paramList="?dragtype="+type+"&dragdatum="+dragdatum+"&pers_uid="+pers_uid+"&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&einheit="+einheit+"&old_ort="+old_ort+idList+"&aktion="+aktion;
    		//var transferObjekt=new lehrstunde(type,dragdatum,pers_uid,stg_kz,sem,ver,grp,einheit,old_ort,idList);
    		transferData.data=new TransferData();
    		transferData.data.addDataForFlavour("application/tempus-lehrstunde",paramList);

    		var styleOrig=evt.target.getAttribute("styleOrig");
    		evt.target.setAttribute("style",styleOrig+"color:red;font-style:italic;");
  	}
};

/***** Board Observer fuer STPL- und LVA-Verschiebung *****/
var boardObserver=
{
	/*canHandleMultipleItems : function()
	{
		var canHandleMultipleItems=false;
	},*/
	getSupportedFlavours : function ()
	{
  	  	var flavours = new FlavourSet();
  	  	flavours.appendFlavour("application/tempus-lehrveranstaltung");
  	  	flavours.appendFlavour("application/tempus-lehrstunde");
  	  	return flavours;
  	},
  	onDragEnter: function (evt,flavour,session)
	{
		var styleNow=evt.target.getAttribute("style");
		if (evt.target.tagName=="label")
			evt.target.setAttribute("style","background-color:#AAFFAA;");
		else
			evt.target.setAttribute("style",styleNow+"border:1px dashed black;");

	},
	onDragExit: function (evt,flavour,session)
	{
		var styleNow=evt.target.getAttribute("style");
		if (evt.target.tagName=="label")
			evt.target.setAttribute("style","");
		else
			evt.target.setAttribute("style",styleNow+"border:1px solid black;");
  	},
  	onDragOver: function(evt,flavour,session)
  	{
  		// Mehrfachauswahl von Lehrstunden mit CTRL bzw. ALT-Taste
  		if ((evt.ctrlKey || evt.altKey) && flavour.contentType=="application/tempus-lehrstunde" && evt.target.tagName=="button")
		{
			var idList=evt.target.getAttribute("idList");
    		var id=evt.target.getAttribute("id");
    		var styleOrig=evt.target.getAttribute("styleOrig");
    		// Ist Element schon vorhanden und an welcher stelle im Array?
    		var gesetzt=null;
    		for (var i=0;i<lehrstunden.length;i++)
    			if (lehrstunden[i].id==id)
    				gesetzt=i;
    		// User will Element anhaengen?
    		if (gesetzt==null && evt.ctrlKey)
    		{
    			var ls=new lehrstunde(id,idList);
				lehrstunden.push(ls);
				evt.target.setAttribute("style",styleOrig+"color:red;font-style:italic;");
    		}
    		else
    			// User will Element entfernen?
    			if (gesetzt!=null && evt.altKey)
    			{
	    			// Element aus Array loeschen
    				if (gesetzt==0)
	    				lehrstunden.shift();
    				else
	    				if (gesetzt==lehrstunden.length)
    						lehrstunden.pop();
	    				else
    					{
	    					var tmpArray=new array();
    						tmpArray.concat(lehrstunden.slice(0,gesetzt-1));
    						tmpArray.concat(lehrstunden.slice(gesetzt+1,lehrstunden.length));
    						lehrstunden=tmpArray;
	    				}
    				evt.target.setAttribute("style",styleOrig+"color:black;font-style:normal;");
    			}

		}
  	},
  	onDrop: function (evt,dropdata,session)
  	{
    		if (dropdata.data!="")
    		{
    			var stplData=document.getElementById('TimeTableWeekData');
    			var datum=stplData.getAttribute("datum");
    			var type=stplData.getAttribute("stpl_type");
    			var stg_kz=stplData.getAttribute("stg_kz");
				var sem=stplData.getAttribute("sem");
				var ver=stplData.getAttribute("ver");
				var grp=stplData.getAttribute("grp");
				var pers_uid=stplData.getAttribute("pers_uid");
    			var ort=stplData.getAttribute("ort");
				var einheit=stplData.getAttribute("einheit");

    			var stunde=evt.target.getAttribute("stunde");
    			var new_datum=evt.target.getAttribute("datum");
    			if (evt.target.tagName=="label")
    				var new_ort=evt.target.getAttribute("value");
    			else
    				if (dropdata.flavour.contentType=="application/tempus-lehrveranstaltung")
    					var new_ort=ort;

    			var url="<?php echo APP_ROOT; ?>content/lvplanung/timetable-week.xul.php";
				url+=dropdata.data+"&new_stunde="+stunde+"&new_datum="+new_datum;
				url+="&type="+type+"&datum="+datum+"&ort="+ort+"&pers_uid="+pers_uid;
				url+="&stg_kz="+stg_kz+"&sem="+sem+"&ver="+ver+"&grp="+grp+"&einheit="+einheit;
				if (evt.target.tagName=="label" || dropdata.flavour.contentType=="application/tempus-lehrveranstaltung")
					url+="&new_ort="+new_ort;
				else
					url+="&aktion=stpl_move";
				url+="&mime="+dropdata.flavour.contentType;

				// Mehrfachauswahl anhaengen
				for (var i=0;i<lehrstunden.length;i++)
    				url+=lehrstunden[i].idList.replace(/&/g,"&x"+i);

    			//var BoxTimeTableWeek=document.getElementById('boxTimeTableWeek');
				//var ScrollX=BoxTimeTableWeek.contentWindow.scrollX;
				//var ScrollY=BoxTimeTableWeek.contentWindow.scrollY;
				//alert('X:'+ScrollX+' Y:'+ScrollY);
				//alert(url);
				location.href=url;
				//BoxTimeTableWeek=document.getElementById('boxTimeTableWeek');
				//BoxTimeTableWeek.scrollTo(ScrollX,ScrollY);
    		}
  	}
};


// ****
// * Observer fuer den Lehrverbandstree
// ****
var verbandtreeDDObserver=
{
	getSupportedFlavours : function ()
	{
  	  	var flavours = new FlavourSet();
  	  	flavours.appendFlavour("application/tempus-student");
  	  	return flavours;
  	},
  	onDragEnter: function (evt,flavour,session)
	{
	},
	onDragExit: function (evt,flavour,session)
	{
  	},
  	onDragOver: function(evt,flavour,session)
  	{
  	},
  	onDrop: function (evt,dropdata,session)
  	{
	    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	    try
	    {
	        dragservice_ds = Components.classes["@mozilla.org/widget/dragservice;1"].getService(Components.interfaces.nsIDragService);
	    }
	    catch (e)
	    {
	    	debug('treeDragDrop: e');
	    }

	    var ds = dragservice_ds;

		var tree = document.getElementById('tree-verband')
	    var row = { }
	    var col = { }
	    var child = { }
	   
	    tree.treeBoxObject.getCellAt(evt.pageX, evt.pageY, row, col, child)
	    
	    if(row.value!=-1) //Drop on Row
	    {
		    //Ziel holen
		    col = tree.columns ? tree.columns["gruppe"] : "gruppe";
			gruppe_kurzbz=tree.view.getCellText(row.value,col);
			
			col = tree.columns ? tree.columns["stg_kz"] : "stg_kz";
			stg_kz=tree.view.getCellText(row.value,col);
			
			col = tree.columns ? tree.columns["sem"] : "sem";
			sem=tree.view.getCellText(row.value,col);
			
			col = tree.columns ? tree.columns["ver"] : "ver";
			ver=tree.view.getCellText(row.value,col);
			
			col = tree.columns ? tree.columns["grp"] : "grp";
			grp=tree.view.getCellText(row.value,col);
	    }
	    else
	    	return false;
	    
	    if(gruppe_kurzbz=='' && sem=='')
	    {
	    	alert('Zuteilung ist nur zu Spezial- oder Lehrverbandsgruppen moeglich');
	    	return false;
	    }
	    
	    uid=dropdata.data;
	    
	    var req = new phpRequest('student/studentDBDML.php','','');
		
		req.add('type','gruppenzuteilung');

		req.add('uid', uid);
		req.add('gruppe_kurzbz', gruppe_kurzbz);
		req.add('stg_kz', stg_kz);
		req.add('semester', sem);
		req.add('verband', ver);
		req.add('gruppe', grp);
		
		var response = req.executePOST();

		var val =  new ParseReturnValue(response)

		if (!val.dbdml_return)
		{
			if(val.dbdml_errormsg=='')
				alert(response);
			else
				alert(val.dbdml_errormsg)
		}
		else
		{
			StudentTreeRefresh();
		}
  	}
};