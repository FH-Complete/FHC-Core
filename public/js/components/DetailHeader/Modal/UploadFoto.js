import BsModal from "../../Bootstrap/Modal.js";
import FormForm from "../../Form/Form.js";
import FormInput from "../../Form/Input.js";

import ApiHandleFoto from "../../../../js/api/factory/fotoHandling.js";

export default {
	name: "modalUploadFoto",
	components: {
		BsModal,
		FormForm,
		FormInput,
	},
	props: {
		person_id: {
			type: Number,
			required: true
		}
	},
	data(){
		return{
			formData: {
				file: null,
				preview: null,
				base64Image: null,
			},
		}
	},
	methods:{
		open(person_id){
			this.$refs.modalUploadFoto.show(person_id);
		},
		onFileChange(e) {
			this.file = e.target.files[0];
			if (!this.file) return;

			// convert File in base64
			const reader = new FileReader();
			reader.onload = (event) => {
				this.base64Image = event.target.result;
				this.preview = this.base64Image;
			};
			reader.readAsDataURL(this.file);
		},

		async uploadImage(person_id) {
			if (!this.base64Image) {
				this.$fhcAlert.alertInfo(this.$p.t('header', 'alert_chooseFoto'));
				return;
			}

			return this.$api
				.call(ApiHandleFoto.uploadFoto(person_id, {
						image: this.base64Image,    // Base64 String
						filename: this.file.name    // Original name of file
							}))
				.then(result => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successFotoUpload'));
					this.resetModal();
					this.$refs.modalUploadFoto.hide();
					this.$emit('reload');
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		resetModal(){
			this.formData.foto = [];
		}
	},
	template: `
		<bs-modal
			ref="modalUploadFoto"
			dialog-class="modal-dialog-scrollable"
			>
				<template #title>
					Upload Foto
				</template>

				<form-form
					ref="formUploadFoto"
					>

					  <div>
						<input type="file" @change="onFileChange" accept="image/*" />
						<div v-if="preview">
						  <p>Preview:</p>
						  <img :src="preview" width="200" />
						</div>
					  </div>
				</form-form>

				<template #footer>
					<div class="d-grid gap-2 d-md-flex justify-content-md-end">
						<button type="button" class="btn btn-primary" @click="uploadImage(person_id)">{{$p.t('ui', 'hochladen')}}</button>
					</div>
				</template>

			</bs-modal>
	`,
}