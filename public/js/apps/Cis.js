import FhcSearchbar from "../components/searchbar/searchbar.js";
import CisMenu from "../components/Cis/Menu.js";

import fhcapifactory from "./api/fhcapifactory.js";
Vue.$fhcapi = fhcapifactory;

Vue.createApp({
    components: {
        FhcSearchbar,
        CisMenu
    },
    data: function() {
        return {
            searchbaroptions: {
                types: [
                    "mitarbeiter",
                    "raum",
                    "organisationunit"
                ],
                actions: {
                    employee: {
                        defaultaction: {
                            type: "link",
                            action: function(data) {
                                return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                                "/Cis/Profil/View/"+data.uid; 
                                
                            }
                        },
                        childactions: []
                    },
                    raum: {
                        defaultaction: {
                            type: "function",
                            action: function(data) { 
                                alert('raum defaultaction ' + JSON.stringify(data));
                            }
                        },
                        childactions: [
                            {
                                label: "Rauminformation",
                                icon: "fas fa-info-circle",
                                type: "link",
                                action: function(data) {
                                    return data.infolink;
                                }
                            },
                            {
                                label: "Raumreservierung",
                                icon: "fas fa-bookmark",
                                type: "link",
                                action: function(data) {
                                    return data.booklink;
                                }
                            }
                        ]
                    },
                    organisationunit: {
                        defaultaction: {
                            type: "function",
                            action: function(data) {
                                alert('organisationunit defaultaction ' + JSON.stringify(data));
                            }
                        },
                        childactions: []
                    }
                }
            }
        };
    },
    methods: {
        searchfunction: function(searchsettings) {
            return Vue.$fhcapi.Search.search(searchsettings);
        }
    }
}).mount('#cis-header');