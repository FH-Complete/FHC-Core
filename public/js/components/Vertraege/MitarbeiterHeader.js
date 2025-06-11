import {CoreFilterCmpt} from "../filter/Filter.js";
import FormInput from '../Form/Input.js';

export default {
	name: 'CoreListMitarbeiter',
	components: {
		CoreFilterCmpt,
		FormInput
	},
	emits: [
		"selectedPerson"
	],
	props: {
		endpoint: {
			type: Object,
			required: true
		},
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					this.endpoint.getMitarbeiter()
				),
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
					{
						title: "Geburtsdatum",
						field: "gebdatum",
						headerFilter:"input",
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
						cm.getColumnByField('gebdatum').component.updateDefinition({
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
				this.$refs.table.tabulator.clearFilter();
			}
		},
	},
	template: `
	<div class="core-mitarbeiter-header">
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

