import ResultPerson from "./result/person.js";
import ResultStudent from "./result/student.js";
import ResultPrestudent from "./result/prestudent.js";
import ResultEmployee from "./result/employee.js";
import ResultOrganisationunit from "./result/organisationunit.js";
import ResultRoom from "./result/room.js";
import ResultCms from "./result/cms.js";
import ResultDms from "./result/dms.js";
import ResultMergedperson from "./result/mergedperson.js";
import ResultMergedstudent from "./result/mergedstudent.js";

export default {
	components: {
		ResultPerson,
		ResultStudent,
		ResultPrestudent,
		ResultEmployee,
		ResultOrganisationunit,
		ResultRoom,
		ResultCms,
		ResultDms,
		ResultMergedperson,
		ResultMergedstudent
	},
	props: [ "searchoptions", "searchfunction" ],
	provide() {
		return {
			languages: Vue.computed(() => this.languages),
			query: Vue.computed(() => this.lastQuery)
		};
	},
	data() {
		return {
			searchtimer: null,
			hidetimer: null,
			showsettings: false,
			searchsettings: {
				searchstr: '',
				types: []
			},
			showresult: false,        
			searchresult: [],
			searching: false,
			error: null,
			abortController: null,
			retry: 0,
			languages: null,
			lastQuery: ''
		};
	},
	created() {
		this.$fhcApi.factory
			.language.getAll()
			.then(result => {
				this.languages = result.data.reduce((a, c) => {
					a[c.sprache] = c;
					return a;
				}, {});
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	beforeMount() {
		this.updateSearchOptions();
	},
	methods: {
		updateSearchOptions() {
			this.searchsettings.types = [];
			for (const idx in this.searchoptions.types) {
				this.searchsettings.types.push(this.searchoptions.types[idx]);
			}
		},
		calcSearchResultExtent() {
			var rect = this.$refs.searchbox.getBoundingClientRect();
            //console.log(window.innerWidth + ' ' + window.innerHeight + ' ' + JSON.stringify(rect));
			this.$refs.result.style.height = Math.floor(window.innerHeight * 0.80) + 'px';
		},
		search() {
			if (this.searchtimer !== null) {
				clearTimeout(this.searchtimer);
			}
			if (this.abortController) {
				this.abortController.abort();
				this.abortController = null;
			}
			if (this.searchsettings.searchstr.length >= 2) {
				this.calcSearchResultExtent();
				this.searchtimer = setTimeout(
					this.callsearchapi,
					500
				);
			} else {                
				this.showresult = false;
			}
		},
		callsearchapi() {
			this.error = null;
			this.searchresult = [];
			this.searching = true;
			this.showsearchresult();  
			
			if (this.abortController)
				this.abortController.abort();
			this.abortController = new AbortController();

			this
				.searchfunction(this.searchsettings, { timeout: 50000, signal: this.abortController.signal })
				.then(response => {
					if (!response.data) {
						this.error = this.$p.t('search/error_general');
					} else {
						let res = response.data.map(el => ({...el, ...JSON.parse(el.data)}));
						this.lastQuery = response.meta.searchstring;

						if (this.searchoptions.mergeResults) {
							let counter = 0;
							let mergeTypes = [];
							let mergedType = 'merged';
							let mergeKey = '';

							switch (this.searchoptions.mergeResults) {
							case 'student':
								mergeTypes = ['student', 'prestudent'];
								mergedType += this.searchoptions.mergeResults;
								mergeKey = 'uid';
								break;
							case 'person':
								mergeTypes = ['person', 'employee', 'unassigned_employee', 'mitarbeiter', 'mitarbeiter_ohne_zuordnung', 'student', 'prestudent'];
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
					}
					this.searching = false;
					this.retry = 0;
				})
				.catch(error => {
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
		refreshsearch() {
			this.search();
			this.togglesettings();
		},
		calcSearchSettingsExtent() {
			var rect = this.$refs.settingsbutton.getBoundingClientRect();
            //console.log(window.innerWidth + ' ' + window.innerHeight + ' ' + JSON.stringify(rect));
			this.$refs.settings.style.top = Math.floor(rect.bottom + 3) + 'px';
			this.$refs.settings.style.right = Math.floor(window.innerWidth - rect.right) + 'px';
			this.$refs.settings.style.width = Math.floor(window.innerWidth * 0.5) + 'px';
            //this.$refs.settings.style.height = Math.floor(window.innerHeight * 0.5) + 'px';  
		},
		togglesettings() {
			this.showsettings = !this.showsettings;
			this.calcSearchSettingsExtent();
		},
		hideresult() {
			this.showresult = false;
			window.removeEventListener('resize', this.calcSearchResultExtent);
		},
		showsearchresult() {
			if (this.searchsettings.searchstr.length >= 3) {
				this.showresult = true;
				window.addEventListener('resize', this.calcSearchResultExtent);
			}
		},
		searchfocusin(e) {
			e.preventDefault();
			e.stopPropagation();
			if (this.hidetimer !== null) {
				clearTimeout(this.hidetimer);
			}
		},
		searchfocusout(e) {
			e.preventDefault();
			e.stopPropagation();
			this.hidetimer = setTimeout(
				this.hideresult,
				100
			);
		}
	},
	template: `
	<form
		ref="searchform"
		class="d-flex me-3 position-relative"
		action="javascript:void(0);"
		@focusin="searchfocusin"
		@focusout="searchfocusout"
	>
		<div class="input-group me-2 bg-white">
			<input
				ref="searchbox"
				@input="search"
				@focus="showsearchresult"
				v-model="searchsettings.searchstr"
				class="form-control"
				type="search"
				:placeholder="$p.t('search/input_search_label')"
				:aria-label="$p.t('search/input_search_label')"
			>
			<button
				ref="settingsbutton"
				@click="togglesettings"
				class="btn btn-light border-start"
				type="button"
				id="search-filter"
				:title="$p.t('search/button_filter_label')"
				:aria-label="$p.t('search/button_filter_label')"
				>
				<i class="fas fa-cog"></i>
			</button>
		</div>

		<div
			v-show="showresult"
			ref="result"
			class="searchbar_results"
			tabindex="-1"
		>
			<div v-if="searching">
				<i class="fas fa-spinner fa-spin fa-2x"></i>
			</div>
			<div v-else-if="error !== null">{{ error }}</div>
			<div v-else-if="searchresult.length < 1">{{  $p.t('search/error_no_results') }}</div>
			<template v-else>
				<template v-for="res in searchresult">
					<result-person v-if="res.type === 'person'" :res="res" :actions="searchoptions.actions.person" @actionexecuted="hideresult"></result-person>
					<result-student v-else-if="res.type === 'student'" :res="res" :actions="searchoptions.actions.student" @actionexecuted="hideresult"></result-student>
					<result-prestudent v-else-if="res.type === 'prestudent'" :res="res" :actions="searchoptions.actions.prestudent" @actionexecuted="hideresult"></result-prestudent>
					<result-employee v-else-if="res.type === 'employee'" :res="res" :actions="searchoptions.actions.employee" @actionexecuted="hideresult"></result-employee>
					<result-employee v-else-if="res.type === 'unassigned_employee'" :res="res" :actions="searchoptions.actions.employee" @actionexecuted="hideresult"></result-employee>
					<result-organisationunit v-else-if="res.type === 'organisationunit'" :res="res" :actions="searchoptions.actions.organisationunit" @actionexecuted="hideresult"></result-organisationunit>
					<result-room v-else-if="res.type === 'room'" :res="res" :actions="searchoptions.actions.room" @actionexecuted="hideresult"></result-room>
					<result-cms v-else-if="res.type === 'cms'" :res="res" :actions="searchoptions.actions.cms" @actionexecuted="hideresult"></result-cms>
					<result-dms v-else-if="res.type === 'dms'" :res="res" :actions="searchoptions.actions.dms" @actionexecuted="hideresult"></result-dms>
					<result-mergedperson v-else-if="res.type === 'mergedperson'" :res="res" :actions="searchoptions.actions.mergedperson" @actionexecuted="hideresult"></result-mergedperson>
					<result-mergedstudent v-else-if="res.type === 'mergedstudent'" :res="res" :actions="searchoptions.actions.mergedstudent" @actionexecuted="hideresult"></result-mergedstudent>
					<div v-else class="searchbar-result text-danger fw-bold">{{ $p.t('search/error_unknown_type', res) }}</div>
				</template>
			</template>
		</div>
		<div
			v-show="showsettings"
			ref="settings"
			class="searchbar_settings"
			tabindex="-1"
		>
			<div class="btn-group" v-if="searchoptions.types.length > 0">
				<template v-for="(type, index) in searchoptions.types" :key="type">
					<input
						type="checkbox"
						class="btn-check"
						:id="$.uid + 'search_type_' + index"
						:value="type"
						v-model="searchsettings.types"
					/>
					<label
						class="btn btn-outline-secondary"
						:for="$.uid + 'search_type_' + index"
					>
						{{ type }}
					</label>
				</template>
			</div>
			<div class="mb-2"></div>
			<button
				ref="settingsrefreshsearch"
				@click="refreshsearch"
				class="btn btn-primary"
				type="button"
			>
				{{ $p.t('search/button_applyfilter_label') }}
			</button>
		</div>
	</form>`
};
