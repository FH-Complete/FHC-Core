import {CoreFilterCmpt} from "../filter/Filter.js";
import FormForm from '../Form/Form.js';
import FormInput from '../Form/Input.js';

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
			this.$fhcApi.factory.vorlagen.getVorlagen()
				.then(result => {
					this.vorlagen = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		}

		if(this.useLoggedInUserOe){
			this.$fhcApi.factory.vorlagen.getVorlagenByLoggedInUser()
				.then(result => {
					//console.log(this.vorlagenOe);
					this.vorlagenOe = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
template: `
	<div class="core-vorlagen-dropdown">
		<div v-if="isAdmin" class="col-sm-8 pt-3">
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
		
		<div v-if="useLoggedInUserOe" class="col-sm-8 pt-3">
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