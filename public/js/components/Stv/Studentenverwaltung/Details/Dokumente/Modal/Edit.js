import BsModal from "../../../../../Bootstrap/Modal.js";
import FormForm from "../../../../../Form/Form.js";
import FormInput from "../../../../../Form/Input.js";

export default {
	name: "modalEditDocuments",
	components: {
		BsModal,
		FormForm,
		FormInput
	},
	data(){
		return {
			formData: [],
			listDokTypen: []
		}
	},
	methods: {
		updateFile(akte_id){
			console.log("in update" + akte_id);

			this.$fhcApi.factory.stv.documents.updateFile(akte_id, this.formData).
			then(response => {
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
				this.$refs.modalEditDocument.hide();
				this.$emit('reload');
			})
			.catch(this.$fhcAlert.handleSystemError);
		},
		open(akte_id){
			this.loadFormData(akte_id);
			this.$refs.modalEditDocument.show();
		},
		loadFormData(akte_id){
			return this.$fhcApi.factory.stv.documents.loadAkte(akte_id)
				.then(result => {
					this.formData = result.data;
					return result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	created(){
		this.$fhcApi.factory.stv.documents.getDoktypen()
			.then(result => {
				this.listDokTypen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
		<bs-modal
			ref="modalEditDocument"
			dialog-class="modal-dialog-scrollable"
		>
			<template #title>
				{{ $p.t('dokumente', 'dokDetails') }} {{formData.nameperson}}
			</template>

			<form-form
				ref="formEditDocument"
				>
				<div>
					<div class="row mb-3">
						<form-input
							type="select"
							name="dokument_kurzbz"
							:label="$p.t('dokumente/dokTyp')"
							v-model="formData.dokument_kurzbz"
						>
							<option
								v-for="typ in listDokTypen"
								:key="typ.dokument_kurzbz"
								:value="typ.dokument_kurzbz" >{{typ.bezeichnung}}
							</option>
						</form-input>
					</div>

					<div class="row mb-3">
						<form-input
							type="text"
							name="titel_intern"
							:label="$p.t('dokumente/title')"
							v-model="formData.titel_intern"
						>
						</form-input>
					</div>

					<div class="row mb-3">
						<form-input
							type="textarea"
							name="anmerkung"
							:label="$p.t('global/anmerkung')"
							v-model="formData.anmerkung_intern"
							rows="5"
						>
						</form-input>
					</div>			

					<div class="row mb-3">
						<form-input
							type="DatePicker"
							v-model="formData.nachgereicht_am"
							name="nachgereicht_am"
							:label="$p.t('dokumente/nachreichungAm')"
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
							type="textarea"
							name="anmerkung"
							:label="$p.t('dokumente/anmerkung_person')"
							v-model="formData.anmerkung"
							rows="5"
							disabled
						>
						</form-input>
					</div>

				</div>

			</form-form>

			<template #footer>
				<div class="d-grid gap-2 d-md-flex justify-content-md-end">
					<button type="button" class="btn btn-primary" @click="updateFile(formData.akte_id)">{{$p.t('global', 'speichern')}}</button>
				</div>
			</template>

		</bs-modal>
	`,
}
