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
		}
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.$fhcApi.factory.vertraege.person.getStatiOfContract,
				ajaxParams: () => {
					return {
						vertrag_id: this.vertrag_id
					};
				},
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Status", field: "bezeichnung"},
					{title: "Datum", field: "format_datum"},
					{title: "vertrag_id", field: "vertrag_id", visible: false},
					// {title: "User", field: "bezeichnung", visible: false},
					{title: "insertvon", field: "insertvon", visible: false},
					{title: "insertamum", field: "format_insertamum", visible: false},
					{title: "updatevon", field: "updatevon", visible: false},
					{title: "updateamum", field: "format_updateamum", visible: false},
					{title: "betreuerart_kurzbz", field: "betreuerart_kurzbz", visible: false},
					{title: "Vertragsstunden", field: "vertragsstunden", visible: false},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150,
						formatter: (cell, formatterParams, onRendered) => {

							const container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = 'Status bearbeiten';
							button.addEventListener(
								'click',
								(event) =>
									this.actionEditStatus(cell.getData().vertrag_id, cell.getData().vertragsstatus_kurzbz)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = 'Status löschen';
							button.addEventListener(
								'click',
								() =>
									this.actionDeleteStatus(cell.getData().vertrag_id, cell.getData().vertragsstatus_kurzbz)
							);

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
			},
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
	<!--TODO(Manu) check filter (akzeptiert, neu, erteilt?), design -->
	<div class="core-vertraege h-50 d-flex flex-column w-100">
		<br>
		<h4>Vertragsstatus</h4>
	
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			new-btn-label="Status"
			@click:new="actionNewStatus"
			>
		</core-filter-cmpt>

		<div >
			<bs-modal ref="contractStatus">
				<template #title>
					<p class="fw-bold mt-3">{{$p.t('ui', 'add_Status')}}</p>

				</template>

				<core-form>
					<div class="row mb-3">
						<form-input
							type="DatePicker"
							:label="$p.t('global/datum')"
							name="datum"
							v-model="formData.datum"
							auto-apply
							:enable-time-picker="false"
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
					<button type="button" class="btn btn-primary" @click="statusNew ? handleSubmit('new') : handleSubmit('edit')">{{$p.t('ui', 'speichern')}}</button>
				</template>

			</bs-modal>
		</div>

	</div>`
}