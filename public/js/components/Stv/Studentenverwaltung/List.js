import {CoreFilterCmpt} from "../../filter/Filter.js";
import ListNew from './List/New.js';
import CoreTag from '../../Tag/Tag.js';
import { tagHeaderFilter } from "../../../tabulator/filters/extendedHeaderFilter.js";
import { addTagInTable, deleteTagInTable, updateTagInTable } from "../../../../js/helpers/TagHandler.js";
import { tagFormatter } from "../../../../js/tabulator/formatter/tags.js";
import { extendedHeaderFilter } from "../../../tabulator/filters/extendedHeaderFilter.js";
import ApiTag from "../../../api/factory/stv/tag.js";
import ListFilter from './List/Filter.js';

import draggable from '../../../directives/draggable.js';

export default {
	name: "ListPrestudents",
	components: {
		CoreFilterCmpt,
		ListNew,
		CoreTag,
		ListFilter
	},
	directives: {
		draggable
	},
	inject: {
		lists: {
			from: 'lists',
			required: true
		},
		$reloadList: {
			from: '$reloadList',
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
					{
						title: 'Tags',
						field: 'tags',
						tooltip: false,
						headerFilter: "input",
						headerFilterFunc: tagHeaderFilter,
						headerFilterFuncParams: {field: 'tags'},
						formatter: (cell) => tagFormatter(cell, this.$refs.tagComponent),
						width: 150,
					},
					{title:"Nachname", field:"nachname", headerFilter: true},
					{title:"Vorname", field:"vorname", headerFilter: true},
					{title:"Wahlname", field:"wahlname", visible:false, headerFilter: true},
					{title:"Vornamen", field:"vornamen", visible:false, headerFilter: true},
					{title:"TitelPost", field:"titelpost", headerFilter: "list", headerFilterParams: {valuesLookup:true, listOnEmpty:true, autocomplete:true, sort:"asc"}},
					{title:"Ersatzkennzeichen", field:"ersatzkennzeichen", headerFilter: true},
					{
						title: "Geburtsdatum",
						field: "gebdatum",
						formatter: dateFormatter, 
						headerFilter: true,
						headerFilterFunc(headerValue, rowValue) {
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
					return this.$api.call({method: 'post', url, params});
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
				persistenceID: 'stv-list',
			},
			tabulatorEvents: [
				{
					event: 'rowSelectionChanged',
					handler: this.rowSelectionChanged
				},
				{
					event: 'dataProcessed',
					handler: (data) => {
						this.getAllRows()
						this.autoSelectRows(data)
					}
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
				},
				{
					event: 'dataTreeRowExpanded',
					handler: (data) => {
						this.getExpandedRows()
					}
				},
				{
					event: 'dataTreeRowCollapsed',
					handler: (data) => {
						this.getExpandedRows()
					}
				}
			],
			focusObj: null, // TODO(chris): this should be in the filter component
			lastSelected: null,
			filter: [],
			count: 0,
			filteredcount: 0,
			selectedcount: 0,
			currentEndpointRawUrl: '',
			//tags
			expanded: [],
			selectedColumnValues: [],
			tagEndpoint: ApiTag
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
		},
		selectedDragObject() {
			return this.selected.map(item => {
				let type, id;
				if (item.uid) {
					type = 'student';
					id = item.uid;
				} else if (item.prestudent_id) {
					type = 'prestudent';
					id = item.prestudent_id;
				} else if (item.person_id) {
					type = 'person';
					id = item.person_id;
				}
				return {
					...item,
					type,
					id
				};
			});
		},
		downloadConfig() {
			return {
				csv: {
					formatter: 'csv',
					file: this.fileString,
					options: {
						delimiter: ';',
						bom: true,
					}
				}
			};
		},
		fileString() {
			let today = new Date().toLocaleDateString('en-GB')
				.replace(/\//g, '_');
			return "StudentList_" + today + ".csv";
		}
	},
	methods: {
		reload() {
			this.$refs.table.reloadTable();
		},
		actionNewPrestudent() {
			this.$refs.new.open();
		},
		rowSelectionChanged(data, rows) {
			this.selectedcount = data.length;
			this.lastSelected = this.selected;

			//for tags
			this.selectedRows = this.$refs.table.tabulator.getSelectedRows();
			this.selectedColumnValues = this.selectedRows.filter(row => row.getData().prestudent_id !== undefined && row.getData().prestudent_id).map(row => row.getData().prestudent_id);

			this.$emit('update:selected', data);

			// set selected elements draggable
			const tableEl = this.$refs.table?.$refs?.table;
			if (tableEl) {
				const oldDragables = tableEl.querySelectorAll('[draggable]');
				for (const draggable of oldDragables)
					draggable.removeAttribute('draggable');
			}
			rows.forEach(row => row.getElement().draggable = true);
		},
		autoSelectRows(data) {
			if (Array.isArray(this.lastSelected) && this.lastSelected.length){
				// NOTE(chris): reselect rows on refresh
				let selected = this.lastSelected.map(el => this.$refs.table.tabulator.getRow(el.prestudent_id))
				// TODO(chris): unselect current item if it's no longer in the table?
				// or maybe reselect only the last one?
				selected = selected.filter(el => el);

				if (selected.length)
					this.$refs.table.tabulator.selectRow(selected);
			} else if(data && this.lastSelected === undefined) {
				// NOTE(chris): select row if it's the only one (preferably only on startup)
				if (data.length == 1) {
					this.$refs.table.tabulator.selectRow(this.$refs.table.tabulator.getRows());
				}
			}
		},
		updateFilter(filter) {
			this.filter = filter;
			this.updateUrl();
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

			const params = {};
			if (this.filter.length)
				params.filter = this.filter;

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
		dragCleanup(evt) {
			if (evt.dataTransfer.dropEffect == 'none')
				return; // aborted or wrong target
			
			this.$reloadList();
		},
		onKeydown(e) { // TODO(chris): this should be in the filter component
			if (!this.focusObj)
				return;

			var next;
			switch (e.code) {
				case 'Enter':
				case 'Space':
					e.preventDefault();
					var e2 = new Event('click', e);
					e2.altKey = e.altKey;
					e2.ctrlKey = e.ctrlKey;
					e2.shiftKey = e.shiftKey;
					this.focusObj.dispatchEvent(e2);
					//row.component.toggleSelect();
					break;
				case 'ArrowUp':
					e.preventDefault();
					next = this.focusObj.previousElementSibling;
					if (next)
						this.changeFocus(this.focusObj, next);
					break;
				case 'ArrowDown':
					e.preventDefault();
					next = this.focusObj.nextElementSibling;
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
		},
		//methods tags
		addedTag(addedTag)
		{
			addTagInTable(addedTag, this.allRows, 'prestudent_id')
		},
		deletedTag(deletedTag)
		{
			deleteTagInTable(deletedTag, this.allRows);
		},
		updatedTag(updatedTag)
		{
			updateTagInTable(updatedTag, this.allRows)
		},
		getAllRows() {
			this.allRows = this.$refs.table.tabulator.getRows();
		},

	},
	// TODO(chris): focusin, focusout, keydown and tabindex should be in the filter component
	// TODO(chris): filter component column chooser has no accessibilty features
	template: `
	<div class="stv-list h-100 pt-3">
		<div
			class="tabulator-container d-flex flex-column h-100"
			:class="{'has-filter': filter.length}"
			tabindex="0"
			@focusin="onFocus"
			@keydown="onKeydown"
			v-draggable:copyLink.capture="selectedDragObject"
			@dragend="dragCleanup"
		>
			<core-filter-cmpt
				ref="table"
				:description="countsToHTML"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="tabulatorEvents"
				table-only
				:side-menu="false"
				reload
				:download="downloadConfig"
				` + /* TODO(chris): Ausgeblendet für Testing
				new-btn-show
				*/`
				:new-btn-label="$p.t('stv/action_new')"
				@click:new="actionNewPrestudent"
			>

			<template #actions>
				<core-tag ref="tagComponent"
					:endpoint="tagEndpoint"
					:values="selectedColumnValues"
					@added="addedTag"
					@deleted="deletedTag"
					@updated="updatedTag"
					zuordnung_typ="prestudent_id"
				></core-tag>
			</template>

			<template #filter>
				<div class="card">
					<div class="card-body">
						<list-filter @change="updateFilter" />
					</div>
				</div>
			</template>
			</core-filter-cmpt>
		</div>
		<list-new ref="new" :studiengang-kz="studiengangKz" :studiensemester-kurzbz="studiensemesterKurzbz"></list-new>
	</div>`
};
