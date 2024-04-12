import BsModal from "../../../../Bootstrap/Modal.js";
import CoreForm from "../../../../Form/Form.js";
import FormValidation from "../../../../Form/Validation.js";
import FormInput from "../../../../Form/Input.js";

// TODO(chris): Phrasen

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
				.post('api/frontend/v1/stv/konto/update', this.data)
				.then(result => {
					this.$emit('saved', result.data);
					this.loading = false;
					this.$refs.modal.hide();
					this.$fhcAlert.alertSuccess('Daten wurden gespeichert');
				})
				.catch(error => {console.log(error); // TODO(chris): check if working with current fhcApi
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
					label="Buchungsnr"
					disabled
					>
				</form-input>
				<form-input
					v-model="data.betrag"
					name="betrag"
					label="Betrag"
					>
				</form-input>
				<form-input
					type="DatePicker"
					v-model="data.buchungsdatum"
					name="buchungsdatum"
					label="Buchungsdatum"
					>
				</form-input>
				<form-input
					v-model="data.buchungstext"
					name="buchungstext"
					label="Buchungstext"
					>
				</form-input>
				<form-input
					v-if="config.showMahnspanne"
					v-model="data.mahnspanne"
					name="mahnspanne"
					label="Mahnspanne"
					>
				</form-input>
				<form-input
					type="select"
					v-model="data.buchungstyp_kurzbz"
					name="buchungstyp_kurzbz"
					label="Typ"
					>
					<option v-for="typ in lists.buchungstypen" :key="typ.buchungstyp_kurzbz" :value="typ.buchungstyp_kurzbz" :class="typ.aktiv ? '' : 'text-decoration-line-through text-muted'">
						{{ typ.beschreibung }}
					</option>
				</form-input>
				<form-input
					type="select"
					v-model="data.studiensemester_kurzbz"
					name="studiensemester_kurzbz"
					label="Studiensemester"
					>
					<option v-for="sem in lists.studiensemester" :key="sem.studiensemester_kurzbz" :value="sem.studiensemester_kurzbz">
						{{ sem.studiensemester_kurzbz }}
					</option>
				</form-input>
				<form-input
					type="select"
					v-model="data.studiengang_kz"
					name="studiengang_kz"
					label="Studiengang"
					>
					<option v-for="stg in lists.stgs" :key="stg.studiengang_kz" :value="stg.studiengang_kz">
						{{ stg.kuerzel }}
					</option>
				</form-input>
				<form-input
					v-if="config.showCreditpoints"
					v-model="data.credit_points"
					name="credit_points"
					label="Credit Points"
					>
				</form-input>
				<form-input
					v-model="data.zahlungsreferenz"
					name="zahlungsreferenz"
					label="Zahlungsreferenz"
					disabled
					>
				</form-input>
				<form-input
					type="textarea"
					v-model="data.anmerkung"
					name="anmerkung"
					label="Anmerkung"
					>
				</form-input>
			</fieldset>

			<template #title>
				Edit Buchung #{{data.buchungsnr}}
			</template>
			<template #footer>
				<button type="submit" class="btn btn-primary" :disabled="loading">
					<i v-if="loading" class="fa fa-spinner fa-spin"></i>
					Speichern
				</button>
			</template>
		</bs-modal>
	</core-form>`
};