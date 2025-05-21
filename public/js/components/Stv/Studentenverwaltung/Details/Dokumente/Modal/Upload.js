import BsModal from "../../../../../Bootstrap/Modal.js";
import FormForm from "../../../../../Form/Form.js";
import FormInput from "../../../../../Form/Input.js";
import FormUploadDms from "../../../../../Form/Upload/Dms.js";

import ApiStvDocuments from "../../../../../../api/factory/stv/documents.js";

export default {
	name: "modalUploadDocuments",
	components: {
		BsModal,
		FormForm,
		FormInput,
		FormUploadDms
	},
	data(){
		return{
			formData: {
				dokument_kurzbz: null,
				anhang: [],
				anmerkung_intern: null
			},
			listDokTypen: []
		}
	},
	methods:{
		open(prestudent_id, dokument_kurzbz){
			this.formData.dokument_kurzbz = dokument_kurzbz;
			this.formData.prestudent_id = prestudent_id;
			this.$refs.modalUploadFile.show();
		},
		uploadFile(){
		//	console.log(this.formData.anhang[0]);
			return this.$api
				.call(ApiStvDocuments.uploadFile(this.formData.prestudent_id, this.formData))
				.then(result => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successUpload'));
					this.resetModal();
					this.$refs.modalUploadFile.hide();
					this.$emit('reload');
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		resetModal(){
			this.formData.dokument_kurzbz = null;
			this.formData.anhang = [];
			this.formData.anmerkung_intern = null;
			this.formData.titel_intern = null;
		}
	},
	created(){
		this.$api
			.call(ApiStvDocuments.getDoktypen())
			.then(result => {
				this.listDokTypen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
			<bs-modal
			ref="modalUploadFile"
			dialog-class="modal-dialog-scrollable"
		>
			<template #title>
				Upload {{ $p.t('stv', 'tab_documents') }} 
			</template>

			<form-form
				ref="formUploadFile"
				>

				<div>
					<div class="row mb-3">
						<label for="text" class="form-label col-sm-2">{{$p.t('global','dokument')}}</label>
						<FormUploadDms ref="upload" id="file" v-model="formData.anhang"></FormUploadDms>
					</div>

					<div class="row mb-3">
						<form-input
							type="select"
							name="dokument_kurzbz"
							:label="$p.t('dokumente/dokTyp')"
							v-model="formData.dokument_kurzbz"
						>
						<option value=null> -- {{ $p.t('fehlermonitoring', 'keineAuswahl') }} --</option>
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

				</div>

			</form-form>

			<template #footer>
				<div class="d-grid gap-2 d-md-flex justify-content-md-end">
					<button type="button" class="btn btn-primary" @click="uploadFile">{{$p.t('ui', 'hochladen')}}</button>
				</div>
			</template>

		</bs-modal>
	`,
}