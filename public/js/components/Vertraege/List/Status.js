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
		vertrag_id: {
			type: [Number],
			required: true
		},
		listContractStati: {
			type: Array,
			required: true
		},
		formDataParent: {
			type: Object,
			required: true
		},
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
					this.endpoint.getStatiOfContract(this.vertrag_id)
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Status", field: "bezeichnung"},
					{
						title: "Datum",
						field: "datum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							const date = new Date(dateStr); // Convert to Date object
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour: "2-digit",
								minute: "2-digit",
								hour12: false
							});
						}
					},
					{title: "vertrag_id", field: "vertrag_id", visible: false},
					{title: "Vertragsstatus", field: "vertragsstatus_kurzbz", visible: false},
					{title: "User", field: "mitarbeiter_uid", visible: false},
					{title: "insertvon", field: "insertvon", visible: false},
					{
						title: "insertamum",
						field: "insertamum",
						visible: false,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour: "2-digit",
								minute: "2-digit",
								hour12: false
							});
						}
					},
					{title: "updatevon", field: "updatevon", visible: false},
					{
						title: "updateamum",
						field: "updateamum",
						visible: false,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour: "2-digit",
								minute: "2-digit",
								hour12: false
							});
						}
					},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150,
						formatter: (cell, formatterParams, onRendered) => {

							const container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = this.$p.t('vertrag', 'editStatus');
							button.addEventListener(
								'click',
								(event) =>
									this.actionEditStatus(cell.getData().vertrag_id, cell.getData().vertragsstatus_kurzbz)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('vertrag', 'deleteStatus');
							if (!this.hasSchreibrechte) {
								button.disabled = true;
								button.classList.add('disabled');
							} else {
								button.addEventListener(
									'click',
									() =>
										this.actionDeleteStatus(cell.getData().vertrag_id, cell.getData().vertragsstatus_kurzbz)
								);
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
				persistenceID: 'core-contracts-status'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async() => {

						await this.$p.loadCategory(['ui', 'global', 'vertrag']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('global', 'status')
						});
						cm.getColumnByField('datum').component.updateDefinition({
							title: this.$p.t('global', 'datum')
						});
						cm.getColumnByField('mitarbeiter_uid').component.updateDefinition({
							title: this.$p.t('person', 'uid')
						});
						cm.getColumnByField('vertrag_id').component.updateDefinition({
							title: this.$p.t('ui', 'vertrag_id')
						});
						cm.getColumnByField('vertragsstatus_kurzbz').component.updateDefinition({
							title: this.$p.t('vertrag', 'vertragStatus')
						});
						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
						});
						cm.getColumnByField('updatevon').component.updateDefinition({
							title: this.$p.t('global', 'updatevon')
						});
						cm.getColumnByField('updateamum').component.updateDefinition({
							title: this.$p.t('global', 'updateamum')
						});
						cm.getColumnByField('insertvon').component.updateDefinition({
							title: this.$p.t('global', 'insertvon')
						});
						cm.getColumnByField('insertamum').component.updateDefinition({
							title: this.$p.t('global', 'insertamum')
						});
					}
				}
			],
			clickedRows: [],
			statusNew: true,
			formData: {
				vertragsstatus_kurzbz: null,
				datum: new Date()
			},
		}
	},
	watch: {
		vertrag_id() {
			//this.reloadTable();
			this.$refs.table.tabulator.setData('api/frontend/v1/vertraege/vertraege/getStatiOfContract/' + this.vertrag_id);
		},
		formDataParent: {
			handler(newVal, oldVal) {
				this.formData = this.formDataParent;
			},
			deep: true
		}
	},
	methods: {
		actionNewStatus() {
			this.resetModal();
			this.$refs.contractStatus.show();
		},
		actionEditStatus(vertrag_id, status) {
			this.statusNew = false;
			this.$emit('loadContractStatus', {
				status: status,
				vertrag_id: vertrag_id
			});
		},
		actionDeleteStatus(vertrag_id, status) {
			this.$emit('deleteContractStatus', {
				status: status,
				vertrag_id: vertrag_id
			  });
		},
		handleSubmit(action){
			if (action == 'new') {
				this.$emit('setContractStatus', {
				status: this.formData.vertragsstatus_kurzbz,
				datum: this.formData.datum
				});
			}
			if (action == 'edit') {
				this.$emit('updateContractStatus', {
					status: this.formData.vertragsstatus_kurzbz,
					datum: this.formData.datum
				});
			}
		},
		closeModal(){
			this.$refs.contractStatus.hide();
			this.$emit('close-modal');
		},
		openModal(){
			this.$refs.contractStatus.show();
			this.$emit('open-modal');
		},
		reload() {
			this.$refs.table.reloadTable();
			this.$emit('reload');
		},
		resetModal(){
			this.formData = {};
			this.formData.vertragsstatus_kurzbz = null;
			this.formData.datum = new Date();
			this.statusNew = true;
		}
	},
	template: `
	<div class="core-contracts-status h-50 d-flex flex-column w-100 mt-2">
		<br>
		<h5>{{$p.t('vertrag', 'vertragStatus')}}</h5>
	
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			:new-btn-label="this.$p.t('global', 'status')"
			@click:new="actionNewStatus"
			>
		</core-filter-cmpt>

		<div >
			<bs-modal ref="contractStatus">
				<template #title>
					<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('vertrag', 'addStatus')}}</p>
					<p v-else class="fw-bold mt-3">{{$p.t('vertrag', 'editStatus')}}</p>

				</template>

				<core-form ref="statusData">
					<div class="row mb-3">
						<form-input
							type="DatePicker"
							:label="$p.t('global/datum')"
							name="datum"
							v-model="formData.datum"
							auto-apply
							:enable-time-picker="true"
							format="dd.MM.yyyy"
							preview-format="dd.MM.yyyy"
							:teleport="true"
							>
						</form-input>
					</div>
					<div class="row mb-3">
						<form-input
							type="select"
							:label="$p.t('global/typ')"
							v-model="formData.vertragsstatus_kurzbz"
							name="vertragsstatus_kurzbz"
							:disabled="!statusNew"
							>
							<option :value="null">{{$p.t('ui', 'bitteWaehlen')}}</option>
							<option
								v-for="entry in listContractStati"
								:key="entry.vertragsstatus_kurzbz"
								:value="entry.vertragsstatus_kurzbz"
								>
								{{entry.bezeichnung}}
							</option>
						</form-input>
					</div>
				</core-form>

				<template #footer>
					<button type="button" class="btn btn-primary" :disabled="!this.hasSchreibrechte" @click="statusNew ? handleSubmit('new') : handleSubmit('edit')">{{$p.t('ui', 'speichern')}}</button>
				</template>

			</bs-modal>
		</div>

	</div>`
}