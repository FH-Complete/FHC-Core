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
				persistenceID: 'core-mitarbeiter_20250901-2',
				footerElement: '<div>&sum; <span id="search_count"></span> / <span id="total_count"></span></div>',
				selectableRangeMode: 'click',
				selectable: true,
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
						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('uid').component.updateDefinition({
							visible: true
						});

						cm.getColumnByField('person_id').component.updateDefinition({
							title: this.$p.t('person', 'person_id')
						});

						cm.getColumnByField('nachname').component.updateDefinition({
							title: this.$p.t('person', 'nachname'),
							visible: true
						});
						cm.getColumnByField('vorname').component.updateDefinition({
							title: this.$p.t('person', 'vorname'),
							visible: true
						});
						cm.getColumnByField('gebdatum').component.updateDefinition({
							title: this.$p.t('person', 'geburtsdatum')
						});
						cm.getColumnByField('unternehmen').component.updateDefinition({
							title: this.$p.t('person', 'firma')
						});
						cm.getColumnByField('vertragsarten').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragsarten'),
							visible: true
						});
						cm.getColumnByField('ids').component.updateDefinition({
							title: this.$p.t('vertrag', 'idsDienstverhaeltnisse'),
							visible: true
						});
						cm.getColumnByField('aktiv').component.updateDefinition({
							title: this.$p.t('global', 'aktiv'),
							width: 45
						});
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
