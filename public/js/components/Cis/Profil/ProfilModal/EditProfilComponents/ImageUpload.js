import Dms from "../../../../Form/Upload/Dms.js";
import BsModal from "../../../../Bootstrap/Modal.js";

import ApiProfilUpdate from '../../../../../api/factory/profilUpdate.js';

export default {
	data() {
		return {
			dmsData: [],
		};
	},
	components: {
		Dms,
		BsModal,
	},
	mixins: [BsModal],
	props: {
		titel: {
			type: Object,
		},
		files: {
			type: Array,
		},
		updateID: {
			type: Boolean,
		},
		onHideBsModal: Function,
		onHiddenBsModal: Function,
		onHidePreventedBsModal: Function,
		onShowBsModal: Function,
		onShownBsModal: Function,
	},
	methods:{
		async uploadImage(){
			if(this.dmsData){
				let formData = new FormData();
				formData.append("files[]", this.dmsData[0]);
				await this.$api
					.call(ApiProfilUpdate.updateProfilbild(formData))
					.then((res) => {
						console.log("here", res.data?.map((file) => file.dms_id)) ;
					});	
			}
			
		}
	},
	mounted() {
		this.modal = this.$refs.modalContainer.modal;
		if (this.files) {
			this.dmsData = this.files;
		}
	},
	popup(options) {
			BsModal.popup.bind(this);
			return BsModal.popup(null, options);
	},
	template: /*html*/`

	<bs-modal v-show="!loading" ref="modalContainer" v-bind="$props" body-class="" dialog-class="modal-lg" class="bootstrap-alert" :backdrop="false">
		<template #title>
			<p style="opacity:0.8" class="ms-2" v-if="!updateID">{{$p.t('profilUpdate','profilUpdateInformationMessage',[titel])}}</p>
		</template>
		<template #default>
			<div class="form-underline">
				<div class="form-underline-titel">{{titel?titel:$p.t('global','titel')}}</div>
			</div>
			<div class="row gx-2">
				<div class="col">
					<dms ref="update" id="files" name="files" :multiple="false" v-model="dmsData" @update:model-value="didFilesChange"  ></dms>
				</div>
				<div class="col-auto">
					<button @click="dmsData=[]" class="btn btn-danger"><i style="color:white" class="fa fa-trash"></i></button>
				</div>
			</div>
			<div class="d-flex" style="margin-left:auto;">
				<button @click="uploadImage" class="btn btn-primary">upload</button>
			</div>
		</template>
		<template #footer>
		</template>
	</bs-modal>
    `,
};
