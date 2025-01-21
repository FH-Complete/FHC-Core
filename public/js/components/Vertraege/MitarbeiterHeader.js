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
					event: 'tableBuilt',
					handler: async() => {

						await this.$p.loadCategory(['person', 'global', 'vertrag']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('person_id').component.updateDefinition({
							title: this.$p.t('person', 'person_id')
						});

						cm.getColumnByField('nachname').component.updateDefinition({
							title: this.$p.t('person', 'nachname')
						});
						cm.getColumnByField('vorname').component.updateDefinition({
							title: this.$p.t('person', 'vorname')
						});
						cm.getColumnByField('aktiv').component.updateDefinition({
							title: this.$p.t('global', 'aktiv')
						});
						cm.getColumnByField('format_gebdatum').component.updateDefinition({
							title: this.$p.t('person', 'geburtsdatum')
						});
						cm.getColumnByField('unternehmen').component.updateDefinition({
							title: this.$p.t('person', 'firma')
						});
						cm.getColumnByField('vertragsarten').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragsarten')
						});
						cm.getColumnByField('ids').component.updateDefinition({
							title: this.$p.t('vertrag', 'idsDienstverhaeltnisse')
						});
					}
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
		<h4>{{$p.t('vertrag', 'vertragsverwaltung')}}</h4>
		
<!--	filter: show only active employees-->
		<div class="justify-content-end py-3">
			<form-input
				container-class="form-switch"
				type="checkbox"
				:label="$p.t('vertrag/nurAktiveMaAnzeigen')"
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

