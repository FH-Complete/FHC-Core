import {CoreFilterCmpt} from "../filter/Filter.js";
import FormForm from '../Form/Form.js';
import FormInput from '../Form/Input.js';

import ApiVorlage from '../../api/factory/vorlagen.js';

export default {
	components: {
		FormForm,
		FormInput,
		CoreFilterCmpt
	},
	props: {
		label: {
			type: String,
			required: true
		},
		isAdmin: {
			type: Boolean,
			required: false
		},
		useLoggedInUserOe: {
			type: Boolean,
			required: false
		}
	},
	data() {
		return {
			vorlagen: [],
			selectedValue: null,
			vorlagenOe: []
		}
	},
	methods: {
		updateValue() {
			this.$emit('change', this.selectedValue);
		},
		setValue(value) {
			this.selectedValue = value;
		},
	},
	created() {
		if(this.isAdmin) {
			this.$api
				.call(ApiVorlage.getVorlagen())
				.then(result => {
					this.vorlagen = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		}

		if(this.useLoggedInUserOe){
			this.$api
				.call(ApiVorlage.getVorlagenByLoggedInUser())
				.then(result => {
					//console.log(this.vorlagenOe);
					this.vorlagenOe = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
template: `
	<div class="core-vorlagen-dropdown">
		<div v-if="isAdmin" class="col-sm-12 pt-2">
			<form-input
				ref="dropdown"
				type="select"
				:label="label"
				@change="updateValue"
				v-model="selectedValue"
				>
				<option 
					v-for="vorlage in vorlagen" 
					:key="vorlage.vorlage_kurzbz" 
					:value="vorlage.vorlage_kurzbz" 
					>
					{{vorlage.bezeichnung}}
				</option>
			</form-input>
		</div>
		
		<div v-if="useLoggedInUserOe" class="col-sm-12 pt-2">
			<form-input
				ref="dropdown"
				type="select"
				:label="label"
				@change="updateValue"
				v-model="selectedValue"
				>
				<option 
					v-for="vorlage in vorlagenOe" 
					:key="vorlage.id" 
					:value="vorlage.id" 
					>
					{{vorlage.description}}
				</option>
			</form-input>
		</div>
	</div>
	`,
}