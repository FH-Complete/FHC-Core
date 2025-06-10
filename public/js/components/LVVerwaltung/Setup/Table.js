import {CoreFilterCmpt} from "../../filter/Filter.js";
import BsModal from "../../Bootstrap/Modal.js";
import DetailsForm from "../Details/Form.js";
import CoreTag from '../../Tag/Tag.js';
import { tagHeaderFilter } from '../../../../js/tabulator/filters/extendedHeaderFilter.js';
import { extendedHeaderFilter } from "../../../../js/tabulator/filters/extendedHeaderFilter.js";

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

		}
	},
	data() {
		return {
			fieldTitleMap: {
				lv_kurzbz: ['lehre', 'kurzbz'],
				tags: ['ui', 'tags'],
				lehrveranstaltung_id: ['lehre', 'lehrveranstaltung_id'],
				lv_bezeichnung: ['ui', 'bezeichnung'],
				lv_bezeichnung_english: ['lehre', 'bezeichnungeng'],
				lv_studiengang_kz: ['lehre', 'studiengangskennzahlLehre'],
				studiengang: ['lehre', 'studiengang'],
				semester: ['lehre', 'semester'],
				sprache: ['global', 'sprache'],
				lv_ects: ['lehre', 'ects'],
				semesterstunden: ['lehre', 'semesterstunden'],
				anmerkung: ['global', 'anmerkung'],
				lehre: ['lehre', 'lehre'],
				lehreverzeichnis: ['ui', 'lehreverzeichnis'],
				aktiv: ['person', 'aktiv'],
				planfaktor: ['ui', 'planfaktor'],
				planlektoren: ['ui', 'planlektoren'],
				planpersonalkosten: ['ui', 'planpersonalkosten'],
				plankostenprolektor: ['ui', 'plankostenprolektor'],
				orgform_kurzbz: ['lehre', 'organisationsform'],
				studienplan_id: ['ui', 'studienplan_id'],
				studienplan_bezeichnung: ['ui', 'studienplan_bezeichnung'],
				lehrtyp_kurzbz: ['ui', 'lehrtyp_kurzbz'],
				lehrform_kurzbz: ['lehre', 'lehrform'],
				le_planstunden: ['lehre', 'leplanstunden'],
				lehreinheit_id: ['lehre', 'lehreinheit_id'],
				stundenblockung: ['lehre', 'stundenblockung'],
				wochenrythmus: ['lehre', 'wochenrhytmus'],
				startkw: ['lehre', 'startkw'],
				raumtyp: ['lehre', 'raumtyp'],
				raumtypalternativ: ['lehre', 'raumtypalternativ'],
				gruppen: ['lehre', 'gruppen'],
				lektoren: ['lehre', 'lehrende'],
			},

			expanded: [],
			selectedColumnValues: [],
			tagEndpoint: ApiTag,
			iconPath: FHC_JS_DATA_STORAGE_OBJECT.app_root + '/skin/images/lehrtyp_',
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
				},{
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
				dataTreeElementColumn: "lv_kurzbz",
				dataTreeFilter: true,
				dataTreeStartExpanded: false,
				dataTreeCollapseElement: '<i class="fa-solid fa-caret-down"></i>',
				dataTreeExpandElement: '<i class="fa-solid fa-caret-right"></i>',
				columnDefaults: {
					tooltip: true,
					headerFilter: "input",
					headerFilterFunc: extendedHeaderFilter
				},
				layout: 'fitDataStretch',
				layoutColumnsOnNewData: false,
				height: '100%',
				selectableRowsRangeMode: 'click',
				selectableRows: true,
				rowContextMenu: (component, e) => {

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
										this.copyLehreinheit(row, "halb");
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

				persistenceID: 'lehrveranstaltungen_2025_05_27_v1',
				columns: [
					{
						title: this.$p.t('lehre', 'kurzbz'),
						field: "lv_kurzbz",
						headerFilterFuncParams: {field: 'lv_kurzbz'},
						headerFilter: true,
						formatter: (cell, formatterParams) => {
							const rowData = cell.getRow().getData();
							const iconKey = (rowData.lehrtyp_kurzbz || '').toLowerCase();

							const span = document.createElement('span');
							span.classList.add('lv_table_icon', `icon-${iconKey}`);
							span.title = iconKey || 'LV-Teil';
							return span;
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
						formatter: (cell) => {
							let tags = cell.getValue();
							if (!tags) return;

							let container = document.createElement('div');
							container.className = "d-flex gap-1";

							let parsedTags = JSON.parse(tags);
							let maxVisibleTags = 2;

							const rowData = cell.getRow().getData();
							if (rowData._tagExpanded === undefined) {
								rowData._tagExpanded = false;
							}

							const renderTags = () => {
								container.innerHTML = '';
								parsedTags = parsedTags.filter(item => item !== null);
								const tagsToShow = rowData._tagExpanded ? parsedTags : parsedTags.slice(0, maxVisibleTags);

								tagsToShow.forEach(tag => {
									if (!tag) return;
									let tagElement = document.createElement('span');
									tagElement.innerText = tag.beschreibung;
									tagElement.title = tag.notiz;
									tagElement.className = "tag " + tag.style;
									if (tag.done) tagElement.className += " tag_done";

									tagElement.addEventListener('click', (event) => {
										event.stopPropagation();
										event.preventDefault();
										this.$refs.tagComponent.editTag(tag.id);
									});

									container.appendChild(tagElement);
								});

								if (parsedTags.length > maxVisibleTags) {
									let toggle = document.createElement('button');
									toggle.innerText = (rowData._tagExpanded ? '- ' : '+ ') + (parsedTags.length - maxVisibleTags);
									toggle.className = "display_all";
									toggle.title = rowData._tagExpanded ? "Tags ausblenden" : "Tags einblenden";

									toggle.addEventListener('click', () => {
										rowData._tagExpanded = !rowData._tagExpanded;
										renderTags();
									});

									container.appendChild(toggle);
								}
							};

							renderTags();
							return container;
						},
						width: 150,
					},
					{
						title: this.$p.t('lehre', 'lehrveranstaltung_id'),
						field: "lehrveranstaltung_id",
						headerFilterFuncParams: {field: 'lehrveranstaltung_id'},
						headerFilter: true
					},
					{title: this.$p.t('ui', 'bezeichnung'), field: "lv_bezeichnung", headerFilter: true, headerFilterFuncParams: {field: 'lv_bezeichnung'}},
					{title: this.$p.t('lehre', 'bezeichnungeng'), field: "lv_bezeichnung_english", headerFilter: true, headerFilterFuncParams: {field: 'lv_bezeichnung_english'}},
					{
						title: this.$p.t('lehre', 'studiengangskennzahlLehre'),
						field: "lv_studiengang_kz",
						headerFilter: true,
						headerFilterFuncParams: {field: 'lv_studiengang_kz'}
					},
					{title: this.$p.t('lehre', 'studiengang'), field: "studiengang", headerFilter: true, headerFilterFuncParams: {field: 'studiengang'}},
					{title: this.$p.t('lehre', 'semester'), field: "semester", headerFilter: true, headerFilterFuncParams: {field: 'semester'}},
					{title: this.$p.t('global', 'sprache'), field: "sprache", headerFilter: true, headerFilterFuncParams: {field: 'sprache'}},
					{title: this.$p.t('lehre', 'ects'), field: "lv_ects", headerFilter: true, headerFilterFuncParams: {field: 'lv_ects'}},
					{title: this.$p.t('lehre', 'semesterstunden'), field: "semesterstunden", headerFilter: true, headerFilterFuncParams: {field: 'semesterstunden'}},
					{title: this.$p.t('global', 'anmerkung'), field: "anmerkung", headerFilter: true, headerFilterFuncParams: {field: 'anmerkung'}},
					{title: this.$p.t('lehre', 'lehre'), field: "lehre", headerFilter: true, headerFilterFuncParams: {field: 'lehre'}},
					{title: "Lehreverzeichnis", field: "lehreverzeichnis", headerFilter: true, headerFilterFuncParams: {field: 'lehreverzeichnis'}},
					{title: this.$p.t('person', 'aktiv'), field: "aktiv", headerFilter: true, headerFilterFuncParams: {field: 'aktiv'}},
					{title: "Planfaktor", field: "planfaktor", headerFilter: true, headerFilterFuncParams: {field: 'planfaktor'}},
					{title: "Planlektoren", field: "planlektoren", headerFilter: true, headerFilterFuncParams: {field: 'planlektoren'}},
					{title: "planpersonalkosten", field: "planpersonalkosten", headerFilter: true, headerFilterFuncParams: {field: 'planpersonalkosten'}},
					{title: "plankostenprolektor", field: "plankostenprolektor", headerFilter: true, headerFilterFuncParams: {field: 'plankostenprolektor'}},
					{title: this.$p.t('ui', 'organisationsform'), field: "orgform_kurzbz", headerFilter: true, headerFilterFuncParams: {field: 'orgform_kurzbz'}},
					{title: this.$p.t('ui', 'studienplan_id'), field: "studienplan_id", headerFilter: true, headerFilterFuncParams: {field: 'studienplan_id'}},
					{title: "studienplan_bezeichnung", field: "studienplan_bezeichnung", headerFilter: true, headerFilterFuncParams: {field: 'studienplan_bezeichnung'}},
					{title: "lehrtyp_kurzbz", field: "lehrtyp_kurzbz", headerFilter: true, headerFilterFuncParams: {field: 'lehrtyp_kurzbz'}},
					{title: this.$p.t('lehre', 'lehrform'), field: "lehrform_kurzbz", headerFilter: true, headerFilterFuncParams: {field: 'lehrform_kurzbz'}},
					{title: this.$p.t('lehre', 'leplanstunden'), field: "le_planstunden", headerFilter: true, headerFilterFuncParams: {field: 'le_planstunden'}},
					{title: this.$p.t('lehre', 'lehreinheit_id'), field: "lehreinheit_id", headerFilter: true, headerFilterFuncParams: {field: 'lehreinheit_id'}},
					{title: this.$p.t('lehre', 'stundenblockung'), field: "stundenblockung", headerFilter: true, headerFilterFuncParams: {field: 'stundenblockung'}},
					{title: this.$p.t('lehre', 'wochenrhytmus'), field: "wochenrythmus", headerFilter: true, headerFilterFuncParams: {field: 'wochenrythmus'}},
					{title: this.$p.t('lehre', 'startkw'), field: "startkw", headerFilter: true, headerFilterFuncParams: {field: 'startkw'}},
					{title: this.$p.t('lehre', 'raumtyp'), field: "raumtyp", headerFilter: true, headerFilterFuncParams: {field: 'raumtyp'}},
					{title: this.$p.t('lehre', 'raumtypalternativ'), field: "raumtypalternativ", headerFilter: true, headerFilterFuncParams: {field: 'raumtypalternativ'}},
					{title: this.$p.t('lehre', 'gruppen'), field: "gruppen", headerFilter: true, headerFilterFuncParams: {field: 'gruppen'}},
					{title: this.$p.t('lehre', 'lehrende'), field: "lektoren", headerFilter: true, headerFilterFuncParams: {field: 'lektoren'}},
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
		async reload()
		{

			if (this.shouldAutoLoad)
			{
				this.$refs.table.reloadTable();
			}
		},
		rowSelectionChanged(data) {
			this.selectedRows = this.$refs.table.tabulator.getSelectedRows();
			this.selectedColumnValues = this.selectedRows.filter(row => row.getData().lehreinheit_id !== undefined).map(row => row.getData().lehreinheit_id);

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
				return this.$api.getUri(ApiLv.getByEmpStg(
					this.filter.emp,
					this.filter.stg
				));
			}

			if (this.filter.activeFilter === 'verband' && this.filter.stg)
			{
				return this.$api.getUri(ApiLv.getByStg(
					this.filter.stg,
					this.filter.semester
				));
			}
		},
		resetEmployeeFilter()
		{
			const newFilter = { ...this.filter };
			delete newFilter.emp;
			newFilter.activeFilter = 'verband';
		},
		buildParams()
		{
			const params = {};
			for (const [key, value] of Object.entries(this.filter)) {
				if (value !== undefined && value !== null) {
					params[key] = value;
				}
			}
			return params;
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
			const table = this.$refs.table.tabulator;

			this.selectedRows.forEach(row =>
			{
				if (Array.isArray(addedTag.response))
				{
					addedTag.response.forEach(tag => {

						const all = this.getAllRows(table.getRows());
						const targetRow = all.find(row => row.getData().lehreinheit_id === tag.lehreinheit_id);
						if (targetRow)
						{
							const rowData = targetRow.getData();
							let tags = [];
							try {
								tags = JSON.parse(rowData.tags || '[]');
							} catch (e) {}

							const tagExists = tags.some((t) => t.id === tag.id);
							if (!tagExists)
							{
								addedTag.id = tag.id;
								tags.push({ ...addedTag });
								targetRow.update({ tags: JSON.stringify(tags) });
								targetRow.reformat();
							}
						}
					});
				}
			});
		},
		deletedTag(deletedTag) {
			const table = this.$refs.table.tabulator;
			const all = this.getAllRows(table.getRows());

			const targetRow = all.find(row => {
				const rowData = row.getData();

				let tags = [];
				try {
					tags = JSON.parse(rowData.tags || '[]');
				} catch (e) {}

				return tags.some(tag => tag.id === deletedTag);
			});

			if (targetRow) {
				const rowData = targetRow.getData();
				let tags = [];

				try {
					tags = JSON.parse(rowData.tags || '[]');
				} catch (e) {}

				const filteredTags = tags.filter(t => t.id !== deletedTag);
				const updatedTags = JSON.stringify(filteredTags);

				if (updatedTags !== rowData.tags) {
					targetRow.update({
						tags: updatedTags
					});

					targetRow.reformat();
				}
			}
		},

		updatedTag(updatedTag) {
			const table = this.$refs.table.tabulator;
			const all = this.getAllRows(table.getRows());

			const targetRow = all.find(row => {
				const rowData = row.getData();
				let tags = [];

				try {
					tags = JSON.parse(rowData.tags || '[]');
				} catch (e) {}

				return tags.some(t => t?.id === updatedTag.id);
			});

			if (targetRow)
			{
				const rowData = targetRow.getData();
				let tags = [];
				try {
					tags = JSON.parse(rowData.tags || '[]');
				} catch (e) {}

				let changed = false;

				const tagIndex = tags.findIndex(tag => tag?.id === updatedTag.id);
				if (tagIndex !== -1) {
					tags[tagIndex] = { ...updatedTag };
					changed = true;
				}

				if (changed)
				{
					targetRow.update({
						tags: JSON.stringify(tags),
					});
					targetRow.reformat();
				}
			}
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
			let rows = this.$refs.table.tabulator.getRows();
			let allRows = this.getAllRows(rows);
			allRows.forEach(row => {
				if (row.getTreeChildren().length > 0 && row.isTreeExpanded())
				{
					this.expanded.push(row.getData().uniqueindex);
				}
			});
		},
		reexpandRows() {
			const all = this.getAllRows(this.$refs.table.tabulator.getRows());

			const matchingRows = all.filter(row =>
				this.expanded.includes(row.getData().uniqueindex)
			);

			matchingRows.forEach((row, index) => {
				row._row.modules.dataTree.open = true;

				if (index === matchingRows.length - 1)
				{
					row.treeExpand();
				}
			});
		},
		deleteLehreinheit(row)
		{
			let deleteData = {
				lehreinheit_id: row.getData().lehreinheit_id,
			}
			return this.$api.call(ApiLehreinheit.delete(deleteData))
				.then(result => {
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
		<bs-modal ref="lehreinheitModal" dialogClass="modal-lg">
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