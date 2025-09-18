import {CoreFilterCmpt} from "../../filter/Filter.js";
import CoreForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';
import ApiLektor from "../../../api/lehrveranstaltung/lektor.js";

export default{
	name: "LVLektorDaten",
	components: {
		CoreFilterCmpt,
		CoreForm,
		FormInput
	},
	props: {
		lehreinheit_id: Number,
		mitarbeiter_uid: String
	},
	emits: [
		'changedLektor',
		'changedCosts',
	],
	inject: {
		dropdowns: {
			from: 'dropdowns'
		}
	},

	data() {
		return{
			original: null,
			data: null,
			changed: {},
			internal_mitarbeiter_uid: null,
			filteredLektor: [],
		}
	},
	computed: {
		changedLength() {
			return Object.keys(this.changed).length;
		},
		berechneteGesamtkosten() {
			if (!this.data) return 0;

			const stunden = Number(this.data.semesterstunden) || 0;
			const stundensatz = Number(this.data.stundensatz) || 0;

			return (stunden * stundensatz).toFixed(2);
		}
	},
	watch: {
		lehreinheit_id:
		{
			deep: true,
			handler(newVal, oldVal) {
				this.data = null;
				this.original = null;
				this.internal_mitarbeiter_uid = null;
			}
		},
		mitarbeiter_uid:
		{
			deep: true,
			handler(newVal, oldVal) {
				this.internal_mitarbeiter_uid = newVal;

				if (newVal === null)
					this.data = null;
				else if (newVal !== undefined && this.lehreinheit_id !== undefined)
					this.getLektorData();
			}

		},
		data: {
			handler(newValue) {
				if (newValue === null)
				{
					this.changed = {}
					return
				}
				let changed = {};

				let keys = Object.keys(this.original);

				for (let key of keys)
				{
					if (this.original[key] !== newValue[key])
						changed[key] = newValue[key];
				}
				this.changed = changed;
			},
			deep: true
		},
	},
	methods: {
		getLektorData()
		{
			if (!this.lehreinheit_id || !this.internal_mitarbeiter_uid)
				return;

			return this.$api.call(ApiLektor.getLektorDaten(this.lehreinheit_id, this.internal_mitarbeiter_uid))
				.then(result => {
					this.data = result.data;
					this.original = { ...this.data };
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateDaten()
		{
			if (!this.changedLength)
				return;

			if (this.changed.mitarbeiter_uid && this.changed.mitarbeiter_uid.uid)
			{
				this.changed.mitarbeiter_uid = this.changed.mitarbeiter_uid.uid;
			}
			this.$refs.form.clearValidation();

			let updatedData = {
				lehreinheit_id: this.lehreinheit_id,
				mitarbeiter_uid: this.mitarbeiter_uid,
				formData: this.changed
			}
			this.$refs.form.call(ApiLektor.update(updatedData))
				.then(result => {
					let warning = result.data?.retval?.warning;
					if (warning)
						this.$fhcAlert.alertWarning(warning)
					this.original = {...this.data};

					if (this.changed.mitarbeiter_uid)
					{
						this.$emit('changedLektor', this.changed.mitarbeiter_uid);
					}
					if (this.changed.semesterstunden || this.changed.stundensatz)
					{
						this.$emit('changedCosts');
					}
					this.changed = {};

				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		searchLektor(event)
		{
			const query = event.query.toLowerCase().trim();
			this.filteredLektor = this.dropdowns.lektor_array.filter(lektor => {
				const fullName = `${lektor.vorname.toLowerCase()} ${lektor.nachname.toLowerCase()}`;
				const reverseFullName = `${lektor.nachname.toLowerCase()} ${lektor.vorname.toLowerCase()}`;
				return fullName.includes(query) || reverseFullName.includes(query) || lektor.uid.toLowerCase().includes(query);
			}).map(lektor => ({
				label: `${lektor.nachname} ${lektor.vorname} (${lektor.uid})`,
				uid: lektor.uid
			}));
		},

	},
	created() {
		this.getLektorData()
	},
	template: `
		<core-form ref="form" @submit.prevent="updateDaten">
			<div class="position-sticky top-0 z-1">
				<button type="submit" class="btn btn-primary position-absolute top-0 end-0" :disabled="!changedLength">{{$p.t('ui', 'speichern')}}</button>
			</div>
		<fieldset class="overflow-hidden">
			<legend>{{$p.t('lehre', 'daten')}}</legend>
			<template v-if="data">
				<div class="row align-items-start mb-3">
					<form-input
						:label="$p.t('lehre', 'lehrfunktion')"
						type="select"
						container-class="col-3"
						v-model="data.lehrfunktion_kurzbz"
						name="lehrfunktion_kurzbz"
						>
						<option
							v-for="lehrfunktion in dropdowns.lehrfunktion_array"
							:value="lehrfunktion.lehrfunktion_kurzbz"
						>
							{{ lehrfunktion.lehrfunktion_kurzbz }}
						</option>
					</form-input>
					

					<form-input
						type="autocomplete"
						:label="$p.t('lehre', 'lektor')"
						:disabled="data.vertrag_id !== null"
						:suggestions="filteredLektor"
						placeholder="Mitarbeiter hinzufügen"
						v-model="data.mitarbeiter_uid"
						field="label"
						container-class="col-3"
						dropdown
						@complete="searchLektor"
						name="lektorautocomplete"
					></form-input>
					
					<form-input
						:label="$p.t('lehre', 'anmerkung')"
						type="text"
						container-class="col-3"
						v-model="data.anmerkung"
						name="anmerkung"
						>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						:label="$p.t('lehre', 'las')"
						type="number"
						min="0"
						step="0.01"
						container-class="col-3"
						:disabled="data.vertrag?.vertragsstatus_kurzbz === 'akzeptiert'"
						v-model="data.semesterstunden"
						name="semesterstunden"
						>
					</form-input>
					
					<form-input
						:label="$p.t('lehre', 'planstunden')"
						type="number"
						min="0"
						step="0.01"
						container-class="col-3"
						v-model="data.planstunden"
						name="planstunden"
						>
					</form-input>

				</div>
				<div class="row mb-3">
					
					<form-input
						:label="data.default_stundensatz !== null 
							? $p.t('lehre', 'stundensatz') + ' (' + $p.t('lehre', 'default') + ': ' + data.default_stundensatz + ')'
							: $p.t('lehre', 'stundensatz')"
						type="number"
						min="0"
						step="0.01"
						container-class="col-3"
						v-model="data.stundensatz"
						:disabled="data.vertrag?.vertragsstatus_kurzbz === 'akzeptiert'"
						name="stundensatz"
						>
					</form-input>
					
					<form-input
						:label="$p.t('lehre', 'bismelden')"
						type="checkbox"
						container-class="col-3"
						v-model="data.bismelden"
						name="bismelden"
						>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						:label="$p.t('lehre', 'gesamtkosten')"
						type="number"
						name="gesamtkosten"
						container-class="col-3"
						readonly
						v-model="berechneteGesamtkosten" 
						:style="{ color: berechneteGesamtkosten <= 0 ? 'red' : 'black' }"
						>
					</form-input>
					
				</div>
				
			</template>
			
		</fieldset>
	</core-form>
	`
};