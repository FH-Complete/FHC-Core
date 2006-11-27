<?php
include('../vilesci/config.inc.php');
?>
function lehrstunde(id,idList)
{
	this.id=id;
	this.idList=idList;
}
var lehrstunden=new Array();

/***** Drag Observer fuer Lehrveranstaltungen *****/
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

    			var url="<?php echo APP_ROOT; ?>content/timetable-week.xul.php";
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