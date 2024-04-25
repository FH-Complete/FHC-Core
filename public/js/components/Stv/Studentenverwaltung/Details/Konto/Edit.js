import BsModal from "../../../../Bootstrap/Modal.js";
import CoreForm from "../../../../Form/Form.js";
import FormValidation from "../../../../Form/Validation.js";
import FormInput from "../../../../Form/Input.js";


export default {
	components: {
		BsModal,
		CoreForm,
		FormValidation,
		FormInput
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
			data: {}
		};
	},
	methods: {
		save() {
			this.$refs.form.clearValidation();
			this.loading = true;

			this.$refs.form
				.factory.stv.konto.edit(this.data)
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
			this.data = {...data};
			this.$refs.modal.show();
		},
		preventCloseOnLoading(ev) {
			if (this.loading)
				ev.returnValue = false;
		}
	},
	template: `
	<core-form ref="form" class="stv-details-konto-edit" @submit.prevent="save">
		<bs-modal ref="modal" @hide-bs-modal="preventCloseOnLoading">
			<form-validation></form-validation>

			<fieldset :disabled="loading">
				<form-input
					v-if="config.showBuchungsnr"
					v-model="data.buchungsnr"
					name="buchungsnr"
					:label="$p.t('konto/buchungsnr')"
					disabled
					>
				</form-input>
				<form-input
					v-model="data.betrag"
					name="betrag"
					:label="$p.t('konto/betrag')"
					>
				</form-input>
				<form-input
					type="DatePicker"
					v-model="data.buchungsdatum"
					name="buchungsdatum"
					:label="$p.t('konto/buchungsdatum')"
					:enable-time-picker="false"
					auto-apply
					>
				</form-input>
				<form-input
					v-model="data.buchungstext"
					name="buchungstext"
					:label="$p.t('konto/buchungstext')"
					>
				</form-input>
				<form-input
					v-if="config.showMahnspanne"
					v-model="data.mahnspanne"
					name="mahnspanne"
					:label="$p.t('konto/mahnspanne')"
					>
				</form-input>
				<form-input
					type="select"
					v-model="data.buchungstyp_kurzbz"
					name="buchungstyp_kurzbz"
					:label="$p.t('konto/buchungstyp')"
					>
					<option v-for="typ in lists.buchungstypen" :key="typ.buchungstyp_kurzbz" :value="typ.buchungstyp_kurzbz" :class="typ.aktiv ? '' : 'text-decoration-line-through text-muted'">
						{{ typ.beschreibung }}
					</option>
				</form-input>
				<form-input
					type="select"
					v-model="data.studiensemester_kurzbz"
					name="studiensemester_kurzbz"
					:label="$p.t('lehre/studiensemester')"
					>
					<option v-for="sem in lists.studiensemester" :key="sem.studiensemester_kurzbz" :value="sem.studiensemester_kurzbz">
						{{ sem.studiensemester_kurzbz }}
					</option>
				</form-input>
				<form-input
					type="select"
					v-model="data.studiengang_kz"
					name="studiengang_kz"
					:label="$p.t('lehre/studiengang')"
					>
					<option v-for="stg in lists.stgs" :key="stg.studiengang_kz" :value="stg.studiengang_kz">
						{{ stg.kuerzel }}
					</option>
				</form-input>
				<form-input
					v-if="config.showCreditpoints"
					v-model="data.credit_points"
					name="credit_points"
					:label="$p.t('konto/credit_points')"
					>
				</form-input>
				<form-input
					v-model="data.zahlungsreferenz"
					name="zahlungsreferenz"
					:label="$p.t('konto/reference')"
					disabled
					>
				</form-input>
				<form-input
					type="textarea"
					v-model="data.anmerkung"
					name="anmerkung"
					:label="$p.t('global/anmerkung')"
					>
				</form-input>
			</fieldset>

			<template #title>
				{{ $p.t('stv/konto_title_edit', data) }}
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