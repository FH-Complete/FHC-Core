import person from "./result/person.js";
import room from "./result/room.js";
import employee from "./result/employee.js";
import organisationunit from "./result/organisationunit.js";
import student from "./result/student.js";
import prestudent from "./result/prestudent.js";
import dms from "./result/dms.js";
import cms from "./result/cms.js";
import mergedStudent from "./result/mergedstudent.js";
import mergedPerson from "./result/mergedperson.js";

export default {
	name: "FhcSearchbar",
	components: {
		person,
		room,
		employee,
		organisationunit,
		student,
		prestudent,
		dms,
		cms,
		mergedStudent,
		mergedPerson
	},
    props: [ "searchoptions", "searchfunction" ],
    provide() {
        return {
            query: Vue.computed(() => this.lastQuery)
        };
    },
    data: function() {
      return {
        searchtimer: null,
        hidetimer: null,
        searchsettings: {
            searchstr: this.getSearchStr(),
            types: this.getInitiallySelectedTypes(),
        },
        searchresult: [],
        searchmode: '',
        showresult: false,  
        searching: false,
        error: null,
            abortController: null,
			settingsDropdown: null,
            lastQuery: ''
      };
    },
	computed:{
		searchTypesPlaceholder() {
			if (!this.searchsettings.types.length) {
				return Object.values(this.typeLabels).join(' / ');
			}
			return this.searchsettings.types.map(type => this.typeLabels[type]).join(' / ');
		},
		types() {
			if (!this.searchoptions.types)
				return [];
			if (Array.isArray(this.searchoptions.types))
				return this.searchoptions.types;
			return Object.keys(this.searchoptions.types);
		},
		typeLabels() {
			if (!this.searchoptions.types)
				return {};
			if (Array.isArray(this.searchoptions.types)) {
				return this.searchoptions.types.reduce((res, type) => {
					res[type] = type;
					return res
				}, {});
			}
			return this.searchoptions.types;
		}
	},
	template: /*html*/`
		<form
			ref="searchform"
			class="d-flex me-3"
			action="javascript:void(0);"
			@focusin="searchfocusin"
			@focusout="searchfocusout"
		>
			<div
				ref="searchbox"
				class="h-100 input-group me-2 searchbar_searchbox"
				:class="showresult ? 'open' : 'closed'"
			>
				<span class="input-group-text">
					<i class="fa-solid fa-magnifying-glass"></i>
				</span>
                <input
                	ref="input"
                    @keyup="search"
                    @focus="showsearchresult"
                    v-model="searchsettings.searchstr"
                    class="form-control searchbar_input"
                    type="search"
                    :placeholder="$p.t('search/input_search_label', { types: searchTypesPlaceholder })"
                    :aria-label="$p.t('search/input_search_label', { types: searchTypesPlaceholder })"
                >
				<button
					v-if="searchsettings.searchstr"
					class="searchbar_input_clear btn btn-outline-secondary"
					@click="clearInput"
				>
					<i class="fas fa-close"></i>
				</button>
                <button
                    data-bs-toggle="collapse"
                    data-bs-target="#searchSettings"
                    aria-expanded="false"
                    aria-controls="searchSettings"
                    ref="settingsbutton"
                    class="searchbar_setting_btn btn btn-secondary"
                    type="button"
                    :title="$p.t('search/button_filter_label')"
                    :aria-label="$p.t('search/button_filter_label')"
                >
                    <i class="fas fa-cog"></i>
                </button>
            </div>

            <div v-show="showresult"
                 class="searchbar_results" tabindex="-1">
              <div class="searchbar_results_scroller" ref="result">
                <div class="searchbar_results_wrapper" ref="results">
                  <div v-if="searching">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                  </div>
                  <div v-else-if="this.error !== null">{{ error }}</div>
                  <div v-else-if="searchresult.length < 1">{{  $p.t('search/error_no_results') }}</div>
                  <template v-else v-for="res in searchresult">
                    <component
                        v-if="isValidRenderer(res.renderer)"
                        :is="res.renderer"
                        :mode="searchmode"
                        :res="res"
                        :actions="searchoptions.actions[dash2camelCase(res.renderer)]"
                        @actionexecuted="hideresult"
                    ></component>
                    <div v-else class="searchbar-result text-danger fw-bold">{{ $p.t('search/error_unknown_type', res) }}</div>
                  </template>
                </div>
              </div>
            </div>

			<div
				id="searchSettings"
				ref="settings"
				@[\`shown.bs.collapse\`]="handleShowSettings"
				@[\`hide.bs.collapse\`]="handleHideSettings"
				class="top-100 end-0 searchbar_settings text-white collapse"
				tabindex="-1"
			>
				<div
					v-if="types.length > 0"
					class="d-flex flex-column m-3"
				>
					<span class="fw-light mb-2">
						{{ $p.t('search/applyfilter_label') }}
					</span>
					<template
						v-for="(label, value) in typeLabels"
						:key="value"
					>
						<div class="form-check form-switch">
							<input
								class="fhc-switches form-check-input"
								type="checkbox"
								role="switch"
								:id="$.uid + 'search_type_' + value"
								:value="value"
								v-model="searchsettings.types"
							>
							<label
								class="ps-2 form-check-label non-selectable"
								:for="$.uid + 'search_type_' + value"
							>
								{{ label }}
							</label>
						</div>
					</template>
				</div>
            </div>
		</form>
    `,
    watch:{
		'searchsettings.searchstr': function (newSearchValue) {
			if(this.searchoptions.origin){
				sessionStorage.setItem(`${this.searchoptions.origin}_searchstr`,newSearchValue);
			}
		},
		'searchsettings.types'(newValue) {
			if (Array.isArray(newValue) && newValue.length === 0) {
				this.searchsettings.types = [...this.types];
			}
			// stores the search types in the localstorage, only if the newValue is also an array
			if (Array.isArray(newValue) && this.searchoptions.origin) {
				localStorage.setItem(`${this.searchoptions.origin}_searchtypes`, JSON.stringify(newValue));
			}
			this.search();
		}
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
    	clearInput() {
    		this.searchsettings.searchstr = "";
    		this.hideresult();
    		this.$refs.input.focus()
    	},
		getInitiallySelectedTypes() {
			let result = false;
			if (this.searchoptions.origin) {
				let localStorageValue = localStorage.getItem(`${this.searchoptions.origin}_searchtypes`);
				if (localStorageValue) {
					result = JSON.parse(localStorageValue);
				}
			}
			if (result)
				return result;
			if (!this.searchoptions.types)
				return [];
			if (Array.isArray(this.searchoptions.types))
				return [...this.searchoptions.types];
			return Object.keys(this.searchoptions.types);
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
            if (this.abortController) {
                this.abortController.abort();
                this.abortController = null;
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
                this.error = this.$p.t('search/error_missing_type');
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
                        let mergedType = 'merged-';
                        let mergeKey = '';

                        switch (this.searchoptions.mergeResults) {
                        case 'student':
                            mergeTypes = ['student', 'prestudent'];
                            mergedType += this.searchoptions.mergeResults;
                            mergeKey = 'uid';
                            break;
                        case 'person':
                            mergeTypes = ['person', 'employee', 'student', 'prestudent'];
                            mergedType += this.searchoptions.mergeResults;
                            mergeKey = 'person_id';
                            break;
                        }

                        if (mergeTypes.length) {
                            res = Object.values(res.reduce((a, c) => {
                                if (!mergeTypes.includes(c.renderer)) {
                                    a['nomerge' + counter++] = c;
                                } else if (c[mergeKey] === null) {
                                    a['nomerge' + counter++] = c;
                                } else if (a[c[mergeKey]] === undefined) {
                                    a[c[mergeKey]] = {
                                        rank: c.rank,
                                        renderer: mergedType,
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
        },
        dash2camelCase(string) {
            return string.replace(/-([a-z])/g, g => g[1].toUpperCase());
        },
        isValidRenderer(renderer) {
            const camelCaseRenderer = this.dash2camelCase(renderer);
            return Object.keys(this.$.components).includes(camelCaseRenderer);
        }
    }
};
