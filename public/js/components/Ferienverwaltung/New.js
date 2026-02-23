import BsModal from "../Bootstrap/Modal.js";
import BsConfirm from "../Bootstrap/Confirm.js";
import CoreForm from "../Form/Form.js";
import FormValidation from "../Form/Validation.js";
import FormInput from "../Form/Input.js";

import ApiFerien from '../../api/factory/ferienverwaltung/ferienverwaltung.js';

export default {
	components: {
		BsModal,
		CoreForm,
		FormValidation,
		FormInput
	},
	props: {
		studiengang_kz_list: {
			type: Array,
			required: true
		}
	},
	data() {
		return {
			loading: false,
			data: {},
		};
	},
	computed: {
	},
	methods: {
		save() {
			this.$refs.form.clearValidation();
			this.loading = true;

			this.$refs.form
				.call(ApiFerien.insert(this.data))
				.then(result => {
					this.$emit('saved', result.data);
					this.loading = false;
					this.$refs.modal.hide();
					this.$fhcAlert.alertSuccess(this.$p.t('ui/gespeichert'));
				})
				.catch(error => {
					if (error)
						this.$fhcAlert.handleSystemError(error);
					this.loading = false;
				});
		},
		open() {
			this.data = {
				studiengang_kz: null,
				bezeichnung: '',
				vondatum: null,
				bisdatum: null
			};
			this.$refs.modal.show();
		},
		preventCloseOnLoading(ev) {
			if (this.loading)
				ev.returnValue = false;
		}
	},
	template: `
	<core-form ref="form" class="stv-details-ferien-edit" @submit.prevent="save">
		<bs-modal ref="modal" @hide-bs-modal="preventCloseOnLoading">
			<form-validation></form-validation>

			<fieldset :disabled="loading">
				<form-input
					type="DatePicker"
					v-model="data.vondatum"
					name="vondatum"
					:label="$p.t('ferien/vondatum')"
					:enable-time-picker="false"
					text-input
					format="dd.MM.yyyy"
					auto-apply
					>
				</form-input>

				<form-input
					type="DatePicker"
					v-model="data.bisdatum"
					name="bisdatum"
					:label="$p.t('ferien/bisdatum')"
					:enable-time-picker="false"
					text-input
					format="dd.MM.yyyy"
					auto-apply
					>
				</form-input>

				<form-input
					v-model="data.bezeichnung"
					name="bezeichnung"
					:label="$p.t('global/bezeichnung')"
					>
				</form-input>

				<form-input
					type="select"
					v-model="data.studiengang_kz"
					name="studiengang_kz"
					:label="$p.t('lehre/studiengang')"
					>
					<option v-for="studiengang in studiengang_kz_list" :key="studiengang.studiengang_kz" :value="studiengang.studiengang_kz">
						{{ studiengang.kuerzel }}
					</option>
				</form-input>
			</fieldset>

			<template #footer>
				<button type="submit" class="btn btn-primary" :disabled="loading">
					<i v-if="loading" class="fa fa-spinner fa-spin"></i>
					{{ $p.t('ui/speichern') }}
				</button>
			</template>
		</bs-modal>
	</core-form>`
};