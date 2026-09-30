import CoreForm from '../Form/Form.js';
import FormInput from '../Form/Input.js';
import ApiDropdowns from '../../api/dropdowns/dropdown.js';

export default {
	name: "NationsDropdown",
	components: {
		CoreForm,
		FormInput
	},
	inject: {
		lists: {
			from: 'lists'
		},
	},
	props: {
		nation: {
			type: String,
			required: true
		},
		containerClass: {
			type: String,
			required: false
		},
		labelDropdown: {
			type: String,
			required: false
		},
		nullLabel: {
			type: String,
			required: false,
			default: ''
		},
	},
	data(){
		return {
			filteredNations: [],
			selectedNation: null,
			abortController: {
				nations: null
			},
		};
	},
	watch: {
		nation: {
			immediate: true,
			handler(newVal) {
				if (newVal) {
					this.updateNation(newVal);
				}
			}
		},
		selectedNation(newVal) {
			//emit
			this.$emit('updateNation', {
				nation_code: newVal?.nation_code
			});
		}
	},
	methods: {
		filterNations(event){
			//use search function instead of filteringList to get also params of engltext and nation_code
			if (this.abortController.nations) {
				this.abortController.nations.abort();
			}
			this.abortController.nations = new AbortController();

			return this.$api
				.call(ApiDropdowns.getNations(event.query))
				.then(result => {
					this.filteredNations = result.data.retval;
				});
		},
		updateNation(nation) {
			const detailsNation = this.lists.nations.find(
				item => item.nation_code == nation
			);

			//create new object to avoid erros because of same instance if included more than once
			this.selectedNation = detailsNation
				? { ...detailsNation }
				: { nation_code: nation, label: this.nullLabel };
		},
	},
	template: `
	<div class="stv-nations-dropdown">
		<form-input
			:container-class="containerClass"
			:label="labelDropdown"
			type="autocomplete"
			v-model="selectedNation"
			forceSelection
			optionLabel="label"
			:suggestions="filteredNations"
			dropdown
			@complete="filterNations"
			>
				<template #option="slotProps">
					<div
						:class="slotProps.option.sperre
						? 'item-sperre'
						: ''"
						>
							{{slotProps.option.label}}
					</div>
				</template>
		</form-input>
	</div>	`
};
