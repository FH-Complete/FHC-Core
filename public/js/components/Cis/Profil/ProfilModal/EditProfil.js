import BsModal from "../../../Bootstrap/Modal.js";
import Alert from "../../../Bootstrap/Alert.js";
import EditProfilSelect from "./EditProfilSelect.js";
import Loader from "../../../Loader.js";

export default {
	components: {
		BsModal,
		Alert,
		EditProfilSelect,
		Loader,
	},
	mixins: [BsModal],
	props: {
		value: Object,
		title: String,
		zustelladressenCount: Function,
		zustellkontakteCount: Function,
		/*
		 * NOTE(chris):
		 * Hack to expose in "emits" declared events to $props which we use
		 * in the v-bind directive to forward all events.
		 * @see: https://github.com/vuejs/core/issues/3432
		 */
		onHideBsModal: Function,
		onHiddenBsModal: Function,
		onHidePreventedBsModal: Function,
		onShowBsModal: Function,
		onShownBsModal: Function,
	},

	data() {
		return {
			topic: null,
			profilUpdate: null,
			editData: this.value,
			fileID: null,
			breadcrumb: null,
			loading: false,
			result: false,
			info: null,
		};
	},
	inject: ["setLoading"],
	provide() {
		return {
			updateFileID: this.updateFileIDFunction,
		};
	},

	methods: {
		updateFileIDFunction: function (newFileID) {
			this.fileID = newFileID;
		},

		handleFailedError: function (err) {
			console.error(err);
			this.loading = false;
			this.setLoading(false);
			this.result = false;
			this.hide();
		},

		async submitProfilChange() {

			//? check if data is valid before making a request
			if (this.topic && this.profilUpdate) {
				//? if profil update contains any attachment
				if (this.fileID) {
					const fileData = await this.uploadFiles(this.fileID);

					this.fileID = fileData ? fileData : null;
				}

				//? inserts new row in public.tbl_cis_profil_update
				//* calls the update api call if an update field is present in the data that was passed to the modal
				const handleApiResponse = (res) => {
					//? toggles the loading to false and closes the loading modal
					if (res.data.error) {
						this.result = false;
						Alert.popup(
							"Ein Fehler ist aufgetreten: " + JSON.stringify(res.data.retval)
						);
					} else {
						this.result = true;
						Alert.popup(
							"Ihre Anfrage wurde erfolgreich gesendet. Bitte warten Sie, während sich das Team um Ihre Anfrage kümmert."
						);
					}
				};

				//* v-show on EditProfil modal binded to this.loading
				//? hides the EditProfil modal and shows the loading modal by calling a callback that was passed as prop from the parent component
				this.loading = true;
				this.setLoading(true);

				//? if an updateID is present, updateProfilRequest is called, else insertProfilRequest is called
				this.editData.updateID ?
					this.$fhcApi.factory.profilUpdate.updateProfilRequest(
						this.topic,
						this.profilUpdate,
						this.editData.updateID,
						this.fileID ? this.fileID[0] : null
					)
						.then((res) => {
							handleApiResponse(res);
						})
						.catch((err) => this.$fhcAlert.handleSystemError)
						.finally(() => {
							this.loading = false;
							this.setLoading(false);
							this.hide();
						})
					:
					this.$fhcApi.factory.profilUpdate.insertProfilRequest(
						this.topic,
						this.profilUpdate,
						this.fileID ? this.fileID[0] : null
					)
						.then((res) => {
							handleApiResponse(res);
						})
						.catch((err) => this.$fhcAlert.handleSystemError)
						.finally(() => {
							this.loading = false;
							this.setLoading(false);
							this.hide();
						});
			}
		},

		uploadFiles: async function (files) {
			if (files[0].type !== "application/x.fhc-dms+json") {
				let formData = new FormData();
				formData.append("files[]", files[0]);
				const result = this.editData.updateID
					? //? updating old attachment by replacing
					  //* second parameter of api request insertFile checks if the file has to be replaced or not
					await this.$fhcApi.factory.profilUpdate.insertFile(
						formData,
						this.editData.updateID
					).then((res) => {
						return res.data?.map((file) => file.dms_id);
					})
					: //? fresh insert of new attachment
					await this.$fhcApi.factory.profilUpdate.insertFile(formData).then((res) => {
						return res.data?.map((file) => file.dms_id);
					});
				return result;
			} else {
				//? attachment hasn't been replaced
				return false;
			}
		},
	},
	computed: {
		showFooter: function () {
			switch (this.value.view) {
				case 'Status':
					return false;
				case 'Kontakt':
					return false;
				case 'Adresse':
					return false;
				default:
					return true;
			}
		},
	},
	created() {
		if (this.editData.topic) {
			//? if the topic was passed through the prop add it to the component
			this.topic = this.editData.topic;
		}
	},
	mounted() {
		this.modal = this.$refs.modalContainer.modal;
	},
	popup(options) {
		BsModal.popup.bind(this);
		return BsModal.popup(null, options);
	},
	template: /*html*/ `
<bs-modal v-show="!loading" ref="modalContainer" v-bind="$props" body-class="" dialog-class="modal-lg" class="bootstrap-alert" backdrop="false">
	<template v-if="title" v-slot:title>{{title}}</template>
	<template v-slot:default>
		<div>
			<nav aria-label="breadcrumb" class="ps-2">
				<ol class="breadcrumb">
					<li class="breadcrumb-item" v-for="element in breadcrumb">{{element}}</li>
				</ol>
			</nav>
			<edit-profil-select @submit="submitProfilChange" v-model:breadcrumb="breadcrumb" v-model:topic="topic" v-model:profilUpdate="profilUpdate" ariaLabel="select" :list="editData"></edit-profil-select>
		</div>
	</template>
	<!-- optional footer -->
	<template v-slot:footer v-if="showFooter">
		<loader  ref="loaderRef" :timeout="0"></loader>
		<button class="btn btn-outline-danger " @click="hide">{{$p.t('ui','cancel')}}</button>    
		<button :disabled="!profilUpdate"  @click="submitProfilChange" role="button" class="btn btn-primary">{{$p.t('ui','senden')}}</button>
	</template>
  <!-- end of optional footer --> 
</bs-modal>`,
};
