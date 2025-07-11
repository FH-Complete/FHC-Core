import CoreForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';
import GruppenTable from '../Details/Gruppen.js';
import GruppenDirektTable from '../Details/Direktinskription.js';
import DetailsForm from '../Details/Form.js'
import ApiLehreinheit from "../../../api/lehrveranstaltung/lehreinheit.js";

export default {
	name: "LVTabDetails",
	components: {
		CoreForm,
		FormInput,
		GruppenTable,
		GruppenDirektTable,
		DetailsForm
	},
	props: {
		modelValue: Object,
		config: {
			type: Object,
			default: {}
		},
	},
	inject: {
		dropdowns: {
			from: 'dropdowns'
		}
	},
	watch: {
		modelValue(newValue)
		{
			this.updateLE(newValue)
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

				for (let key of keys) {
					if (this.original[key] !== newValue[key])
					{
						changed[key] = newValue[key];
					}
				}
				this.changed = changed;
			},
			deep: true
		}
	},
	data() {
		return {
			original: null,
			data: null,
			changed: {}
		}
	},
	computed: {
		changedLength() {
			return Object.keys(this.changed).length;
		}
	},

	methods: {
		updateLE(le) {
			if (le?.lehreinheit_id === undefined)
			{
				this.data = null;
				return;
			}
			return this.$api.call(ApiLehreinheit.get(le.lehreinheit_id))
				.then(result => {
					this.data = result.data;
					this.original = {...(this.original || {}), ...this.data};
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		save() {
			if (!this.changedLength)
				return;
			this.$refs.form.clearValidation();

			let updatedData = {
				lehreinheit_id: this.modelValue.lehreinheit_id,
				formData: this.changed
			}
			return this.$refs.form.call(ApiLehreinheit.update(updatedData))
				.then(result => {
					this.original = {...this.data};
					this.changed = {};
					this.$refs.form.setFeedback(true, result.data);
				})
				.catch(this.$fhcAlert.handleSystemError);

		},
		reload(){
			this.updateLE(this.modelValue);
		}
	},
	created() {
		this.updateLE(this.modelValue);
	},
	template: `
	<core-form ref="form" @submit.prevent="save">
		<div class="position-sticky top-0 z-1">
			<button type="submit" class="btn btn-primary position-absolute top-0 end-0" :disabled="!changedLength">{{$p.t('ui', 'speichern')}}</button>
		</div>
		<fieldset class="overflow-hidden">
			<legend>{{this.$p.t('lehre', 'lehreinheit')}}</legend>
			<template v-if="data">
				<details-form :data="data"/>
			</template>
		</fieldset>	
	</core-form>
	<fieldset class="overflow-hidden">
		<div class="row">
			<div class="col-6">
				<legend>{{this.$p.t('lehre', 'gruppen')}}</legend>
				<gruppen-table ref="gruppen_table" :lehreinheit_id="modelValue.lehreinheit_id"></gruppen-table>
			</div>
			<div class="col-6">
				<legend>{{this.$p.t('lehre', 'assignedPersons')}}</legend>
				<gruppen-direkt-table ref="gruppen_direkt_table" :lehreinheit_id="modelValue.lehreinheit_id"></gruppen-direkt-table>
			</div>
		</div>
		
	</fieldset>`
};