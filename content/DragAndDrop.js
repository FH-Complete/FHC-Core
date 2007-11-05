/* -*- Mode: Java; tab-width: 4; indent-tabs-mode: nil; c-basic-offset: 2 -*- */
/* ***** BEGIN LICENSE BLOCK *****
 * Version: NPL 1.1/GPL 2.0/LGPL 2.1
 *
 * The contents of this file are subject to the Netscape Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/NPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is mozilla.org code.
 *
 * The Initial Developer of the Original Code is 
 * Netscape Communications Corporation.
 * Portions created by the Initial Developer are Copyright (C) 1998
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *   Ben Goodger <ben@netscape.com> (Original Author)
 *   Pierre Chanial <pierrechanial@netscape.net>
 *
 * Alternatively, the contents of this file may be used under the terms of
 * either the GNU General Public License Version 2 or later (the "GPL"), or 
 * the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
 * in which case the provisions of the GPL or the LGPL are applicable instead
 * of those above. If you wish to allow use of your version of this file only
 * under the terms of either the GPL or the LGPL, and not to allow others to
 * use your version of this file under the terms of the NPL, indicate your
 * decision by deleting the provisions above and replace them with the notice
 * and other provisions required by the GPL or the LGPL. If you do not delete
 * the provisions above, a recipient may use your version of this file under
 * the terms of any one of the NPL, the GPL or the LGPL.
 *
 * ***** END LICENSE BLOCK ***** */

////////////////////////////////////////////////////////////////////////////
// XXX - WARNING - DRAG AND DROP API CHANGE ALERT - XXX
// This file has been extensively modified in a checkin planned for Mozilla
// 0.8, and the API has been modified. DO NOT MODIFY THIS FILE without 
// approval from ben@netscape.com, otherwise your changes will be lost. 

/**
 * XXX - until load is supported in chrome, you also need to include 
 *       these files:
 *       chrome://global/content/nsTransferable.js
 **/



/**
 * nsDragAndDrop - a convenience wrapper for nsTransferable, nsITransferable
 *                 and nsIDragService/nsIDragSession. 
 *
 * USAGE INFORMATION: see 'README-nsDragAndDrop.html' in the same source directory
 *                    as this file (typically xpfe/global/resources/content)
 */

var nsDragAndDrop = 
{
	_mDS: null,
	get mDragService()
	{
		if (!this._mDS) 
        	{
        	const kDSContractID = "@mozilla.org/widget/dragservice;1";
        	const kDSIID = Components.interfaces.nsIDragService;
        	this._mDS = Components.classes[kDSContractID].getService(kDSIID);
        	}
      	return this._mDS;
	},

  /**
   * void startDrag (DOMEvent aEvent, Object aDragDropObserver) ;
   *
   * called when a drag on an element is started.
   *
   * @param DOMEvent aEvent
   *        the DOM event fired by the drag init
   * @param Object aDragDropObserver
   *        javascript object of format described above that specifies
   *        the way in which the element responds to drag events.
   **/  
  	startDrag: function (aEvent, aDragDropObserver)
    	{

    	try 
    	{
    		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	} catch (e) { alert(e);	}
	if (!("onDragStart" in aDragDropObserver))
    	    return;

      	const kDSIID = Components.interfaces.nsIDragService;
      	var dragAction = 
      	{ 
      		action: kDSIID.DRAGDROP_ACTION_COPY + kDSIID.DRAGDROP_ACTION_MOVE + kDSIID.DRAGDROP_ACTION_LINK 
      	};

      	var transferData = 
      	{ 
      		data: null 
      	};
      	try 
        {
          	aDragDropObserver.onDragStart(aEvent, transferData, dragAction);
        }
      	catch (e) 
        {
          	return;  // not a draggable item, bail!
        }
      	if (!transferData.data) 
      		return;
      	transferData = transferData.data;

      	var transArray = Components.classes["@mozilla.org/supports-array;1"]
        	.createInstance(Components.interfaces.nsISupportsArray);

      	var region = null;
      	if (aEvent.originalTarget.localName == "treechildren") 
      	{
        	// let's build the drag region
        	var tree = aEvent.originalTarget.parentNode;
        	try 
        	{
          		region = Components.classes["@mozilla.org/gfx/region;1"].createInstance(Components.interfaces.nsIScriptableRegion);
          		region.init();
          		var obo = tree.treeBoxObject;
          		var bo = obo.treeBody.boxObject;
          		var obosel= obo.selection;
		        var rowX = bo.x;
          		var rowY = bo.y;
          		var rowHeight = obo.rowHeight;
          		var rowWidth = bo.width;

          		//add a rectangle for each visible selected row
          		for (var i = obo.getFirstVisibleRow(); i <= obo.getLastVisibleRow(); i ++)
          		{
          			if (obosel.isSelected(i))
              				region.unionRect(rowX, rowY, rowWidth, rowHeight);
            			rowY = rowY + rowHeight;
          		}
      
          		//and finally, clip the result to be sure we don't spill over...
          		region.intersectRect(bo.x, bo.y, bo.width, bo.height);
        	}
        	catch(ex)
        	{
          		dump("Error while building selection region: " + ex + "\n");
          		region = null;
        	}
      	}

      	var count = 0;
      	do 
        {
        	var trans = nsTransferable.set(transferData._XferID == "TransferData" 
        		? transferData : transferData.dataList[count++]);
          	transArray.AppendElement(trans.QueryInterface(Components.interfaces.nsISupports));
        }
      	while (transferData._XferID == "TransferDataSet" && count < transferData.dataList.length);
      
      	try 
      	{
        	this.mDragService.invokeDragSession(aEvent.target, transArray, region, dragAction.action);
      	}
      	catch(ex) 
      	{
        	// this could be because the user pressed escape to
        	// cancel the drag. even if it's not, there's not much
        	// we can do, so be silent.
      	}
      	aEvent.stopPropagation();
	},

  /** 
   * void dragOver (DOMEvent aEvent, Object aDragDropObserver) ;
   *
   * called when a drag passes over this element
   *
   * @param DOMEvent aEvent
   *        the DOM event fired by passing over the element
   * @param Object aDragDropObserver
   *        javascript object of format described above that specifies
   *        the way in which the element responds to drag events.
   **/
	dragOver: function (aEvent, aDragDropObserver)
    {
    try 
    	{
    		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	} catch (e) { alert(e);	}
	  if (!("onDragOver" in aDragDropObserver)) 
        return;
      if (!this.checkCanDrop(aEvent, aDragDropObserver))
        return;
      var flavourSet = aDragDropObserver.getSupportedFlavours();
      for (var flavour in flavourSet.flavourTable)
        {
          if (this.mDragSession.isDataFlavorSupported(flavour))
            {
              aDragDropObserver.onDragOver(aEvent, 
                                           flavourSet.flavourTable[flavour], 
                                           this.mDragSession);
              aEvent.stopPropagation();
              break;
            }
        }
    },

  mDragSession: null,

  /** 
   * void drop (DOMEvent aEvent, Object aDragDropObserver) ;
   *
   * called when the user drops on the element
   *
   * @param DOMEvent aEvent
   *        the DOM event fired by the drop
   * @param Object aDragDropObserver
   *        javascript object of format described above that specifies
   *        the way in which the element responds to drag events.
   **/
  drop: function (aEvent, aDragDropObserver)
    {
    try 
        	{
        		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
    	} catch (e) { alert(e);	}
	
      if (!("onDrop" in aDragDropObserver))
        return;
      if (!this.checkCanDrop(aEvent, aDragDropObserver))
        return;  
      if (this.mDragSession.canDrop) {
        var flavourSet = aDragDropObserver.getSupportedFlavours();
        var transferData = nsTransferable.get(flavourSet, this.getDragData, true);
        // hand over to the client to respond to dropped data
        var multiple = "canHandleMultipleItems" in aDragDropObserver && aDragDropObserver.canHandleMultipleItems;
        var dropData = multiple ? transferData : transferData.first.first;
        aDragDropObserver.onDrop(aEvent, dropData, this.mDragSession);
      }
      aEvent.stopPropagation();
    },

  /** 
   * void dragExit (DOMEvent aEvent, Object aDragDropObserver) ;
   *
   * called when a drag leaves this element
   *
   * @param DOMEvent aEvent
   *        the DOM event fired by leaving the element
   * @param Object aDragDropObserver
   *        javascript object of format described above that specifies
   *        the way in which the element responds to drag events.
   **/
  dragExit: function (aEvent, aDragDropObserver)
    {
    try 
        	{
        		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
    	} catch (e) { alert(e);	}
	
      if (!this.checkCanDrop(aEvent, aDragDropObserver))
        return;
      if ("onDragExit" in aDragDropObserver)
        aDragDropObserver.onDragExit(aEvent, this.mDragSession);
    },  
    
  /** 
   * void dragEnter (DOMEvent aEvent, Object aDragDropObserver) ;
   *
   * called when a drag enters in this element
   *
   * @param DOMEvent aEvent
   *        the DOM event fired by entering in the element
   * @param Object aDragDropObserver
   *        javascript object of format described above that specifies
   *        the way in which the element responds to drag events.
   **/
  dragEnter: function (aEvent, aDragDropObserver)
    {
    try 
        	{
        		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
    	} catch (e) { alert(e);	}
	
      if (!this.checkCanDrop(aEvent, aDragDropObserver))
        return;
      if ("onDragEnter" in aDragDropObserver)
        aDragDropObserver.onDragEnter(aEvent, this.mDragSession);
    },  
    
  /** 
   * nsISupportsArray getDragData (Object aFlavourList)
   *
   * Creates a nsISupportsArray of all droppable items for the given
   * set of supported flavours.
   * 
   * @param FlavourSet aFlavourSet
   *        formatted flavour list.
   **/  
  getDragData: function (aFlavourSet)
  {
    	try 
        {
        		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
    	} 
    	catch (e) 
    	{ 
    		alert(e);	
    	}
	
 		var supportsArray = Components.classes["@mozilla.org/supports-array;1"]
        		.createInstance(Components.interfaces.nsISupportsArray);

      	for (var i = 0; i < nsDragAndDrop.mDragSession.numDropItems; ++i)
        {
          	var trans = nsTransferable.createTransferable();
          	for (var j = 0; j < aFlavourSet.flavours.length; ++j)
            	trans.addDataFlavor(aFlavourSet.flavours[j].contentType);
          	nsDragAndDrop.mDragSession.getData(trans, i);
          	supportsArray.AppendElement(trans);
        }
      	return supportsArray;
  },

  /** 
   * Boolean checkCanDrop (DOMEvent aEvent, Object aDragDropObserver) ;
   *
   * Sets the canDrop attribute for the drag session.
   * returns false if there is no current drag session.
   *
   * @param DOMEvent aEvent
   *        the DOM event fired by the drop
   * @param Object aDragDropObserver
   *        javascript object of format described above that specifies
   *        the way in which the element responds to drag events.
   **/
  checkCanDrop: function (aEvent, aDragDropObserver)
    {
    try 
        	{
        		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
    	} catch (e) { alert(e);	}
	
      if (!this.mDragSession) 
        this.mDragSession = this.mDragService.getCurrentSession();
      if (!this.mDragSession) 
        return false;
      //oesi - Auskommentiert um das DragnDrop innerhalb des selben Trees zu ermoeglichen
      //this.mDragSession.canDrop = this.mDragSession.sourceNode != aEvent.target;
      this.mDragSession.canDrop = true;
      if ("canDrop" in aDragDropObserver)
        this.mDragSession.canDrop &= aDragDropObserver.canDrop(aEvent, this.mDragSession);
      return true;
    } 
};

