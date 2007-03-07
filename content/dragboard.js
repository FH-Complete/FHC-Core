function lehrstunde(type,stg_kz,sem,ver,grp)
{
	this.type=type;
	this.stg_kz=stg_kz;
	this.sem=sem;
	this.ver=ver;
	this.grp=grp;
}

var listObserver= 
{ 
	onDragStart: function (evt,transferData,action)
	{
    		var elemID=evt.target.getAttribute("elem");
    		var paramList=evt.target.getAttribute("paramList");
    		var transferObjekt=new lehrstunde(elemID,paramList,elemID,elemID,elemID);
    		transferData.data=new TransferData();
    		//transferData.data.addDataForFlavour("text/unicode",transferObjekt);
    		transferData.data.addDataForFlavour("text/unicode",paramList);
  	}
};

/***** Drag Observer fuer Gruppen *****/
var grpObserver=
{
	onDragStart: function (evt,transferData,action)
	{    		
    		var stg_kz=evt.target.getAttribute("stg_kz");
    		var sem=evt.target.getAttribute("sem");
    		var ver=evt.target.getAttribute("ver");
    		var grp=evt.target.getAttribute("grp");
    		var einheit=evt.target.getAttribute("gruppe");
    		var paramList= stg_kz+'&'+sem+'&'+ver+'&'+grp+'&'+gruppe;
    		transferData.data=new TransferData();
    		transferData.data.addDataForFlavour("gruppe",paramList);
  	}
};

var boardObserver= 
{
	/*canHandleMultipleItems : function()
	{
		var canHandleMultipleItems=false;
	},*/
	getSupportedFlavours : function () 
	{
  	  	var flavours = new FlavourSet();
  	  	flavours.appendFlavour("text/unicode");
  	  	return flavours;
  	},
  	onDragOver: function (evt,flavour,session)
  	{
  	},
  	onDrop: function (evt,dropdata,session)
  	{
    		if (dropdata.data!="")
    		{
    			var dragElement=document.getElementById(dropdata.data);
    			var contentFrame=document.getElementById('iframeTimeTableWeek');
				var stunde=evt.target.getAttribute("stunde");
    			var datum=evt.target.getAttribute("datum");
      			/*//var elem=document.createElement("label");*/
      			/*evt.target.appendChild(elem); */
      			var paramList=dragElement.getAttribute("paramList");
      			/*elem.setAttribute("value",dropdata.data + paramList); */
      			var url=location.href;//contentFrame.getAttribute('src');
				url+=paramList+"&new_stunde="+stunde+"&new_datum="+datum+'&aktion=stplverschieben';
				//contentFrame.setAttribute('src', url);
				location.href=url;
    		}		
  	}
};

