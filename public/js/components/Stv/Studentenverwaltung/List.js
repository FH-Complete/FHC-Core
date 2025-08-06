import {CoreFilterCmpt} from "../../filter/Filter.js";
import ListNew from './List/New.js';


export default {
	name: "ListPrestudents",
	components: {
		CoreFilterCmpt,
		ListNew
	},
	inject: {
		'lists': {
			from: 'lists',
			required: true
		},
		currentSemester: {
			from: 'currentSemester',
			required: true
		}
	},
	props: {
		selected: Array,
		studiengangKz: Number,
		studiensemesterKurzbz: String
	},
	emits: [
		'update:selected'
	],
	data() {
		function dateFormatter(cell)
		{
			let val = cell.getValue();
			if (!val)
				return '&nbsp;';
			let date = new Date(val);
			return date.toLocaleDateString('de-AT', {
				"day": "2-digit",
				"month": "2-digit",
				"year": "numeric"
			});
		}

		return {
			tabulatorOptions: {
				columns:[
					{title:"UID", field:"uid", headerFilter: true},
					{title:"TitelPre", field:"titelpre", headerFilter: "list", headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"}},
					{title:"Nachname", field:"nachname", headerFilter: true},
					{title:"Vorname", field:"vorname", headerFilter: true},
					{title:"Wahlname", field:"wahlname", visible:false, headerFilter: true},
					{title:"Vornamen", field:"vornamen", visible:false, headerFilter: true},
					{title:"TitelPost", field:"titelpost", headerFilter: "list", headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"}},
					{title:"SVNR", field:"svnr", headerFilter: true},
					{title:"Ersatzkennzeichen", field:"ersatzkennzeichen", headerFilter: true},
					{title:"Geburtsdatum", field:"gebdatum", formatter:dateFormatter, 
						headerFilter: true, headerFilterFunc: function(headerValue, rowValue, rowData, filterParams) {
							const matches = headerValue.match(/^(([0-9]{2})\.)?([0-9]{2})\.([0-9]{4})?$/);
							let comparestr = headerValue;
							if(matches !== null) {
								const year = (matches[4] !== undefined) ? matches[4] : '';
								const month = matches[3];
								const day = (matches[2] !== undefined) ? matches[2] : '';
								comparestr = year + '-' + month + '-' + day;
							}
							return rowValue.match(comparestr);
						}
					},
					{title:"Geschlecht", field:"geschlecht", headerFilter: "list", headerFilterParams: {values:{'m':'männlich','w':'weiblich','x':'divers','u':'unbekannt'}, listOnEmpty:true, autocomplete:true}},
					{title:"Sem.", field:"semester", headerFilter: "list", headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"}},
					{title:"Verb.", field:"verband", headerFilter: "list", headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"}},
					{title:"Grp.", field:"gruppe", headerFilter: "list", headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"}},
					{title:"Studiengang", field:"studiengang", headerFilter: "list", headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"}},
					{title:"Studiengang_kz", field:"studiengang_kz", visible:false, headerFilter: true},
					{title:"Personenkennzeichen", field:"matrikelnr", headerFilter: true},
					{title:"PersonID", field:"person_id", headerFilter: true},
					{title:"Status", field:"status", headerFilter: "list", headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"}},
					{title:"Status Datum", field:"status_datum", visible:false, formatter:dateFormatter},
					{title:"Status Bestaetigung", field:"status_bestaetigung", visible:false, formatter:dateFormatter, headerFilter: true},
					{title:"EMail (Privat)", field:"mail_privat", visible:false, headerFilter: true},
					{title:"EMail (Intern)", field:"mail_intern", visible:false, headerFilter: true},
					{title:"Anmerkungen", field:"anmerkungen", visible:false, headerFilter: true},
					{title:"AnmerkungPre", field:"anmerkung", visible:false, headerFilter: true},
					{title:"OrgForm", field:"orgform_kurzbz", headerFilter: "list", headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"}},
					{title:"Aufmerksamdurch", field:"aufmerksamdurch_kurzbz", visible:false},
					{title:"Gesamtpunkte", field:"punkte", visible:false},
					{title:"Aufnahmegruppe", field:"aufnahmegruppe_kurzbz", visible:false},
					{title:"Dual", field:"dual", visible:false, 
						formatter:'tickCross', formatterParams: {
							tickElement: '<i class="fas fa-check text-success"></i>',
							crossElement: '<i class="fas fa-times text-danger"></i>'
						},						
						headerFilter:"tickCross", headerFilterParams: {
							"tristate":true, elementAttributes:{"value":"true"}
						}, headerFilterEmptyCheck:function(value){return value === null}
					},
					{title:"Matrikelnummer", field:"matr_nr", visible:false, headerFilter: true},
					{title:"Studienplan", field:"studienplan_bezeichnung", headerFilter: "list", headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"}},
					{title:"PreStudentInnenID", field:"prestudent_id", headerFilter: true},
					{title:"Priorität", field:"priorisierung_relativ"},
					{title:"Mentor", field:"mentor", visible:false},
					{title:"Aktiv", field:"bnaktiv", visible:false, 
						formatter:'tickCross', formatterParams: {
							allowEmpty:true,
							tickElement: '<i class="fas fa-check text-success"></i>',
							crossElement: '<i class="fas fa-times text-danger"></i>'
						},						
						headerFilter:"tickCross", headerFilterParams: {
							"tristate":true, elementAttributes:{"value":"true"}
						}, headerFilterEmptyCheck:function(value){return value === null}
					},
				],
				rowFormatter(row) {
					if (row.getData().bnaktiv === false) {
						row.getElement().classList.add('text-black','text-opacity-50','fst-italic');
					}
				},

				ajaxRequestFunc: (url, config, params) => {
					if( url === '' ) 
					{
						return Promise.resolve({ data: []});
					}
					return this.$api.call({url, params});
				},
				ajaxResponse: (url, params, response) => {
					return response?.data;
				},

				layout: 'fitDataStretch',
				layoutColumnsOnNewData: false,
				height: '100%',
				selectable: true,
				selectableRangeMode: 'click',
				index: 'prestudent_id',
				persistenceID: 'stv-list'
			},
			tabulatorEvents: [
				{
					event: 'rowSelectionChanged',
					handler: this.rowSelectionChanged
				},
				{
					event: 'dataProcessed',
					handler: this.autoSelectRows
				},
				{
					event: 'dataLoaded',
					handler: data => this.count = data.length
				},
				{
					event: 'dataFiltered',
					handler: (filters, rows) => this.filteredcount = rows.length
				},
				{
					event: 'rowClick',
					handler: this.handleRowClick // TODO(chris): this should be in the filter component
				}
			],
			focusObj: null, // TODO(chris): this should be in the filter component
			lastSelected: null,
			filterKontoCount0: undefined,
			filterKontoMissingCounter: undefined,
			count: 0,
			filteredcount: 0,
			selectedcount: 0,
			currentEndpointRawUrl: ''
		}
	},
	methods: {
		reload() {
			this.$refs.table.reloadTable();
		},
		actionNewPrestudent() {
			this.$refs.new.open();
		},
		rowSelectionChanged(data) {
			this.selectedcount = data.length;
			this.lastSelected = this.selected;
			this.$emit('update:selected', data);
		},
		autoSelectRows(data) {
			if (this.lastSelected) {
				// NOTE(chris): reselect rows on refresh
				let selected = this.lastSelected.map(el => this.$refs.table.tabulator.getRow(el.prestudent_id))
				// TODO(chris): unselect current item if it's no longer in the table?
				// or maybe reselect only the last one?
				selected = selected.filter(el => el);

				if (selected.length)
					this.$refs.table.tabulator.selectRow(selected);
			} else if(this.lastSelected === undefined) {
				// NOTE(chris): select row if it's the only one (preferably only on startup)
				if (data.length == 1) {
					this.$refs.table.tabulator.selectRow(this.$refs.table.tabulator.getRows());
				}
			}
		},
		updateUrl(endpoint, first) {
			this.lastSelected = first ? undefined : this.selected;

			if( endpoint === undefined ) 
			{
				endpoint = {url: this.currentEndpointRawUrl};
			} 
			else if( endpoint.url === undefined ) 
			{
				endpoint.url = this.currentEndpointRawUrl;
			} else
			{
				this.currentEndpointRawUrl = endpoint.url;
			}

			endpoint.url = endpoint.url.replace(
				'CURRENT_SEMESTER',
				encodeURIComponent(this.currentSemester)
				);

			const params = {}, filter = {};
			if (this.filterKontoCount0)
				filter.konto_count_0 = this.filterKontoCount0;
			if (this.filterKontoMissingCounter)
				filter.konto_missing_counter = this.filterKontoMissingCounter;

			if (filter.konto_count_0 || filter.konto_missing_counter)
				params.filter = filter;

			if (!this.$refs.table.tableBuilt) {
				if (!this.$refs.table.tabulator) {
					this.tabulatorOptions.ajaxURL = endpoint.url;
					this.tabulatorOptions.ajaxParams = params;
				} else
					this.$refs.table.tabulator.on("tableBuilt", () => {
						this.$refs.table.tabulator.setData(endpoint.url, params);
					});
			} else
				this.$refs.table.tabulator.setData(endpoint.url, params);
		},
		onKeydown(e) { // TODO(chris): this should be in the filter component
			if (!this.focusObj)
				return;
			switch (e.code) {
				case 'Enter':
				case 'Space':
					e.preventDefault();
					const e2 = new Event('click', e);
					e2.altKey = e.altKey;
					e2.ctrlKey = e.ctrlKey;
					e2.shiftKey = e.shiftKey;
					this.focusObj.dispatchEvent(e2);
					//row.component.toggleSelect();
					break;
				case 'ArrowUp':
					e.preventDefault();
					var next = this.focusObj.previousElementSibling;
					if (next)
						this.changeFocus(this.focusObj, next);
					break;
				case 'ArrowDown':
					e.preventDefault();
					var next = this.focusObj.nextElementSibling;
					if (next)
						this.changeFocus(this.focusObj, next);
					break;
			}
		},
		changeFocus(a, b) { // TODO(chris): this should be in the filter component
			if (b) {
				b.tabIndex = 0;
				this.focusObj = b;
				b.focus();
			} else {
				this.focusObj = null;
			}
			a.tabIndex = -1;
			return this.focusObj;
		},
		onFocus(e) { // TODO(chris): this should be in the filter component
			if (!this.focusObj) {
				var container, target;
				if (e.target.classList.contains('tabulator-container')) {
					container = e.target;
					target = container.querySelector('.tabulator-row');
				} else if (e.target.classList.contains('tabulator-row')) {
					container = e.target.closest('.tabulator-container');
					target = e.target;
				}
				if (container && target) {
					this.changeFocus(container, target);
				}
			}
		},
		handleRowClick(e, row) { // TODO(chris): this should be in the filter component
			if (this.focusObj) {
				let el = row.getElement();
				if (el != this.focusObj)
					this.changeFocus(this.focusObj, el);
			}
		}
	},
	computed: {
		countsToHTML: function() {
			return this.$p.t('global/ausgewaehlt')
				+ ': <strong>' + (this.selectedcount || 0) + '</strong>'
				+ ' | '
				+ this.$p.t('global/gefiltert')
				+ ': '
				+ '<strong>' + (this.filteredcount || 0) + '</strong>'
				+ ' | '
				+ this.$p.t('global/gesamt')
				+ ': <strong>' + (this.count || 0) + '</strong>';
		}
	},
	// TODO(chris): focusin, focusout, keydown and tabindex should be in the filter component
	// TODO(chris): filter component column chooser has no accessibilty features
	template: `
	<div class="stv-list h-100 pt-3">
		<div class="tabulator-container d-flex flex-column h-100" :class="{'has-filter': filterKontoCount0 || filterKontoMissingCounter}" tabindex="0" @focusin="onFocus" @keydown="onKeydown">
			<core-filter-cmpt
				ref="table"
				:description="countsToHTML"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="tabulatorEvents"
				table-only
				:side-menu="false"
				reload
				` + /* TODO(chris): Ausgeblendet für Testing
				new-btn-show
				*/`
				:new-btn-label="$p.t('stv/action_new')"
				@click:new="actionNewPrestudent"
			>
			<template #filter>
				<div class="card">
					<div class="card-body">
						<div class="input-group mb-3">
							<label class="input-group-text col-4" for="stv-list-filter-konto-count-0">{{ $p.t('stv/konto_filter_count_0') }}</label>
							<select class="form-select" id="stv-list-filter-konto-count-0" v-model="filterKontoCount0" @input="$nextTick(updateUrl)">
								<option v-for="typ in lists.buchungstypen" :key="typ.buchungstyp_kurzbz" :value="typ.buchungstyp_kurzbz">
									{{ typ.beschreibung }}
								</option>
							</select>
							<button v-if="filterKontoCount0" class="btn btn-outline-secondary" @click="filterKontoCount0 = undefined; updateUrl()">
								<i class="fa fa-times"></i>
							</button>
						</div>
						<div class="input-group">
							<label class="input-group-text col-4" for="stv-list-filter-konto-missing-counter">{{ $p.t('stv/konto_filter_missing_counter') }}</label>
							<select class="form-select" id="stv-list-filter-konto-missing-counter" v-model="filterKontoMissingCounter" @input="$nextTick(updateUrl)">
								<option value="alle">{{ $p.t('stv/konto_all_types') }}</option>
								<option v-for="typ in lists.buchungstypen" :key="typ.buchungstyp_kurzbz" :value="typ.buchungstyp_kurzbz">
									{{ typ.beschreibung }}
								</option>
							</select>
							<button v-if="filterKontoMissingCounter" class="btn btn-outline-secondary" @click="filterKontoMissingCounter = undefined; updateUrl()">
								<i class="fa fa-times"></i>
							</button>
						</div>
					</div>
				</div>
			</template>
			</core-filter-cmpt>
		</div>
		<list-new ref="new" :studiengang-kz="studiengangKz" :studiensemester-kurzbz="studiensemesterKurzbz"></list-new>
	</div>`
};
