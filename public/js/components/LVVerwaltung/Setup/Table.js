import {CoreFilterCmpt} from "../../filter/Filter.js";
import BsModal from "../../Bootstrap/Modal.js";
import DetailsForm from "../Details/Form.js";
import CoreTag from '../../Tag/Tag.js';
import { tagHeaderFilter } from '../../../../js/tabulator/filters/extendedHeaderFilter.js';
import { extendedHeaderFilter } from "../../../../js/tabulator/filters/extendedHeaderFilter.js";
import { tagFormatter } from "../../../../js/tabulator/formatter/tags.js";
import { addTagInTable, deleteTagInTable, updateTagInTable } from "../../../../js/helpers/TagHelper.js";

import ApiLv from "../../../api/lehrveranstaltung.js";
import ApiTag from "../../../api/lehrveranstaltung/tag.js";
import ApiLehreinheit from "../../../api/lehrveranstaltung/lehreinheit.js";

export default {
	name: "LVVerwaltungTable",
	components: {
		CoreFilterCmpt,
		BsModal,
		DetailsForm,
		CoreTag

	},
	props: {
		selected: Object,
		filter: {
			type: Object,
			default: () => ({})
		}
	},
	inject: {
		currentSemester: {
			from: 'currentSemester'
		},
		lehreinheitAnmerkungDefault: {
			from: 'lehreinheitAnmerkungDefault',
			default: ''
		},
		lehreinheitRaumtypDefault: {
			from: 'lehreinheitRaumtypDefault',
			default: ''
		},
		lehreinheitRaumtypAlternativeDefault: {
			from: 'lehreinheitRaumtypAlternativeDefault',
			default: ''
		}
	},
	emits: [
		'update:selected',
		'row-clicked'
	],
	watch: {
		filter: {
			handler() {
				if (this.$refs.table && this.$refs.table.tabulator)
				{
					this.expanded = [];
					this.reload();
				}
			},
			deep: true,
		},
		currentSemester: {
			handler(newVal)
			{
				this.lv_info_default.studiensemester_kurzbz = newVal
				this.lv_info = false;
			}
		}
	},
	data() {
		return {
			expanded: [],
			selectedColumnValues: [],
			tagEndpoint: ApiTag,
			tabulatorEvents: [
				{
					event: 'rowSelectionChanged',
					handler: this.rowSelectionChanged
				},

				{
					event: 'dataProcessed',
					handler: (data) => {
						this.reexpandRows()
						this.$emit('update:selected', {})
					}
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
			formData: {},
			lv_info: false,
			lv_info_default: {
				stundenblockung: 2,
				wochenrythmus: 1,
				studiensemester_kurzbz: this.currentSemester,
				lehrform_kurzbz: 'UE',
				anmerkung: this.lehreinheitAnmerkungDefault.replace("'","\'"),
				raumtyp: this.lehreinheitRaumtypDefault,
				raumtypalternativ: this.lehreinheitRaumtypAlternativeDefault,
				lehrfach_id: ''

			}
		}
	},
	computed: {
		tabulatorOptions() {
			return {
				index: 'uniqueindex',
				ajaxURL: 'dummy',
				ajaxRequestFunc: async (url, config, params) => {
					let realUrl = this.buildApiUrl();
					if (realUrl)
						return this.$api.call(ApiLv.getTable(this.buildApiUrl()));
				},
				ajaxResponse: (url, params, response) => { return response?.data || [] },
				dataTree: true,
				initialSort:[
					{column: 'lv_bezeichnung', dir: 'desc'},
				],
				dataTreeChildIndent: 20,
				dataTreeElementColumn: "lv_kurzbz",
				dataTreeStartExpanded: false,
				dataTreeCollapseElement: '<i class="fa-solid fa-caret-down"></i>',
				dataTreeExpandElement: '<i class="fa-solid fa-caret-right"></i>',
				columnDefaults: {
					tooltip: true,
					headerFilter: "input",
					headerFilterFunc: extendedHeaderFilter,
				},
				layout: 'fitDataStretch',
				persistenceID: 'lehrveranstaltungen_2025_07_31_v1',
				selectableRowsRangeMode: 'click',
				selectableRows: true,
				rowContextMenu: (component, e) => {

					if (e.getData()?.lehreinheit_id === undefined)
						return;
					return [
						{
							label: "LV-Teil kopieren",
							menu: [
								{
									label: "Alles",
									action: (e, row) =>
									{
										this.copyLehreinheit(row, "alle");
									},
								},
								{
									label: "Nur LV-Teil",
									action: (e, row) =>
									{
										this.copyLehreinheit(row, "lvteil");
									},
								},
								{
									label: "Nur mit Gruppen",
									action: (e, row) =>
									{
										this.copyLehreinheit(row, "gruppen");
									},
								},
								{
									label: "Nur mit Lehrenden",
									action: (e, row) =>
									{
										this.copyLehreinheit(row, "lektoren");
									},
								},
							],
						},
						{
							label: "Entfernen",
							action: (e, row)  => {
								this.deleteLehreinheit(row)
							},
						},
					];
				},

				columns: [
					{
						title: this.$p.t('lehre', 'kurzbz'),
						field: "lv_kurzbz",
						headerFilterFuncParams: {field: 'lv_kurzbz'},
						headerFilter: true,
						formatter: (cell, formatterParams) => {
							const rowData = cell.getRow().getData();
							const iconKey = (rowData.lehrtyp_kurzbz || '').toLowerCase();
							const lvkurzbz = (cell.getValue()).toUpperCase();

							const parentspan = document.createElement('span');
							const span = document.createElement('span');


							span.classList.add('lv_table_icon', `icon-${iconKey}`);
							span.title = iconKey || 'LV-Teil';

							parentspan.appendChild(span);
							parentspan.appendChild(document.createTextNode(` ${lvkurzbz}`));

							return parentspan
						},

						cellClick: (e, cell) => {
							cell.getRow().treeToggle();
						}
					},
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
					{
						title: this.$p.t('lehre', 'lehrveranstaltung_id'),
						field: "lehrveranstaltung_id",
						headerFilterFuncParams: {field: 'lehrveranstaltung_id'},
						headerFilter: true,
						visible: false
					},
					{title: this.$p.t('ui', 'bezeichnung'), field: "lv_bezeichnung", headerFilter: true, headerFilterFuncParams: {field: 'lv_bezeichnung'}},
					{title: this.$p.t('lehre', 'bezeichnungeng'), field: "lv_bezeichnung_english", headerFilter: true, headerFilterFuncParams: {field: 'lv_bezeichnung_english'}, visible: false},
					{
						title: this.$p.t('lehre', 'studiengangskennzahlLehre'),
						field: "lv_studiengang_kz",
						headerFilter: true,
						headerFilterFuncParams: {field: 'lv_studiengang_kz'},
						visible: false
					},
					{title: this.$p.t('lehre', 'studiengang'), field: "studiengang", headerFilter: true, headerFilterFuncParams: {field: 'studiengang'}, visible: false},
					{title: this.$p.t('lehre', 'semester'), field: "semester", headerFilter: true, headerFilterFuncParams: {field: 'semester'}},
					{title: this.$p.t('global', 'sprache'), field: "sprache", headerFilter: true, headerFilterFuncParams: {field: 'sprache'}, visible: false},
					{title: this.$p.t('lehre', 'ects'), field: "lv_ects", headerFilter: true, headerFilterFuncParams: {field: 'lv_ects'}, visible: false},
					{title: this.$p.t('lehre', 'semesterstunden'), field: "semesterstunden", headerFilter: true, headerFilterFuncParams: {field: 'semesterstunden'}, visible: false},
					{title: this.$p.t('global', 'anmerkung'), field: "anmerkung", headerFilter: true, headerFilterFuncParams: {field: 'anmerkung'}, visible: false},
					{title: this.$p.t('lehre', 'lehre'), field: "lehre", headerFilter: true, headerFilterFuncParams: {field: 'lehre'}, formatter: (cell) => cell.getValue() ? this.toUpperCase(this.$p.t('ui', 'ja')) : this.toUpperCase(this.$p.t('ui', 'nein')), visible: false},
					{title: this.$p.t('person', 'aktiv'), field: "aktiv", headerFilter: true, headerFilterFuncParams: {field: 'aktiv'}, formatter: (cell) => cell.getValue() ? this.toUpperCase(this.$p.t('ui', 'ja')) : this.toUpperCase(this.$p.t('ui', 'nein')), visible: false},
					{title: this.$p.t('lehre', 'organisationsform'), field: "orgform_kurzbz", headerFilter: true, headerFilterFuncParams: {field: 'orgform_kurzbz'}},
					{title: this.$p.t('ui', 'studienplan_id'), field: "studienplan_id", headerFilter: true, headerFilterFuncParams: {field: 'studienplan_id'}, visible: false},
					{title: this.$p.t('lehre', 'studienplan'), field: "studienplan_bezeichnung", headerFilter: true, headerFilterFuncParams: {field: 'studienplan_bezeichnung'}, visible: false},
					{title: this.$p.t('lehre', 'lehrtyp'), field: "lehrtyp_kurzbz", headerFilter: true, headerFilterFuncParams: {field: 'lehrtyp_kurzbz'}, visible: false},
					{title: this.$p.t('lehre', 'lehrform'), field: "lehrform_kurzbz", headerFilter: true, headerFilterFuncParams: {field: 'lehrform_kurzbz'}},
					{title: this.$p.t('lehre', 'leplanstunden'), field: "le_planstunden", headerFilter: true, headerFilterFuncParams: {field: 'le_planstunden'}, visible: false},
					{title: this.$p.t('lehre', 'lehreinheit_id'), field: "lehreinheit_id", headerFilter: true, headerFilterFuncParams: {field: 'lehreinheit_id'}, visible: false},
					{title: this.$p.t('lehre', 'studiensemester'), field: "studiensemester_kurzbz", headerFilter: true, headerFilterFuncParams: {field: 'studiensemester_kurzbz'}, visible: false},
					{title: this.$p.t('lehre', 'unr'), field: "unr", headerFilter: true, headerFilterFuncParams: {field: 'unr'}, visible: false},
					{title: this.$p.t('lehre', 'organisationseinheit'), field: "fachbereich", headerFilter: true, headerFilterFuncParams: {field: 'fachbereich'}, visible: false},
					{title: this.$p.t('lehre', 'stundenblockung'), field: "stundenblockung", headerFilter: true, headerFilterFuncParams: {field: 'stundenblockung'}, visible: false},
					{title: this.$p.t('lehre', 'wochenrhythmus'), field: "wochenrythmus", headerFilter: true, headerFilterFuncParams: {field: 'wochenrythmus'}, visible: false},
					{title: this.$p.t('lehre', 'startkw'), field: "start_kw", headerFilter: true, headerFilterFuncParams: {field: 'startkw'}, visible: false},
					{title: this.$p.t('lehre', 'raumtyp'), field: "raumtyp", headerFilter: true, headerFilterFuncParams: {field: 'raumtyp'}, visible: false},
					{title: this.$p.t('lehre', 'raumtypalternativ'), field: "raumtypalternativ", headerFilter: true, headerFilterFuncParams: {field: 'raumtypalternativ'}, visible: false},
					{title: this.$p.t('lehre', 'gruppen'), field: "gruppen", headerFilter: true, headerFilterFuncParams: {field: 'gruppen'}},
					{title: this.$p.t('lehre', 'lehrende'), field: "lektoren", headerFilter: true, headerFilterFuncParams: {field: ['lektoren', 'vorname', 'nachname']}},
				],
			}

		}
	},

	mounted() {
		if (this.shouldAutoLoad())
		{
			this.reload();
		}
	},
	methods: {
		shouldAutoLoad() {
			return this.filter && this.filter.activeFilter;
		},
		toUpperCase(str) {
			if (!str)
				return '';
			return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase()
		},
		async reload()
		{

			if (this.shouldAutoLoad)
			{
				this.$refs.table.reloadTable();
			}
		},
		rowSelectionChanged(data) {
			this.selectedRows = this.$refs.table.tabulator.getSelectedRows();
			this.selectedColumnValues = this.selectedRows.filter(row => row.getData().lehreinheit_id !== undefined && row.getData().lehreinheit_id).map(row => row.getData().lehreinheit_id);

			if (data[0]?.lehreinheit_id !== undefined && this.selectedColumnValues.length === 1)
			{
				this.$emit('update:selected', [data[0]]);
				this.lv_info = false
			}
			else if (data[0]?.lehrveranstaltung_id)
			{
				this.$emit('update:selected', {});
				this.getLVInfos(data[0]);
			}
		},
		getLVInfos(data)
		{
			this.$api.call(ApiLv.getByLV(data.lehrveranstaltung_id))
				.then(result => {

					if (result.data?.lehrfach_id === undefined
						&& Array.isArray(result.data?.lehrfaecher))
					{
						const match = result.data.lehrfaecher?.find(
							lf => lf.lehrfach?.startsWith(result.data.lvbezeichnung)
						);
						if (match)
						{
							result.data.lehrfach_id = match.lehrveranstaltung_id;
						}
					}
					this.lv_info = {...this.lv_info_default, ...result.data};
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		buildApiUrl()
		{
			if (this.filter.activeFilter === 'employee' && this.filter.emp)
			{
				const { emp, stg, studiensemester_kurzbz } = this.filter;
				return this.$api.getUri(ApiLv.getByEmp(studiensemester_kurzbz, emp, stg));
			}

			if (this.filter.activeFilter === 'verband' && this.filter.stg)
			{
				const { stg, semester, studiensemester_kurzbz } = this.filter;
				return this.$api.getUri(ApiLv.getByStg(studiensemester_kurzbz, stg, semester));
			}
		},
		resetEmployeeFilter()
		{
			const newFilter = { ...this.filter };
			delete newFilter.emp;
			newFilter.activeFilter = 'verband';
		},
		showLehreinheitModal() {
			this.resetModal();
			this.$refs.lehreinheitModal.show();
		},
		addNewLehreinheit()
		{
			return this.$api.call(ApiLehreinheit.add(this.lv_info))
				.then(result => {
					this.$refs.lehreinheitModal.hide()
					this.reload()
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		resetModal()
		{
			this.lv_info_default = {
				stundenblockung: 2,
				wochenrythmus: 1,
				studiensemester_kurzbz: this.currentSemester,
				lehrform_kurzbz: 'UE',
				anmerkung: this.lehreinheitAnmerkungDefault.replace("'","\'"),
				raumtyp: this.lehreinheitRaumtypDefault,
				raumtypalternativ: this.lehreinheitRaumtypAlternativeDefault,
				lehrfach_id: ''
			}
		},
		addedTag(addedTag)
		{
			addTagInTable(addedTag, this.allRows, 'lehreinheit_id')
		},
		deletedTag(deletedTag)
		{
			deleteTagInTable(deletedTag, this.allRows);
		},

		updatedTag(updatedTag)
		{
			updateTagInTable(updatedTag, this.allRows);
		},
		async copyLehreinheit(row, art)
		{
			let data = {
				lehreinheit_id: row.getData().lehreinheit_id,
				art: art
			}

			return this.$api.call(ApiLehreinheit.copy(data))
				.then(result => {
					this.reload()
				})
				.catch(this.$fhcAlert.handleSystemError)
		},

		async getExpandedRows() {
			this.expanded = [];

			this.allRows.forEach(row => {
				if (row.getTreeChildren().length > 0 && row.isTreeExpanded())
				{
					this.expanded.push(row.getData().lv_bezeichnung);
				}
			});
		},
		reexpandRows() {
			this.allRows = this.getAllRows(this.$refs.table.tabulator.getRows());

			let lastMatchingRow = null;

			this.allRows.forEach(row => {
				if (this.expanded.includes(row.getData().lv_bezeichnung))
				{
					if (row._row.modules.dataTree)
					{
						row._row.modules.dataTree.open = true;
					}

					if (row._row.data._children?.length > 0)
					{
						lastMatchingRow = row;
					}
				}
			});

			if (lastMatchingRow)
			{
				lastMatchingRow.treeExpand();
			}

			this.$refs.table.tabulator.redraw();
		},
		deleteLehreinheit(row)
		{
			let lehreinheit_id = row.getData().lehreinheit_id;

			let is_selected = this.selectedColumnValues.length > 0 && this.selectedColumnValues.includes(lehreinheit_id);

			let deleteData = is_selected ? {lehreinheit_id: [...new Set(this.selectedColumnValues)]} : {lehreinheit_id: lehreinheit_id};

			return this.$api.call(ApiLehreinheit.delete(deleteData))
				.then(result => {

					if (result?.data?.errors)
					{
						result.data.errors.forEach(error  => {
							this.$fhcAlert.alertError(error)
						})
					}
					this.reload()
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		getAllRows(rows)
		{
			let result = [];
			rows.forEach(row =>
			{
				result.push(row);
				let children = row.getTreeChildren();
				if(children && children.length > 0)
				{
					result = result.concat(this.getAllRows(children));
				}
			});
			return result;
		},
		resetTree() {
			this.allRows.forEach(row => {
				row._row.modules.dataTree.open = false;
			});

			let rootRows = this.$refs.table.tabulator.getRows(true);
			var lastRow = rootRows[rootRows.length - 1];
			lastRow?.treeCollapse(true)

			this.currentTreeLevel = 0;
		},
		expandTree()
		{
			this.currentTreeLevel = (this.currentTreeLevel || 1);

			let lastMatchingRow = null;

			this.allRows.forEach(row => {
				const level = row._row.modules.dataTree?.index ?? 0;

				if (level === this.currentTreeLevel - 1 )
				{
					row._row.modules.dataTree.open = true;

					if (row._row.data._children?.length > 0)
					{
						lastMatchingRow = row;
					}
				}
			});

			if (lastMatchingRow)
			{
				lastMatchingRow.treeExpand();
				this.currentTreeLevel++;
			}
			this.$refs.table.tabulator.redraw();
		},
	},
	template: `
	<core-filter-cmpt
		ref="table"
		:tabulator-options="tabulatorOptions"
		:tabulator-events="tabulatorEvents"
		table-only
		:side-menu="false"
		:reload=true
		new-btn-label="LV-Teil hinzufügen"
		new-btn-show
		:new-btn-disabled="!lv_info"
		@click:new="showLehreinheitModal">
		
		<template #actions>
			<button @click="expandTree" class="btn btn-outline-secondary" type="button" :title="$p.t('lehre', 'aufklappen')"><i class="fa-solid fa-maximize"></i></button>
			<button @click="resetTree" class="btn btn-outline-secondary" type="button" :title="$p.t('lehre', 'zuklappen')"><i id="togglegroup" class="fa-solid fa-minimize"></i></button>
			<core-tag ref="tagComponent"
				:endpoint="tagEndpoint"
				:values="selectedColumnValues"
				@added="addedTag"
				@deleted="deletedTag"
				@updated="updatedTag"
				zuordnung_typ="lehreinheit_id"
			></core-tag>
		</template>
		<template #search>
			<slot name="filterzuruecksetzen"></slot>
		</template>
	</core-filter-cmpt>
	<bs-modal ref="lehreinheitModal" dialogClass="modal-xxl">
		<template #title>
			<p class="fw-bold mt-3">{{$p.t('lehre', 'newlehreinheit')}}</p>
		</template>
			
		<template v-if="lv_info">
			<details-form :data="lv_info"/>
		</template>
			
		<template #footer>
			<button type="button" class="btn btn-primary" @click="addNewLehreinheit">{{$p.t('ui', 'speichern')}}</button>
		</template>
	</bs-modal>

`
};