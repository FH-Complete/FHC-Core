import {CoreFilterCmpt} from "../filter/Filter.js";
import FormInput from '../Form/Input.js';

export default {
	components: {
		CoreFilterCmpt,
		FormInput
	},
	inject: {
		cisRoot: {
			from: 'cisRoot'
		},
	},
	emits: [
		"selectedPerson"
	],
	props: {
		// maybe later nur fixe oder alle Mitarbeiter: gleich funktionsaufruf
		//oder Mitarbeiter mit Verträgen
/*		filterMa: {
			type: Object,
			required: true,
			default: function () {
				return {
					active: true,
					hasVertraege: false
				};
			},
		},*/
/*		vertragsarten: {
			type: Array,
			required: false,
			default: function (){
				return {
					['echterdv']
				}
			}
		}*/
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.$fhcApi.factory.vertraege.person.getMitarbeiter,
				ajaxParams: () => {
					return {
						fix: this.filterMa
					};
				},
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "UID", field: "uid", headerFilter:"input"},
					{title: "personID", field: "person_id", visible: false, headerFilter:"input"},
					{title: "Nachname", field: "nachname", headerFilter:"input"},
					{title: "Vorname", field: "vorname", headerFilter:"input"},
					{
						title: "Aktiv", field: "aktiv", headerFilter: "input",
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}
					},
					{title: "Geburtsdatum", field: "format_gebdatum", headerFilter:"input"},
					{title: "Unternehmen", field: "unternehmen", headerFilter:"input"},
					{title: "Vertragsarten", field: "vertragsarten", headerFilter:"input"},
					{title: "Ids Dienstverträge", field: "ids", headerFilter:"input"},
				],
				layout: 'fitColumns',
				persistenceID: 'core-mitarbeiter',
				selectableRangeMode: 'click',
				selectable: true,
			},
			tabulatorEvents: [
				{
					event: 'rowSelectionChanged',
					handler: this.rowSelectionChanged
				},
		{
/*					event: 'tableBuilt',
					handler: async() => {

						await this.$p.loadCategory(['ui', 'global', 'vertrag']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('ui', 'bezeichnung')
						});
						cm.getColumnByField('lehreinheit_id').component.updateDefinition({
							title: this.$p.t('ui', 'lehreinheit_id')
						});
						cm.getColumnByField('betrag').component.updateDefinition({
							title: this.$p.t('ui', 'betrag')
						});
						cm.getColumnByField('status').component.updateDefinition({
							title: this.$p.t('global', 'status')
						});
						cm.getColumnByField('vertragstyp_bezeichnung').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragstyp')
						});
						cm.getColumnByField('format_vertragsdatum').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragsdatum')
						});
						cm.getColumnByField('vertragsdatum').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragsdatum_iso')
						});
						cm.getColumnByField('vertragsstunden').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragsstunden')
						});
						cm.getColumnByField('vertragsstunden_studiensemester_kurzbz').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragsstunden_studiensemester')
						});
						cm.getColumnByField('vertrag_id').component.updateDefinition({
							title: this.$p.t('ui', 'vertrag_id')
						});
						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});
						cm.getColumnByField('isabgerechnet').component.updateDefinition({
							title: this.$p.t('vertrag', 'abgerechnet')
						});
						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
						});
					} */
				}
			],
			selectedPerson: null,
			isFilterSet: false,
		}
	},
	methods: {
		rowSelectionChanged(data) {
			this.selectedPerson = data[0].person_id;
			this.$emit('selectedPerson', this.selectedPerson);
		},
		onSwitchChange() {
			if (this.isFilterSet) {
				this.$refs.table.tabulator.setFilter("aktiv", "=", true);
			}
			else {
				this.$refs.table.tabulator.clearFilter("aktiv");
			}
		},
	},
	template: `
	<div class="core-mitarbeiter h-100 d-flex flex-column">
		<h4>Vertragsverwaltung</h4>
		
<!--	filter: show only active employees-->
		<div class="justify-content-end py-3">
			<form-input
				container-class="form-switch"
				type="checkbox"
				label="nur aktive Mitarbeiter:innen anzeigen"
				v-model="isFilterSet"
				@change="onSwitchChange"
				>
			</form-input>
		</div>
		
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			>
		</core-filter-cmpt>

	</div>`
}

