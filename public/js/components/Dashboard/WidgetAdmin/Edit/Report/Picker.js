import FormInput from '../../../../Form/Input.js';
import PickerOption from './Picker/Option.js';

import ApiReport from '../../../../../api/factory/report.js';

export default {
	name: "WidgetsGenteratorReportPicker",
	components: {
		FormInput,
		PickerOption,
	},
	props: {
		modelValue: {
			type: String,
			required: true
		},
		details: {
			type: Object,
			default: {}
		},
	},
	emits: [
		"update:modelValue",
		"update:details",
	],
	data() {
		return {
			allItems: null,
			filteredItems: [],
			selectedValue: '',
		};
	},
	methods: {
		initSelectedValue() {
			if (this.modelValue) {
				const res = this.allItems.find(item => item.statistik_kurzbz == this.modelValue);
				if (res) {
					this.selectedValue = res;
					this.loadDetails(this.modelValue);
					return;
				}
				this.selectedValue = this.modelValue;
			} else {
				this.selectedValue = '';
			}
			this.$emit('update:details', null);
		},
		searchItems(event) {
			if (this.allItems) {
				if (!event.query) {
					this.filteredItems = this.allItems;
				} else {
					const regex = new RegExp(event.query, 'i');
					this.filteredItems = this.allItems.filter(t => regex.test(t.bezeichnung) || regex.test(t.statistik_kurzbz) || regex.test(t.gruppe));
				}
			}
		},
		updateSelectedItem(v) {
			if (v?.statistik_kurzbz) {
				this.$emit('update:modelValue', v.statistik_kurzbz);
				this.loadDetails(v.statistik_kurzbz);
			}
		},
		loadDetails(statistik_kurzbz) {
			this.$api
				.call(ApiReport.vars(statistik_kurzbz))
				.then(result => {
					this.$emit('update:details', result.data);
				})
				.catch(this.$fhcAlert.handleSystemErrors)
		},
	},
	created() {
		this.$api
			.call(ApiReport.list())
			.then(result => {
				this.allItems = result.data;
				this.initSelectedValue();
			})
			.catch(this.$fhcAlert.handleSystemErrors);
	},
	template: /*html*/ `
	<form-input
		type="autocomplete"
		:label="$p.t('dashboard/widget_report_statistik')"
		v-model="selectedValue"
		class="widgets-report-config-picker"
		:suggestions="filteredItems"
		field="bezeichnung"
		dropdown-mode="blank"
		dropdown
		force-selection
		@complete="searchItems"
		@update:modelValue="updateSelectedItem"
	>
		<template #option="{ option }">
			<picker-option :option="option" />
		</template>
	</form-input>
	`,
};
