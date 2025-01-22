import {CoreFilterCmpt} from "../../filter/Filter.js";

import BsModal from "../../Bootstrap/Modal.js";
import CoreForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';

export default {
	components: {
		CoreFilterCmpt,
		BsModal,
		CoreForm,
		FormInput
	},
	inject: {
		hasSchreibrechte: {
			from: 'hasSchreibrechte',
			default: false
		},
	},
	props: {
		person_id: {
			type: [Number],
			required: true
		},
		vertrag_id: {
			type: [Number],
			required: true
		},
	},
	data() {
		return {
			//tableData: [],
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.$fhcApi.factory.vertraege.person.getAllContractsAssigned,
				ajaxParams: () => {
					return {
						person_id: this.person_id,
						vertrag_id: this.vertrag_id
					};
				},
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Typ", field: "type"},
					{title: "Betrag", field: "betrag",
						formatter: function(cell) {
							let value = cell.getValue();
							if (value == null) {
								return "0.00";
							}
							return parseFloat(value).toFixed(2);
						}
					},
					{title: "Bezeichnung", field: "bezeichnung"},
					{title: "Studiensemester", field: "studiensemester_kurzbz"},
					{title: "Pruefung_id", field: "pruefung_id", visible: false},
					{title: "mitarbeiter_uid", field: "mitarbeiter_uid", visible: false},
					{title: "projektarbeit_id", field: "projektarbeit_id", visible: false},
					{title: "lehreinheit_id", field: "lehreinheit_id", visible: true},
					{title: "betreuerart_kurzbz", field: "betreuerart_kurzbz", visible: false},
					{title: "vertrag_id", field: "vertrag_id", visible: false}, //just for testing
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 50,
						formatter: (cell, formatterParams, onRendered) => {

							const container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('vertrag', 'deleteLehrauftrag');

							let type = cell.getData().type;
							if (type == 'Lehrauftrag')
							{
								if (!this.hasSchreibrechte) {
									button.disabled = true;
									button.classList.add('disabled');
								} else {
									button.addEventListener(
										'click',
										() =>
											this.actionDeleteLehrauftrag(cell.getData().vertrag_id, cell.getData().lehreinheit_id, cell.getData().mitarbeiter_uid)
									);
								}
							}

							if (type == 'Betreuung')
							{
								if (!this.hasSchreibrechte) {
									button.disabled = true;
									button.classList.add('disabled');
								} else {
									button.addEventListener(
										'click',
										() =>
											this.actionDeleteBetreuung(cell.getData().vertrag_id, cell.getData().projektarbeit_id, cell.getData().betreuerart_kurzbz)
									);
								}
							}

							container.append(button);

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitColumns',
				layoutColumnsOnNewData: false,
				height: '200',
				selectableRangeMode: 'click',
				selectable: true,
				persistenceID: 'core-contracts-details'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async() => {

						await this.$p.loadCategory(['ui', 'global', 'vertrag', 'projektarbeitsbeurteilung', 'lehre']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('type').component.updateDefinition({
							title: this.$p.t('global', 'typ')
						});
						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('ui', 'bezeichnung')
						});
						cm.getColumnByField('lehreinheit_id').component.updateDefinition({
							title: this.$p.t('ui', 'lehreinheit_id')
						});
						cm.getColumnByField('betrag').component.updateDefinition({
							title: this.$p.t('ui', 'betrag')
						});
						cm.getColumnByField('studiensemester_kurzbz').component.updateDefinition({
							title: this.$p.t('lehre', 'studiensemester')
						});
						cm.getColumnByField('mitarbeiter_uid').component.updateDefinition({
							title: this.$p.t('ui', 'mitarbeiter_uid')
						});
						cm.getColumnByField('projektarbeit_id').component.updateDefinition({
							title: this.$p.t('ui', 'projektarbeit_id')
						});
						cm.getColumnByField('betreuerart_kurzbz').component.updateDefinition({
							title: this.$p.t('projektarbeitsbeurteilung', 'betreuerart')
						});
						cm.getColumnByField('pruefung_id').component.updateDefinition({
							title: this.$p.t('ui', 'pruefung_id')
						});
						cm.getColumnByField('vertrag_id').component.updateDefinition({
							title: this.$p.t('ui', 'vertrag_id')
						});
						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
						});
					}
				}
			],
			clickedRows: [],
		}
	},
	watch: {
		person_id() {
			this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getAllContractsAssigned/' + this.person_id + '/' + this.vertrag_id);
		},
		vertrag_id() {
			this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getAllContractsAssigned/' + this.person_id + '/' + this.vertrag_id);
		},
	},
	methods: {
		actionDeleteLehrauftrag(vertrag_id, lehreinheit_id, mitarbeiter_uid) {
			this.$emit('deleteLehrauftrag', {
				lehreinheit_id: lehreinheit_id,
				vertrag_id: vertrag_id,
				mitarbeiter_uid: mitarbeiter_uid
			});
		},
		actionDeleteBetreuung(vertrag_id, projektarbeit_id, betreuerart_kurzbz) {
			this.$emit('deleteBetreuung', {
				person_id: this.person_id,
				vertrag_id: vertrag_id,
				projektarbeit_id: projektarbeit_id,
				betreuerart_kurzbz: betreuerart_kurzbz
			});
		},
		reload() {
			this.$refs.table.reloadTable();
			this.$emit('reload');
		},
	},
	template: `

	<div class="ore-contracts-details h-50 d-flex flex-column w-100 mt-2">
<!--	<div class="core-contracts-details vv">-->
	<br>		
		<h5>{{$p.t('vertrag', 'vertragDetails')}}</h5>
	
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			>
		</core-filter-cmpt>		
	</div>`
}