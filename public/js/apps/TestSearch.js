import {CoreFilterCmpt} from '../components/Filter.js';
import {CoreNavigationCmpt} from '../components/Navigation.js';
import verticalsplit from "../components/verticalsplit/verticalsplit.js";
import searchbar from "../components/searchbar/searchbar.js";
import fhcapifactory from "./api/fhcapifactory.js";

Vue.$fhcapi = fhcapifactory;

Vue.createApp({
  "data": function() {
    return {
      "title": "Test Search",
      "appSideMenuEntries": {},
      "searchbaroptions": {
          "types": [
            "person",
            "raum",
            "mitarbeiter",
            "student",
            "prestudent",
            "document",
            "cms",
            "organisationunit"
          ],
          "actions": {
              "person": {
                  "defaultaction": {
                    "type": "link",
                    "action": function(data) { 
                      //alert('person defaultaction ' + JSON.stringify(data)); 
                      //window.location.href = data.profil;
                      return data.profil;
                    }
                  },
                  "childactions": [
                      {
                          "label": "testchildaction1",
                          "icon": "fas fa-check-circle",
                          "type": "function",
                          "action": function(data) { 
                              alert('person testchildaction 01 ' + JSON.stringify(data)); 
                          }
                      },
                      {
                          "label": "testchildaction2",
                          "icon": "fas fa-file-csv",
                          "type": "function",
                          "action": function(data) { 
                              alert('person testchildaction 02 ' + JSON.stringify(data)); 
                          }
                      }
                  ]
              },
              "raum": {
                  "defaultaction": {
                    "type": "function",
                    "action": function(data) { 
                      alert('raum defaultaction ' + JSON.stringify(data)); 
                    }
                  },
                  "childactions": [                      
                     {
                          "label": "Rauminformation",
                          "icon": "fas fa-info-circle",
                          "type": "link",
                          "action": function(data) { 
                              return data.infolink;
                          }
                      },
                      {
                          "label": "Raumreservierung",
                          "icon": "fas fa-bookmark",
                          "type": "link",
                          "action": function(data) { 
                              return data.booklink;
                          }
                      }
                  ]
              },
              "employee": {
                  "defaultaction": {
                    "type": "function",
                    "action": function(data) { 
                      alert('employee defaultaction ' + JSON.stringify(data)); 
                    }
                  },
                  "childactions": [
                      {
                          "label": "testchildaction1",
                          "icon": "fas fa-address-book",
                          "type": "function",
                          "action": function(data) { 
                              alert('employee testchildaction 01 ' + JSON.stringify(data)); 
                          }
                      },
                      {
                          "label": "testchildaction2",
                          "icon": "fas fa-user-slash",
                          "type": "function",
                          "action": function(data) { 
                              alert('employee testchildaction 02 ' + JSON.stringify(data)); 
                          }
                      },
                      {
                          "label": "testchildaction3",
                          "icon": "fas fa-bell",
                          "type": "function",
                          "action": function(data) { 
                              alert('employee testchildaction 03 ' + JSON.stringify(data)); 
                          }
                      },
                      {
                          "label": "testchildaction4",
                          "icon": "fas fa-calculator",
                          "type": "function",
                          "action": function(data) { 
                              alert('employee testchildaction 04 ' + JSON.stringify(data)); 
                          }
                      }
                  ]
              },
              "organisationunit": {
                  "defaultaction": {
                    "type": "function",
                    "action": function(data) { 
                      alert('organisationunit defaultaction ' + JSON.stringify(data)); 
                    }
                  },
                  "childactions": []
              }
          }
      },
      "searchbaroptions2": {
          "types": [
            "raum",
            "organisationunit"
          ],
          "actions": {
              "raum": {
                  "defaultaction": {
                    "type": "function",
                    "action": function(data) { 
                      alert('raum defaultaction ' + JSON.stringify(data)); 
                    }
                  },
                  "childactions": [                      
                     {
                          "label": "Rauminformation",
                          "icon": "fas fa-info-circle",
                          "type": "link",
                          "action": function(data) { 
                              return data.infolink;
                          }
                      },
                      {
                          "label": "Raumreservierung",
                          "icon": "fas fa-bookmark",
                          "type": "link",
                          "action": function(data) { 
                              return data.booklink;
                          }
                      }
                  ]
              },              
              "organisationunit": {
                  "defaultaction": {
                    "type": "function",
                    "action": function(data) { 
                      alert('organisationunit defaultaction ' + JSON.stringify(data)); 
                    }
                  },
                  "childactions": []
              }
          }
      }
    };
  },
  "components": {
    "CoreNavigationCmpt": CoreNavigationCmpt,
    "CoreFilterCmpt": CoreFilterCmpt,
    "verticalsplit": verticalsplit,
    "searchbar": searchbar
  },
  "methods": {
    "newSideMenuEntryHandler": function(payload) {
        this.appSideMenuEntries = payload;
    },
    "searchfunction": function(searchsettings) {
        return Vue.$fhcapi.Search.search(searchsettings);  
    },
    "searchfunctiondummy": function(searchsettings) {
        return Vue.$fhcapi.Search.searchdummy(searchsettings);  
    }
  }
}).mount('#main');
