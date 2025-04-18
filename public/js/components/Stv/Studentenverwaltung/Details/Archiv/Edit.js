import BsModal from "../../../../Bootstrap/Modal.js";
import CoreForm from "../../../../Form/Form.js";
import FormValidation from "../../../../Form/Validation.js";
import FormInput from "../../../../Form/Input.js";
import FormUploadDms from '../../../../Form/Upload/Dms.js';

export default {
	components: {
		BsModal,
		CoreForm,
		FormValidation,
		FormInput,
		FormUploadDms
	},
	inject: {
		lists: {
			from: 'lists'
		}
	},
	props: {
		config: {
			type: Object,
			default: {}
		}
	},
	data() {
		return {
			loading: false,
			//file: [],
			data: {
				datei: []
			}
		};
	},
	methods: {
		save() {
			this.$refs.form.clearValidation();
			this.loading = true;

			//~ const formData = new FormData();
			//~ formData.append('data', JSON.stringify(this.data));
			//Object.entries(this.data.anhang).forEach(([k, v]) => formData.append(k, v));

			this.$refs.form
				.factory.stv.archiv.update(this.data)
				.then(result => {
					this.$emit('saved', result.data);
					this.loading = false;
					this.$refs.modal.hide();
					this.$fhcAlert.alertSuccess(this.$p.t('ui/gespeichert'));
				})
				.catch(error => {
					this.$fhcAlert.handleSystemError(error);
					this.loading = false;
				});
		},
		open(data) {
			this.data.datei = [];
			this.data = {...this.data, ...data};
			this.$refs.modal.show();
		},
		preventCloseOnLoading(ev) {
			if (this.loading)
				ev.returnValue = false;
		}
	},
	template: `
	<core-form ref="form" class="stv-details-archiv-edit" @submit.prevent="save">
		<bs-modal ref="modal" @hide-bs-modal="preventCloseOnLoading">
			<form-validation></form-validation>
			<fieldset :disabled="loading">
				<div class="mb-3">
					{{ data.titel }} ({{ data.bezeichnung }})
				</div>
				<form-input
					v-model="data.akte_id"
					name="akte_id"
					:label="$p.t('stv/archiv_akte_id')"
					disabled
					>
				</form-input>
				<div class="position-relative">
					<label for="text" class="form-label col-sm-2">{{ $p.t('stv/archiv_new_file') }}</label>
					<!--Upload Component-->
					<FormUploadDms ref="upload" id="inhalt" v-model="data.datei"></FormUploadDms>
				</div>
				<div class="mt-3">
					<form-input
						container-class="form-check"
						type="checkbox"
						name="signiert"
						:label="$p.t('stv/archiv_signiert')"
						v-model="data.signiert"
					>
					</form-input>
						<form-input
							container-class="form-check"
							type="checkbox"
							name="stud_selfservice"
							:label="'Selfservice'"
							v-model="data.stud_selfservice"
					>
				</div>
				</form-input>
			</fieldset>

			<template #title>
				{{ $p.t('stv/archiv_title_edit', data) }}
			</template>
			<template #footer>
				<button type="submit" class="btn btn-primary" :disabled="loading">
					<i v-if="loading" class="fa fa-spinner fa-spin"></i>
					{{ $p.t('ui/speichern') }}
				</button>
			</template>
		</bs-modal>
	</core-form>`
};