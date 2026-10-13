import {CoreFilterCmpt} from "../../../../components/filter/Filter.js";
import BsModal from '../../../Bootstrap/Modal.js';
import FormInput from "../../../Form/Input.js";
import CoreTag from '../../../../components/Tag/Tag.js';

import {
	buildTagHeaderFilterExpression,
	buildTagOptionsFromRows,
	customTagFilter,
	setTagHeaderFilterValue,
	tagHeaderFilter,
	extendedHeaderFilter,
	syncTagHeaderFilterOptions,
	syncSelectedTagOptionsWithHeaderFilters
} from "../../../../tabulator/filters/extendedHeaderFilter.js";

import { tagFormatter } from "../../../../tabulator/formatter/tags.js";
import ApiTempusTag from "../../../../api/factory/tempus/tag.js";

export default {
	name: "TableView",
	inject: {
		events: "events",
		timezone: "timezone",
		tableActions: "tableActions",
	},
	components: {
		CoreFilterCmpt,
		BsModal,
		FormInput,
		CoreTag
	},
	props: {
		day: {
			type: luxon.DateTime,
			required: true
		},
		end: {
			type: luxon.DateTime,
			required: true
		}
	},
	data()
	{
		return {
			raumtyp_array: [],
			actionSelect: '',
			tagFilterState: {
				initialOptions: [],
				selectedOptions: [],
			},
			tagFilterLabels: {
				tag: "Tag",
				clear: "Clear",
				connectors: {
					AND: "AND",
					OR: "OR",
					NOT: "NOT",
				},
			},
			tagEndpoint: ApiTempusTag,
			selectedColumnValues: []
		}
	},
	computed: {
		start() {
			return this.day.startOf('day');
		},
		tableEnd() {
			return this.end.endOf('day');
		},
		preparedEvents() {
			const end = this.tableEnd;
			return this.events
				.filter(e => e.start < end && e.end > this.start)
				.sort((a, b) => a.start.ts - b.start.ts)
				.map(event => ({
					...event.orig,
					row_index: event.id,
				}));
		},
		tabulatorOptions() {
			return {
				index: "row_index",
				layout: 'fitDataStretch',
				placeholder: "Keine Daten verfügbar",
				persistenceID: "2026_09_29_table_view_v1",
				height: '100%',
				data: this.preparedEvents,
				columns: [
					{
						formatter: 'rowSelection',
						titleFormatter: 'rowSelection',
						titleFormatterParams: {
							rowRange: "active"
						},
						headerSort: false,
						width: 40
					},
					{
						title: 'Tags',
						field: 'tags',
						tooltip: false,
						headerFilter: customTagFilter,
						headerFilterFunc: tagHeaderFilter,
						formatter: (cell, formatterParams, onRendered) => tagFormatter(cell, this.$refs.tagComponent, onRendered),
						width: 150,
					},

					{title: 'Datum', field: 'datum', headerFilter: "input", formatter: (cell) => {
							let val = cell.getValue();
							if (!val)
								return '&nbsp;';
							return luxon.DateTime.fromISO(val).toFormat('dd.MM.yyyy')
						}
					},
					{title: 'Von', field: 'beginn', headerFilter: "input"},
					{title: 'Bis', field: 'ende', headerFilter: "input"},
					{title: 'Lehrfach', field: 'lehrfach', headerFilter: "input"},
					{title: 'Bezeichnung', field: 'lehrfach_bez', headerFilter: "input"},
					{title: 'Lehrform', field: 'lehrform', headerFilter: "input"},
					{title: 'Raum', field: 'ort_kurzbz', headerFilter: "input"},
					{
						title: 'Lehreinheit_id',
						field: 'lehreinheit_id',
						headerFilter: "input",
						tooltip: false,
						formatter: function(cell) {
							let value = cell.getValue();
							if (!value || !value.length)
								return '';

							let semester = cell.getRow().getData().le_studiensemester_kurzbz.toLowerCase();
							let base_url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

							return value.map(lehreinheit => {
								let url = `${base_url}/LVVerwaltung/stdsem/${semester}/le/${lehreinheit}`;
								return `<a href="${url}" target="_blank">${lehreinheit}</a>`;
							}).join(', ');
						},
					},
					{
						title: 'Lektor',
						field: 'lektor',
						headerFilter: "input",
						formatter: (cell) => {
							let value = cell.getValue();
							if (!value)
								return '';
							return value.map(l => l.kurzbz).join(', ') ?? '–'
						}
					},
					{title: 'OE', field: 'organisationseinheit', headerFilter: "input"},
					{title: 'Status', field: 'status_kurzbz', headerFilter: "input"},
				]
			}
		},
	},
	methods:
	{
		tagHeaderFilterParams() {
			return {
				listOnEmpty: true,
				autocomplete: true,
				initialOptions: this.tagFilterState.initialOptions,
				selectedOptions: this.tagFilterState.selectedOptions,
				labels: this.tagFilterLabels,
			};
		},
		tableBuilt() {
			this.$refs.tableViewTable.tabulator.updateColumnDefinition('tags', {
				headerFilterParams: this.tagHeaderFilterParams(),
			});;

		},
		openModal() {
			let selected = this.$refs.tableViewTable?.tabulator?.getSelectedData() ?? [];
			if (!selected.length)
				return;
			this.tableActions?.openRaumauswahl(selected)
		},
		async deleteSelected() {
			let selected = this.$refs.tableViewTable?.tabulator?.getSelectedData() ?? [];

			if (!selected.length) return;

			let isConfirmed = await this.$fhcAlert.confirmDelete();

			if (!isConfirmed) return;

			await this.tableActions?.deleteEntries(selected)

			this.$refs.tableViewTable.tabulator.deselectRow();
		},
		async setStatus() {

			let selected = this.$refs.tableViewTable?.tabulator?.getSelectedData() ?? [];

			if (!selected.length) return;

			if (this.actionSelect === 'delete')
			{
				let isConfirmed = await this.$fhcAlert.confirmDelete();
				if (!isConfirmed) return;
				await this.tableActions?.deleteEntries(selected)
			}
			else if (this.actionSelect === 'toLecturer')
			{
				await this.tableActions?.syncToLecturer(selected)
			}
			else if (this.actionSelect === 'toStudent')
			{
				await this.tableActions?.syncToStudent(selected)
			}
		},
		async fetchAssignedTagsByCalender(calendarGroupId) {
			const result = await this.$api.call(
				ApiTempusTag.getTagsByCalendar(calendarGroupId),
			);

			if (result.meta.status === "success") {
				return result.data.filter((tag) => !!tag);
			}

			this.$fhcAlert.alertError(
				this.$p.t("ui", "failed_assigned_tags_fetch_error_message"),
			);
			return [];
		},
		async addedTag(addedTag) {
			let relevantEvents = this.events.filter(event =>
				this.selectedColumnValues.includes(event.orig.eindeutige_kalender_gruppen_id)
			);

			await Promise.all(relevantEvents.map(async (event) => {
				let tags = await this.fetchAssignedTagsByCalender(event.orig.eindeutige_kalender_gruppen_id);
				event.orig.tags = tags
			}));

			syncTagHeaderFilterOptions(
				this.$refs.tableViewTable?.tabulator?.getData() || [],
				this.tagFilterState.initialOptions,
				this.tagFilterState.selectedOptions,
			);
		},

		async deletedTag(deletedTag) {
			let calendarIds = this.events
				.filter(event => event.orig.tags?.some(t => t.id === deletedTag))
				.map(event => event.orig.eindeutige_kalender_gruppen_id);

			if (!calendarIds.length) return;

			let relevantEvents = this.events.filter(event =>
				calendarIds.includes(event.orig.eindeutige_kalender_gruppen_id)
			);

			await Promise.all(relevantEvents.map(async (event) => {
				let tags = await this.fetchAssignedTagsByCalender(event.orig.eindeutige_kalender_gruppen_id);
				event.orig.tags = tags;
			}));

			syncTagHeaderFilterOptions(
				this.$refs.tableViewTable?.tabulator?.getData() || [],
				this.tagFilterState.initialOptions,
				this.tagFilterState.selectedOptions,
			);
		},
		async updatedTag(updatedTag) {
			let calendarIds = this.events
				.filter(event => event.orig.tags?.some(t => t.id === updatedTag.id))
				.map(event => event.orig.eindeutige_kalender_gruppen_id);

			if (!calendarIds.length) return;

			let relevantEvents = this.events.filter(event =>
				calendarIds.includes(event.orig.eindeutige_kalender_gruppen_id)
			);

			await Promise.all(relevantEvents.map(async (event) => {
				let tags = await this.fetchAssignedTagsByCalender(event.orig.eindeutige_kalender_gruppen_id);
				event.orig.tags = tags;
			}));

			syncTagHeaderFilterOptions(
				this.$refs.tableViewTable?.tabulator?.getData() || [],
				this.tagFilterState.initialOptions,
				this.tagFilterState.selectedOptions,
			);
		},
		dataLoadedHandler (data) {

			syncTagHeaderFilterOptions(
				Array.isArray(data) ? data : [],
				this.tagFilterState.initialOptions,
				this.tagFilterState.selectedOptions,
			);
		},
		dataFilteredHandler(filters, rows) {

			syncSelectedTagOptionsWithHeaderFilters(
				filters,
				this.tagFilterState.selectedOptions
			);
		},
		columnWidthHandler (column) {
			if (column.getField() !== "tags") return;

			column.getCells().forEach((cell) => {
				cell.getElement().firstElementChild?.fitTags?.();
			});
		},
		onRowClick(e, row)
		{
			if (e.target.closest('a, input'))
				return;

			let rowIndex = row.getData().row_index;
			let eventData = this.events.find(ev => ev.id === rowIndex)?.orig ?? row.getData();

			let event = new CustomEvent('cal-click', {
				cancelable: true,
				bubbles: true,
				detail: {source: 'event', value: eventData}
			})

			row.getElement().dispatchEvent(event)
		},

		updateSelectedRows() {
			this.selectedColumnValues = this.$refs.tableViewTable.tabulator.getSelectedRows().map(row => row.getData().eindeutige_kalender_gruppen_id);
		},
	},
	watch: {
		preparedEvents: {
			handler(newData) {
				this.$refs.tableViewTable?.tabulator?.setData(newData);
			},
			deep: true
		},
		"tagFilterState.selectedOptions": {
			handler() {
				const selectedOptions = this.tagFilterState.selectedOptions;
				const combinedFilterStatement = buildTagHeaderFilterExpression(selectedOptions);

				setTagHeaderFilterValue(
					combinedFilterStatement,
					this.$refs.tableViewTable.tabulator,
				);
			},
			deep: true,
		},
	},
	template: /* html */`
	<div class="fhc-calendar-mode-table-view position-absolute top-0 start-0 w-100 h-100 d-flex flex-column">
		<core-filter-cmpt
			ref="tableViewTable"
			:tabulator-options="tabulatorOptions"
			:table-only="true"
			:side-menu="false"
			:download="true"
			:tabulator-events="[{ event: 'tableBuilt', handler: tableBuilt },
								{ event: 'rowSelectionChanged', handler: updateSelectedRows },
								{ event: 'dataLoaded', handler: dataLoadedHandler }, 
								{ event: 'dataFiltered', handler: dataFilteredHandler }, 
								{ event: 'columnWidth', handler: columnWidthHandler },
								{ event: 'rowClick', handler: onRowClick }
			]"
		>
			<template #actions>
				<core-tag ref="tagComponent"
					:endpoint="tagEndpoint"
					:values="selectedColumnValues"
					@added="addedTag"
					@deleted="deletedTag"
					@updated="updatedTag"
					zuordnung_typ="eindeutige_kalender_gruppen_id"
					show-hover
				></core-tag>
				<div class="d-flex gap-2 align-items-baseline">
					<select v-model="actionSelect" class="form-select">
						<option selected disabled value="">Status setzen</option>
						<option value="toLecturer">Freischalten für Voransicht</option>
						<option value="toStudent">Freischalten für Live</option>
					</select>
					<button @click="setStatus" :disabled="!actionSelect" class="btn btn-outline-danger btn-sm text-nowrap">
						Status setzen
					</button>
				</div>
				
				<button @click="deleteSelected" class="btn btn-outline-danger btn-sm">Löschen</button>
				<button @click="openModal" class="btn btn-outline-secondary btn-sm">Raum wechsel</button>
			</template>
		</core-filter-cmpt>
	</div>
	`
}