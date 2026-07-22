import BsModal from "../../../Bootstrap/Modal.js";
import FormForm from "../../../Form/Form.js";

export default {
	name: "modalLoading",
	components: {
		BsModal,
		FormForm
	},
	props: {
		isLoading: {
			type: Boolean,
			required: true
		},
		message: {
			type: String,
			required: true
		}
	},
	data(){
		return{}
	},
	methods:{
		open(){
			this.$refs.modalLoading.show();
		},
		closeModal(){
			this.$refs.modalLoading.hide();
		}
	},
	template: `
		<bs-modal
			ref="modalLoading"
			noCloseBtn
			backdrop='static'
			:keyboard='false'
			dialog-class="modal-dialog-scrollable"
			>
				<template #title>
					Loading
				</template>

				<form-form
					ref="formModal"
					>
						<div class="mt-3">
							<div v-if="isLoading">
								{{message}}
						  </div>
						  <div v-else>
							{{$p.t('ui', 'fenster_schliessen')}}
						  </div>
					  </div>
				</form-form>

				<template #footer>
					<div class="d-grid gap-2 d-md-flex justify-content-md-end">
					    <button
							class="btn btn-primary"
							:disabled="isLoading"
							@click="closeModal()">
								{{$p.t('ui', 'schliessen')}}
						</button>
					</div>
				</template>

			</bs-modal>
	`,
}