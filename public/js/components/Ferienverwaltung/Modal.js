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
	data() {
		return {
			oeList: [],
			studienplaeneList: [],
			ferientypList: [],
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

			let saveFunc = this.data.ferien_id ? ApiFerien.update : ApiFerien.insert;

			this.$refs.form
				.call(saveFunc(this.data))
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
		open(data) {
			this.$refs.form.clearValidation();
			this.data = data ?? {
				oe_kurzbz: null,
				bezeichnung: '',
				vondatum: null,
				bisdatum: null,
				studienplan_id: null
			};

			this.$api
			.call(ApiFerien.getOe())
			.then(result => {
					this.oeList = result.data;
					//this.loading = false;
				}
			)
			.catch(error => {
				if (error)
					this.$fhcAlert.handleSystemError(error);
				//this.loading = false;
			});


			this.getStudienplaene();

			this.$api
			.call(ApiFerien.getFerientypen())
			.then(result => {
					this.ferientypList = result.data;
				}
			)
			.catch(error => {
				if (error)
					this.$fhcAlert.handleSystemError(error);
			});

			this.$refs.modal.show();
		},
		getStudienplaene() {
			if (!this.data.oe_kurzbz) return;

			this.$api
			.call(ApiFerien.getStudienplaene(this.data.oe_kurzbz, this.data.vondatum, this.data.bisdatum))
			.then(result => {
					this.studienplaeneList = result.data;
				}
			)
			.catch(error => {
				if (error)
					this.$fhcAlert.handleSystemError(error);
			});
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
					v-model="data.oe_kurzbz"
					name="oe_kurzbz"
					:label="$p.t('ferien/organisationseinheit')"
					@change="getStudienplaene"
					>
					<option v-for="oe in oeList" :key="oe.oe_kurzbz" :value="oe.oe_kurzbz">
						{{ oe.organisationseinheittyp_kurzbz + ' ' + oe.bezeichnung }}
					</option>
				</form-input>

				<form-input
					type="select"
					v-model="data.studienplan_id"
					name="studienplan_id"
					:label="$p.t('ferien/studienplan')"
					>
					<option :value="null">-- {{ $p.t('ui/keineAuswahl') }} --</option>
					<option v-for="studienplan in studienplaeneList" :key="studienplan.studienplan_id" :value="studienplan.studienplan_id">
						{{ studienplan.bezeichnung }}
					</option>
				</form-input>

				<form-input
					type="select"
					v-model="data.ferientyp_kurzbz"
					name="ferientyp_kurzbz"
					:label="$p.t('ferien/ferientypKurzbz')"
					>
					<option :value="null">-- {{ $p.t('ui/keineAuswahl') }} --</option>
					<option v-for="ferientyp in ferientypList" :key="ferientyp.ferientyp_kurzbz" :value="ferientyp.ferientyp_kurzbz">
						{{ ferientyp.ferientyp_kurzbz }}
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