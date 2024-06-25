import { CoreFetchCmpt } from '../Fetch.js';
import FormInput from '../Form/Input.js';


export default {
	components: {
		CoreFetchCmpt,
		FormInput
	},
	emits: [
		'update:modelValue',
		'load'
	],
	props: {
		// CodeIgniter model (eg: crm/prestudent)
		ciModel: {
			type: String,
			required: true
		},
		// Primarykey(s) of the record (eg: {prestudent_id: 12345})
		pk: {
			type: Object,
			required: true
		},
		// The values as associative array
		modelValue: Object,
		// Show only fields with a name that exists in the filter
		filter: [String, Array]
	},
	data() {
		return {
			fields: [],
			backupModelValue: {}
		};
	},
	computed: {
		filterArray() {
			if (!this.filter || Array.isArray(this.filter))
				return this.filter;
			return [this.filter];
		},
		filteredFields() {
			if (!this.filterArray)
				return this.fields;
			return this.fields.filter(el => this.filterArray.includes(el.name));
		},
		filteredValues() {
			return this.filteredFields.reduce((r,e) => (r[e.name] = this.internModelValue[e.name], r), {});
		},
		internModelValue: {
			get() {
				return this.modelValue || this.backupModelValue;
			},
			set(value) {
				this.backupModelValue = value;
				this.$emit('update:modelValue', value);
				this.originalValues
			}
		}
	},
	watch: {
		pk(n, o) {
			if (!this.$refs.fetch)
				return; // NOTE(chris): no initial load yet
			
			if (Object.keys(o).length == Object.keys(n).length
				&& Object.keys(o).every(key => n.hasOwnProperty(key) && o[key] === n[key]))
				return; // NOTE(chris): old and new are the same

			this.$nextTick(this.$refs.fetch.fetchData);
		}
	},
	methods: {
		loadF(params) {
			// TODO(chris): move to fhcapi.factory
			return this.$fhcApi
				.post('/api/frontend/v1/udf/load/' + params.ciModel, params.pk);
		},
		init(result) {
			const fields = result.map(el => {
				switch (el.type) {
				case 'textfield':
					el.type = 'text';
					break;
				case 'date':
					el.type = 'Datepicker';
					el.clearable = el.hasOwnProperty('clearable') ? el.clearable : false
					el.autoApply = el.hasOwnProperty('autoApply') ? el.autoApply : true
					el.enableTimePicker = el.hasOwnProperty('enableTimePicker') ? el.enableTimePicker : false
					el.format = el.hasOwnProperty('format') ? el.format : "dd.MM.yyyy"
					el.previewFormat = el.hasOwnProperty('previewFormat') ? el.previewFormat : "dd.MM.yyyy"
					el.teleport = el.hasOwnProperty('teleport') ? el.teleport : true
					break;
				case 'multipledropdown':
					el.multiple = true;
				case 'dropdown':
					el.type = 'select';
					el.options = el.options.map(item => {
						if (Array.isArray(item))
							return {
								id: item[0],
								description: item[1]
							};
							if (typeof item === 'object')
								return item;
							return {
								id: item,
								description: item
							};
						});
					break;
				}
				return el;
			});
			const values = fields.reduce((a,c) => {
				a[c.name] = c.value;
				return a;
			}, {});

			this.internModelValue = {...this.internModelValue, ...values};
			this.fields = fields;
			this.$emit('load', values);
		}
	},
	template: `
	<div class="core-udf row">
		<core-fetch-cmpt
			ref="fetch"
			:api-function="loadF"
			:api-function-parameters="{ ciModel, pk }"
			@data-fetched="init"
			>
			<template #default>
				<div v-for="field in filteredFields" :key="field.name" class="col" :class="field.type == 'checkbox' ? 'pt-4 d-flex align-items-center' : ''">
					<form-input
						v-model="internModelValue[field.name]"
						:name="field.name"
						:label="field.title"
						:type="field.type"
						:multiple="field.multiple"
						:title="field.description"
						:disabled="field.disabled"
						:clearable="field.clearable"
						:auto-apply="field.autoApply"
						:enable-time-picker="field.enableTimePicker"
						:format="field.format"
						:preview-format="field.previewFormat"
						:teleport="field.teleport"
						>
						<option v-for="value in field.options" :key="value.id" :value="value.id">{{value.description}}</option>
					</form-input>
				</div>
			</template>
		</core-fetch-cmpt>
	</div>`
}