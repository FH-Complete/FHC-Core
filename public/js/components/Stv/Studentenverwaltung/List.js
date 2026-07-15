import {CoreFilterCmpt} from "../../filter/Filter.js";
import ListNew from './List/New.js';
import CoreTag from '../../Tag/Tag.js';
import { tagHeaderFilter } from "../../../tabulator/filters/extendedHeaderFilter.js";
import { addTagInTable, deleteTagInTable, updateTagInTable } from "../../../../js/helpers/TagHelper.js";
import { tagFormatter } from "../../../../js/tabulator/formatter/tags.js";

import ApiTag from "../../../api/factory/stv/tag.js";
import ListFilter from './List/Filter.js';

import { capitalize } from '../../../helpers/StringHelpers.js';

import draggable from '../../../directives/draggable.js';

import StvColumns from '../../../../../index.ci.php/js/tabulatorcolumns/stv';

import ModuleFilterFilters from '../../../tabulator/filter/filters.js';
Tabulator.extendModule("filter", "filters", ModuleFilterFilters);

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
		},
		tagsEnabled: {
			from: 'configStvTagsEnabled',
			default: false
		},
	},
	props: {
		selected: Array,
		studiengangKz: Number,
		studiensemesterKurzbz: String
	},
	emits: [
		'update:selected',
		'filterActive'
	],
	data() {
		return {
			tabulatorOptions: {
				columns: StvColumns,
				locale: true,
				rowFormatter(row) {
					if (row.getData().bnaktiv === false) {
						row.getElement().classList.add('text-black','text-opacity-50','fst-italic');
					}
					row.getElement().draggable = true
				},

				ajaxRequestFunc: (url, config, params) => {
					if( url === '' ) 
					{
						return Promise.resolve({ data: []});
					}
					/**
					 * NOTE(chris): Because of a bug in Tabulator
					 * we need to get the params from elsewhere.
					 * @see https://github.com/olifolkerd/tabulator/issues/4318
					 */
					const apiconfig = {
						...this.tabulatorOptions.ajaxConfig,
						url: this.tabulatorOptions.ajaxURL,
						params: this.tabulatorOptions.ajaxParams
					};
					return this.$api.call(apiconfig);
				},
				ajaxResponse: (url, params, response) => {
					return response?.data;
				},

				layout: 'fitDataStretch',
				layoutColumnsOnNewData: false,
				height: '100%',
				selectableRows: true,
				selectableRowsRangeMode: 'click',
				index: 'prestudent_id',
				persistenceID: 'stv-list',
			},
			tabulatorEvents: [
				{
					event: 'rowSelectionChanged',
					handler: this.rowSelectionChanged
				},
				{
					event: 'dataLoading',
					handler: this.handleDataLoading
				},
				{
					event: 'renderComplete',
					handler: this.handleRenderComplete
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
				},
				{
					event: 'rowMouseDown',
					handler: this.handleMouseDown
				}
			],
			focusObj: null, // TODO(chris): this should be in the filter component
			lastSelected: null,
			filter: [],
			count: 0,
			filteredcount: 0,
			selectedcount: 0,
			//tags
			expanded: [],
			selectedColumnValues: [],
			tagEndpoint: ApiTag,
			currentEndpoint: null,
			headerFilterActive: false,
			dragSource: [],
			oldScrollUrl: '',
			oldScrollLeft: 0,
			oldScrollTop: 0
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
			let items = this.dragSource?.length ? this.dragSource : this.selected;

			return items.map(item => {
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
		},
	},
	created() {
		if(this.tagsEnabled) {
			const coltags = {
				title: 'Tags',
				field: 'tags',
				tooltip: false,
				headerFilter: "input",
				headerFilterFunc: tagHeaderFilter,
				headerFilterFuncParams: {field: 'tags'},
				formatter: (cell) => tagFormatter(cell, this.$refs.tagComponent),
				width: 150,
			};
			this.tabulatorOptions.columns.splice(2, 0, coltags);
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
			this.$emit('filterActive', filter);
			this.updateUrl();
		},
		updateUrl(endpoint, first) {
			this.lastSelected = first ? undefined : this.selected;

/*			console.log('function param endpoint: ' + JSON.stringify(endpoint));
			console.log('current endpoint: ' + JSON.stringify(this.currentEndpoint));*/

			if( endpoint === undefined && this.currentEndpoint === null)
			{
				endpoint = { url: '' };
			}
			else if( endpoint === undefined )
			{
				endpoint = JSON.parse(JSON.stringify(this.currentEndpoint));
			}
			else
			{
				this.currentEndpoint = JSON.parse(JSON.stringify(endpoint));
			}

			endpoint.url = endpoint.url.replace(
				'CURRENT_SEMESTER',
				encodeURIComponent(this.currentSemester)
				);

			const params = (endpoint?.params !== undefined) ? endpoint.params : {};
			let method = (endpoint?.method !== undefined) ? endpoint.method : 'get';
			if (this.filter.length && !endpoint.url.match(/\/search\//))
			{
				params.filter = this.filter;
				method = 'post';
			}

			this.tabulatorOptions.ajaxURL = endpoint.url;
			this.tabulatorOptions.ajaxParams = { ...params };
			this.tabulatorOptions.ajaxConfig = {method};

			if (!this.$refs.table.tableBuilt) {
				if (this.$refs.table.tabulator) {
					this.$refs.table.tabulator.on("tableBuilt", () => {
						this.$refs.table.tabulator.setData(endpoint.url, params, method);
					});
				}
			} else
				this.$refs.table.tabulator.setData(endpoint.url, params, method);
		},
		dragCleanup(evt) {
			this.dragSource = [];
			if (evt.dataTransfer.dropEffect == 'none')
				return; // aborted or wrong target
			
			this.$reloadList();
		},
		onKeydown(e) { // TODO(chris): this should be in the filter component
			if (!this.focusObj)
				return;

			// Ignore typing inside editable elements
			if (
				e.target instanceof HTMLInputElement ||
				e.target instanceof HTMLTextAreaElement ||
				e.target.isContentEditable
			)
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
		resetFilter(){
			this.$refs.listfilter.resetFilter();
			this.$refs.table.clearFilters();
		},
		handleHeaderFilter(filterActive){
			this.headerFilterActive = filterActive;
		},
		handleMouseDown(e, row)
		{
			let data = row.getData();
			let id = data.uid ?? data.prestudent_id ?? data.person_id;

			const isAlreadySelected = this.selected?.some(row => (row.uid ?? row.prestudent_id ?? row.person_id) === id);

			this.dragSource = (isAlreadySelected && this.selected?.length) ? this.selected : [data];
		},
		handleDataLoading() {
			this.oldScrollLeft = this.$refs.table.tabulator.rowManager.scrollLeft;
			this.oldScrollTop = this.$refs.table.tabulator.rowManager.scrollTop;
		},
		handleRenderComplete() {
			const table = this.$refs.table.tabulator.element.querySelector('.tabulator-tableholder');
			if(table) {
				const curAjaxUrl = this.$refs.table.tabulator.getAjaxUrl();
				if(this.oldScrollUrl === curAjaxUrl) {
					table.scrollLeft = this.oldScrollLeft;
					table.scrollTop = this.oldScrollTop;
				} else {
					this.oldScrollLeft = table.scrollLeft;
					this.oldScrollTop = table.scrollTop;
				}
				this.oldScrollUrl = this.$refs.table.tabulator.getAjaxUrl();
			}
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
				new-btn-show
				:new-btn-label="$p.t('stv/action_new')"
				@click:new="actionNewPrestudent"
				:useSelectionSpan="false"
				@headerFilterOn="handleHeaderFilter"
			>

			<template #actions>
				<core-tag ref="tagComponent"
					v-if="tagsEnabled"
					:endpoint="tagEndpoint"
					:values="selectedColumnValues"
					@added="addedTag"
					@deleted="deletedTag"
					@updated="updatedTag"
					zuordnung_typ="prestudent_id"
				></core-tag>
			</template>

			<template #actions v-if="filter.length || headerFilterActive">
			  <div class="d-flex justify-content-center align-items-center gap-2 ps-4 position-absolute start-50 translate-middle-x">
				<p class="text-danger mb-0">
				  <strong>{{$p.t('filter','filterActive')}}</strong>
				</p>

				<button
				  class="btn btn-outline-danger sm"
				  :title="$p.t('filter/filterDelete')"
				  @click="resetFilter"
				>
				 <span class="fa-solid fa-filter-circle-xmark"></span>
				</button>
			  </div>
			</template>

			<template #filter>
				<div class="card mt-2">
					<div class="card-body p-2">
						<list-filter ref="listfilter" @change="updateFilter" :filterActive="filter.length"/>
					</div>
				</div>
			</template>
			</core-filter-cmpt>
		</div>
		<list-new ref="new" :studiengang-kz="studiengangKz" :studiensemester-kurzbz="studiensemesterKurzbz"></list-new>
	</div>`
};
