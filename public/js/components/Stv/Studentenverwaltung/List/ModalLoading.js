import BsModal from "../../../Bootstrap/Modal.js";
import FormForm from "../../../Form/Form.js";

export default {
	name: "modalLoading",
	components: {
		BsModal,
		FormForm,
		PvProgressBar: primevue.progressbar
	},
	props: {
		isLoading: {
			type: Boolean,
			required: true
		},
		message: {
			type: String,
			required: true
		},
		progress: {
			type: Number,
			default: -1
		},
		total: {
			type: Number,
			default: -1
		},
		processed: {
			type: Number,
			default: -1
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
					{{ $p.t('ui/loading') }}
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

				<pv-progress-bar v-if="progress >= 0" class="my-3" :value="progress">{{processed}} / {{total}}</pv-progress-bar>

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