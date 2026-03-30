import {CoreFilterCmpt} from "../filter/Filter.js";
import FormInput from '../Form/Input.js';

export default {
	name: 'CoreListMitarbeiter',
	components: {
		CoreFilterCmpt,
		FormInput
	},
	emits: [
		"selectionChanged"
	],
	props: {
		endpoint: {
			type: Object,
			required: true
		},
		domain: {
			type: String,
			required: true
		}
	},
	data() {
		return {
			tabulatorData: [],
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					this.endpoint.getMitarbeiter()
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "UID", field: "uid", headerFilter: "input"},
					{title: "personID", field: "person_id", visible: false, headerFilter: "input"},
					{title: "Nachname", field: "nachname", visible: true, headerFilter: "input"},
					{title: "Vorname", field: "vorname", visible: true, headerFilter: "input"},
					{
						title: "Geburtsdatum",
						field: "gebdatum",
						headerFilter: "input",
						visible: true,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
							});
						}
					},
					{
						title: "Aktiv", field: "aktiv", visible: false, headerFilter: true, width: 85,
						formatter:"tickCross",
						hozAlign:"center",
					    formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}
					},
					{title: "Unternehmen", field: "unternehmen", visible: false, headerFilter: "input"},
					{title: "Vertragsarten", field: "vertragsarten", visible: true, headerFilter: "input"},
					{title: "Ids Dienstverträge", field: "ids", visible: true, headerFilter: "input"},
				],
				layout: 'fitColumns',
				persistenceID: 'core-mitarbeiter-2026021701',
				footerElement: '<div>&sum; <span id="search_count"></span> / <span id="total_count"></span></div>',
				selectableRowsRangeMode: 'click',
				selectableRows: 1,
			},
			tabulatorEvents: [
				{
					event: 'rowSelectionChanged',
					handler: this.rowSelectionChanged
				},
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['person', 'global', 'vertrag']);

						const setHeader = (field, text) => {
							const col = this.$refs.table.tabulator.getColumn(field);
							if (!col) return;

							const el = col.getElement();
							if (!el || !el.querySelector) return;

							const titleEl = el.querySelector('.tabulator-col-title');
							if (titleEl) {
								titleEl.textContent = text;
							}
						};

						setHeader('person_id', this.$p.t('person', 'person_id'));
						setHeader('nachname', this.$p.t('person', 'nachname'));
						setHeader('vorname', this.$p.t('person', 'vorname'));
						setHeader('gebdatum', this.$p.t('person', 'geburtsdatum'));
						setHeader('unternehmen', this.$p.t('person', 'firma'));
						setHeader('vertragsarten', this.$p.t('vertrag', 'vertragsarten'));
						setHeader('ids', this.$p.t('vertrag', 'idsDienstverhaeltnisse'));
						setHeader('aktiv', this.$p.t('global', 'aktiv'));
					}
				},
				{
					event: "dataFiltered",
					handler: function(filters, rows) {
						let el = document.getElementById("search_count");
						el.innerHTML = rows.length;
					}
				},
				{
					event: 'dataLoaded',
					handler: (data) => {
						let el = document.getElementById("total_count");
						el.innerHTML = data.length;
					}
				},
			],
			selectedPerson: null,
			selectedUid: null,
		}
	},
	methods: {
		rowSelectionChanged(data) {
				if(typeof data[0] != 'undefined')
				{
					this.selectedPerson = data[0].person_id;
					this.selectedUid = data[0].uid;

					this.$emit('selectionChanged', {
						person_id: this.selectedPerson,
						uid: this.selectedUid
					});
				}
		},
	},
	template: `
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			filter-type="Vertragsverwaltung"
			:side-menu="false"
			>
		</core-filter-cmpt>
`
}
