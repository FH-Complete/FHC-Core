import person from "./result/person.js";
import raum from "./result/room.js";
import employee from "./result/employee.js";
import organisationunit from "./result/organisationunit.js";
import student from "./result/student.js";
import prestudent from "./result/prestudent.js";
import dms from "./result/dms.js";
import cms from "./result/cms.js";
import mergedStudent from "./result/mergedstudent.js";
import mergedPerson from "./result/mergedperson.js";

import ApiLanguage from "../../api/factory/language.js"

export default {
    props: [ "searchoptions", "searchfunction" ],
    provide() {
        return {
            languages: Vue.computed(() => this.languages),
            query: Vue.computed(() => this.lastQuery)
        };
    },
    data: function() {
      return {
        searchtimer: null,
        hidetimer: null,
        searchsettings: {
            searchstr: this.getSearchStr(),
            types: this.getSearchTypes(),
        },
        searchresult: [],
        searchmode: '',
        showresult: false,  
        searching: false,
        error: null,
            abortController: null,
        settingsDropdown:null,
            languages: null,
            lastQuery: ''
      };
    },
    components: {
      person: person,
      raum: raum,
      employee: employee,
      organisationunit: organisationunit,
      student: student,
      prestudent: prestudent,
      dms,
      cms,
      mergedStudent,
      mergedPerson
    },
    template: /*html*/`
          <form ref="searchform" class="d-flex me-3" :class="searchoptions.cssclass" action="javascript:void(0);"
		 	 @focusin="this.searchfocusin" @focusout="this.searchfocusout">
			<div ref="searchbox" class="h-100 input-group me-2">
				<span class="input-group-text">
					<i class="fa-solid fa-magnifying-glass"></i>
				</span>
                <input @keyup="this.search" @focus="this.showsearchresult"
                    v-model="this.searchsettings.searchstr" class="form-control"
                    type="search" :placeholder="'Search: '+ search_types_string" aria-label="Search">
                <span data-bs-toggle="collapse" data-bs-target="#searchSettings" aria-expanded="false" aria-controls="searchSettings" ref="settingsbutton"  class="input-group-text" type="button"><i class="fas fa-cog"></i></span>
            </div>

            <div v-show="this.showresult"
                 class="searchbar_results" tabindex="-1">
              <div class="searchbar_results_scroller" ref="result">
                <div class="searchbar_results_wrapper" ref="results">
                  <div v-if="this.searching">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                  </div>
                  <div v-else-if="this.error !== null">{{ this.error }}</div>
                  <div v-else-if="searchresult.length < 1">Es wurden keine Ergebnisse gefunden.</div>
                  <template v-else="" v-for="res in searchresult">
                    <person v-if="res.type === 'person'" :res="res" :actions="this.searchoptions.actions.person" @actionexecuted="this.hideresult"></person>
                    <student v-else-if="res.type === 'student' || res.type === 'studentStv'" :mode="searchmode" :res="res" :actions="this.searchoptions.actions.student" @actionexecuted="this.hideresult"></student>
                    <prestudent v-else-if="res.type === 'prestudent'" :mode="searchmode" :res="res" :actions="this.searchoptions.actions.prestudent" @actionexecuted="this.hideresult"></prestudent>
                    <merged-student v-else-if="res.type === 'mergedstudent'" :mode="searchmode" :res="res" :actions="this.searchoptions.actions.mergedstudent" @actionexecuted="this.hideresult"></merged-student>
                    <merged-person v-else-if="res.type === 'mergedperson'" :mode="searchmode" :res="res" :actions="this.searchoptions.actions.mergedperson" @actionexecuted="this.hideresult"></merged-person>
                    <employee v-else-if="res.type === 'mitarbeiter' || res.type === 'mitarbeiter_ohne_zuordnung' || res.type === 'employee'  || res.type === 'unassigned_employee'" :res="res" :actions="this.searchoptions.actions.employee" @actionexecuted="this.hideresult"></employee>
                    <organisationunit v-else-if="res.type === 'organisationunit'" :res="res" :actions="this.searchoptions.actions.organisationunit" @actionexecuted="this.hideresult"></organisationunit>
                    <raum v-else-if="res.type === 'raum' || res.type === 'room'" :mode="searchmode" :res="res" :actions="this.searchoptions.actions.raum" @actionexecuted="this.hideresult"></raum>
                    <dms v-else-if="res.type === 'dms'" :res="res" :actions="searchoptions.actions.dms" @actionexecuted="hideresult"></dms>
                    <cms v-else-if="res.type === 'cms'" :res="res" :actions="searchoptions.actions.cms" @actionexecuted="hideresult"></cms>
                    <div v-else="">Unbekannter Ergebnistyp: '{{ res.type }}'.</div>
                  </template>
                </div>
              </div>
            </div>

            <div id="searchSettings"  ref="settings"
				@[\`shown.bs.collapse\`]="handleShowSettings"
				@[\`hide.bs.collapse\`]="handleHideSettings"
                class="top-100 end-0 searchbar_settings text-white collapse" tabindex="-1">
              <div class="d-flex flex-column m-3" v-if="this.searchoptions.types.length > 0">
              <span class="fw-light mb-2">Suche filtern nach:</span>  
              <template v-for="(type, index) in this.searchoptions.types" :key="type">
                    <div class="form-check form-switch">
                        <input class="fhc-switches form-check-input" type="checkbox" role="switch" :id="this.$.uid + 'search_type_' + index" :value="type" v-model="searchsettings.types"  />
                        <label class="ps-2 form-check-label non-selectable" :for="this.$.uid + 'search_type_' + index">{{ type }}</label>
                    </div>
                </template>
              </div>
            </div>
        
          </form>
    `,
    watch:{
		'searchsettings.searchstr': function (newSearchValue, oldSearchValue) {
			if(this.searchoptions.origin){
				sessionStorage.setItem(`${this.searchoptions.origin}_searchstr`,newSearchValue);
			}
		},
    },
	computed:{
		search_types_string(){
			if (Array.isArray(this.searchsettings.types) && this.searchsettings.types.length > 0){
				return this.searchsettings.types.join(' / ');
			}else{
				return JSON.stringify(this.searchsettings.types);
			}
		},
		
	},
    created() {
        this.$api
            .call(ApiLanguage.getAll())
            .then(result => {
                this.languages = result.data.reduce((a, c) => {
                    a[c.sprache] = c;
                    return a;
                }, {});
            })
            .catch(this.$fhcAlert.handleSystemError);
    },
    beforeMount: function() {
		this.$watch('searchsettings.types', (newValue, oldValue) => {
			if (Array.isArray(newValue) && newValue.length === 0){
				this.searchsettings.types = this.allSearchTypes();
			}
			// stores the search types in the localstorage, only if the newValue is also an array
			if(Array.isArray(newValue) && this.searchoptions.origin){
				localStorage.setItem(`${this.searchoptions.origin}_searchtypes`, JSON.stringify(newValue));
			}
			this.search();
		});
    },
	mounted(){
		this.settingsDropdown = new bootstrap.Collapse(this.$refs.settings, {
			toggle: false
		});

		if (!this.searchoptions.origin){
			console.warn("No origin defined in the searchoptions for the searchbar, please define the origin property in the searchbaroptions to allow reliable storage of searchstr and searchtypes accross applications.");
		}
	},
	updated() {
		if(this.showresult) {
			Vue.nextTick(() => {
				this.calcSearchResultHeight();
			});
		}
	},
    methods: {
		getSearchTypes: function () {
			let result = this.allSearchTypes();
			if (this.searchoptions.origin) {
				let localStorageValue = localStorage.getItem(`${this.searchoptions.origin}_searchtypes`);
				if (localStorageValue) {
					result = JSON.parse(localStorageValue);
				}
			}
			return result;
		},
		allSearchTypes() {
			let allTypes = [];
			for (const idx in this.searchoptions.types) {
				allTypes.push(this.searchoptions.types[idx]);
			};
			return allTypes;
		},
		getSearchStr: function(){
			if (!this.searchoptions.origin)
				return '';
			return sessionStorage.getItem(`${this.searchoptions.origin}_searchstr`) ?? '';
		},
		checkSettingsVisibility: function(event) {
			// hides the settings collapsible if the user clicks somewhere else
			if (!this.$refs.settings.contains(event.target))
			{
				this.settingsDropdown.hide();
			}
		},
		handleShowSettings: function() {
			// adds the event listener checkSettingsVisibility only when the collapsible is shown
			document.addEventListener("click", this.checkSettingsVisibility);
		},
		handleHideSettings: function () {
			// removes the event listener checkSettingsVisibility when the collapsible is hidden
			document.removeEventListener("click", this.checkSettingsVisibility);
		},
        allSearchOptions: function() {
            this.searchsettings.types = [];
            for( const idx in this.searchoptions.types ) {
                this.searchsettings.types.push(this.searchoptions.types[idx]);
            }
        },
		calcSearchResultHeight: function() {
			const rect = this.$refs.results.getBoundingClientRect();
			if( rect.height > 0 && rect.height < (window.innerHeight * 0.8) ) {
				this.$refs.result.style.height = Math.ceil(rect.height + 16) + 'px';
			} else {
				this.$refs.result.style.height = Math.floor(window.innerHeight * 0.8) + 'px';
			}
		},
        calcSearchResultExtent: function() {
			if(!this.showresult) {
				return;
			}
			if(this.searchoptions?.calcheightonly === undefined 
				|| this.searchoptions.calcheightonly === false) {
				var rect = this.$refs.searchbox.getBoundingClientRect();
				this.$refs.result.style.top = Math.floor(rect.bottom + 3) + 'px';
				this.$refs.result.style.right = Math.floor(rect.right) + 'px';
				this.$refs.result.style.width = Math.floor(rect.width) + 'px';
			}
            this.calcSearchResultHeight();
        },
        search: function() {
            if( this.searchtimer !== null ) {
                clearTimeout(this.searchtimer);
            }
            if( this.searchsettings.searchstr.length >= 2 ) {
                this.calcSearchResultExtent();
                this.searchtimer = setTimeout(
                    this.callsearchapi,
                    500
                );
            } else {                
                this.showresult = false;
            }
        },
        callsearchapi: function() {
            this.error = null;
            this.searchresult.splice(0, this.searchresult.length);
            this.searching = true;
            this.showsearchresult();
            if(this.searchsettings.types.length === 0) {
                this.error = 'Kein Ergebnistyp ausgewählt. Bitte mindestens einen Ergebnistyp auswählen.';
                this.searching = false;
                return;
            }

            if (this.abortController)
                this.abortController.abort();
            this.abortController = new AbortController();

            this.searchfunction(this.searchsettings, { timeout: 50000, signal: this.abortController.signal })
            .then(response=>{
                if (!response.data) {
                    this.error = this.$p.t('search/error_general');
                } else {
                    let res = response.data.map(el => el.data ? {...el, ...JSON.parse(el.data)} : el);
                    this.lastQuery = response.meta.searchstring;
                    if (this.searchoptions.mergeResults) {
                        let counter = 0;
                        let mergeTypes = [];
                        let mergedType = 'merged';
                        let mergeKey = '';

                        switch (this.searchoptions.mergeResults) {
                        case 'student':
                            mergeTypes = ['student', 'studentStv', 'prestudent'];
                            mergedType += this.searchoptions.mergeResults;
                            mergeKey = 'uid';
                            break;
                        case 'person':
                            mergeTypes = ['person', 'employee', 'unassigned_employee', 'mitarbeiter', 'mitarbeiter_ohne_zuordnung', 'student', 'studentStv', 'prestudent'];
                            mergedType += this.searchoptions.mergeResults;
                            mergeKey = 'person_id';
                            break;
                        }

                        if (mergeTypes.length) {
                            res = Object.values(res.reduce((a, c) => {
                                if (!mergeTypes.includes(c.type)) {
                                    a['nomerge' + counter++] = c;
                                } else if (c[mergeKey] === null) {
                                    a['nomerge' + counter++] = c;
                                } else if (a[c[mergeKey]] === undefined) {
                                    a[c[mergeKey]] = {
                                        rank: c.rank,
                                        type: mergedType,
                                        list: [c]
                                    };
                                } else {
                                    a[c[mergeKey]].list.push(c);
                                    if (c.rank > a[c[mergeKey]].rank)
                                        a[c[mergeKey]].rank = c.rank;
                                }
                                return a;
                            }, {})).sort((a, b) => b.rank - a.rank);
                        }
                    }
                    this.searchresult = res;
                    this.searchmode = response.meta.mode;
                }
                this.searching = false;
                this.retry = 0;
            })
            .catch(error=> {
                if (error.code == "ERR_CANCELED") {
                    return this.retry = 0;
                }
                if (error.code == "ECONNABORTED" && this.retry) {
                    this.retry--;
                    return this.callsearchapi();
                }

                this.error = this.$p.t('search/error_general', error);
                this.searching = false;
                this.retry = 0;
            });
        },
        refreshsearch: function() {
          this.search();
          this.togglesettings();
        },
        hideresult: function() {
            this.showresult = false;
            window.removeEventListener('resize', this.calcSearchResultExtent);
        },
        showsearchresult: function() {
            if( this.searchsettings.searchstr.length >= 2 ) {
                this.showresult = true;
                window.addEventListener('resize', this.calcSearchResultExtent);
				this.calcSearchResultExtent();
            }
        },
        searchfocusin: function(e) {
            e.preventDefault();
            e.stopPropagation();
            if( this.hidetimer !== null ) {
                clearTimeout(this.hidetimer);
            }
        },
        searchfocusout: function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.hidetimer = setTimeout(
                this.hideresult,
                100
            );
        }
    }
};
