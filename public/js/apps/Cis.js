import FhcSearchbar from "../components/searchbar/searchbar.js";
import CisMenu from "../components/Cis/Menu.js";
import PluginsPhrasen from '../plugins/Phrasen.js';
import ApiSearchbar from '../api/factory/searchbar.js';

const app = Vue.createApp({
    name: 'CisApp',
    components: {
        FhcSearchbar,
        CisMenu
    },
    data: function() {
        return {
            searchbaroptions: {
				cssclass: "",
				calcheightonly: true,
                types: [
                    "mitarbeiter",
					"student",
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
					student: {
						defaultaction: {
							type: "link",
							action: function (data) {
								return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router +
									"/Cis/Profil/View/" + data.uid;

							}
						},
						childactions: []
					},
                    raum: {
                        defaultaction: {
                            type: "link",
							renderif: function(data) {
								if(data.content_id === "N/A"){
									return false;
								}
								return true;
							},
                            action: function(data) { 
								const link= FHC_JS_DATA_STORAGE_OBJECT.app_root +
									FHC_JS_DATA_STORAGE_OBJECT.ci_router +
									'/CisVue/Cms/content/' + data.content_id;
								return link;
                            }
                        },
                        childactions: [
                            {
                                label: "LV-Plan",
                                icon: "fas fa-bookmark",
                                type: "link",
                                action: function(data) {
									const link = FHC_JS_DATA_STORAGE_OBJECT.app_root +
										FHC_JS_DATA_STORAGE_OBJECT.ci_router +
										'/CisVue/Cms/getRoomInformation/' + data.ort_kurzbz;
									return link;
                                }
                            },
                            {
                                label: "Rauminformation",
                                icon: "fas fa-info-circle",
                                type: "link",
                                renderif: function(data) {
									if(data.content_id === "N/A"){
										return false;
									}
									return true;
                                },
                                action: function(data) {
									const link= FHC_JS_DATA_STORAGE_OBJECT.app_root +
										FHC_JS_DATA_STORAGE_OBJECT.ci_router +
										'/CisVue/Cms/content/' + data.content_id;
									return link;
                                }
                            },
                        ]
                    },
                    organisationunit: {
                        defaultaction: {
                            type: "link",
							renderif: function(data) {
								if(data.mailgroup) {
									return true;
								}
								return false;
							},
                            action: function(data) {
                                const link = 'mailto:' + data.mailgroup;
								return link;
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
        	return this.$api.call(ApiSearchbar.search(searchsettings));
        }
    }
});
app.use(primevue.config.default, {
	zIndex: {
		overlay: 9000,
		tooltip: 8000
	}
})
app.use(PluginsPhrasen);
app.mount('#cis-header');
