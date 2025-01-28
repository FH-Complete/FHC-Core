import BsModal from "../../../../Bootstrap/Modal.js";
import BsConfirm from "../../../../Bootstrap/Confirm.js";
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
		},
		defaultSemester: {
			from: 'defaultSemester'
		}
	},
	props: {
		personIds: {
			type: Array,
			required: true
		},
		stgKz: {
			type: Number,
			required: true
		},
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
	computed: {
		reversedSems() {
			return this.lists.studiensemester.toReversed();
		},
		activeBuchungstypen() {
			return this.lists.buchungstypen.filter(e => e.aktiv);
		}
	},
	methods: {
		save() {
			this.$refs.form.clearValidation();
			this.loading = true;

			const data = {...{
				person_id: this.personIds,
				studiengang_kz: this.stgKz
			}, ...this.data};

			this.$refs.form
				.factory.stv.konto.checkDoubles(data)
				.then(result => result.data
					? Promise.all(
						result.errors
							.filter(e => e.type == 'confirm')
							.map(e => BsConfirm.popup(Vue.h('div', {class:'text-preline'}, e.message)))
					)
					: Promise.resolve())
				.then(() => data)
				.then(this.$refs.form.factory.stv.konto.insert)
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
				buchungstyp_kurzbz: '',
				betrag: '-0.00',
				buchungsdatum: new Date(),
				buchungstext: '',
				mahnspanne: 30,
				studiensemester_kurzbz: this.defaultSemester,
				credit_points: null,
				anmerkung: ''
			};
			this.$refs.modal.show();
		},
		preventCloseOnLoading(ev) {
			if (this.loading)
				ev.returnValue = false;
		},
		checkDefaultBetrag(ev) {
			const typ = this.lists.buchungstypen.filter(e => e.buchungstyp_kurzbz == ev).pop();
			const amount = typ.standardbetrag || '-0.00';
			const text = typ.standardtext || '';
			const creditpoints = typ.credit_points || '';

			if (!this.data.betrag || this.data.betrag == '-0.00')
				this.data.betrag = amount;

			if (!this.data.buchungstext)
				this.data.buchungstext = text;

			if (this.config.showCreditpoints && (this.data.credit_points == '0.00' || this.data.credit_points === null))
				this.data.credit_points = creditpoints;
		}
	},
	template: `
	<core-form ref="form" class="stv-details-konto-edit" @submit.prevent="save">
		<bs-modal ref="modal" @hide-bs-modal="preventCloseOnLoading">
			<form-validation></form-validation>

			<fieldset :disabled="loading">
				<form-input
					type="select"
					v-model="data.buchungstyp_kurzbz"
					name="buchungstyp_kurzbz"
					:label="$p.t('konto/buchungstyp')"
					@update:model-value="checkDefaultBetrag"
					>
					<option v-for="typ in activeBuchungstypen" :key="typ.buchungstyp_kurzbz" :value="typ.buchungstyp_kurzbz" :class="typ.aktiv ? '' : 'text-decoration-line-through text-muted'">
						{{ typ.beschreibung }}
					</option>
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
					v-model="data.studiensemester_kurzbz"
					name="studiensemester_kurzbz"
					:label="$p.t('lehre/studiensemester')"
					>
					<option v-for="sem in reversedSems" :key="sem.studiensemester_kurzbz" :value="sem.studiensemester_kurzbz">
						{{ sem.studiensemester_kurzbz }}
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
					type="textarea"
					v-model="data.anmerkung"
					name="anmerkung"
					:label="$p.t('global/anmerkung')"
					>
				</form-input>
			</fieldset>

			<template #title>
				{{ $p.t(
					'stv',
					personIds.length > 1 ? 'konto_title_new_multi' : 'konto_title_new',
					{ x: personIds.length }
				) }}
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