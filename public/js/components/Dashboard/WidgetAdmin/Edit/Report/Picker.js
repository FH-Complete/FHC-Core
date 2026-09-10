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
	},
	emits: [
		"update:modelValue",
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

				if (res)
					return this.selectedValue = res;
				
				this.selectedValue = this.modelValue;
			} else {
				this.selectedValue = '';
			}
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
			}
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
	<div class="widgets-report-config-picker">
		<form-input
			type="autocomplete"
			:label="$p.t('dashboard/widget_report_statistik')"
			v-model="selectedValue"
			:suggestions="filteredItems"
			field="statistik_kurzbz"
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
		<div v-if="selectedValue?.statistik_kurzbz" class="text-muted">
			{{ selectedValue.bezeichnung || selectedValue.statistik_kurzbz }}
		</div>
	</div>
	`,
};
