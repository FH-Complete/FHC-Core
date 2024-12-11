/**
 * Copyright (C) 2022 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

import CoreForm from '../Form/Form.js';
import FormInput from '../Form/Input.js';
import FormValidation from "../Form/Validation.js";

export default {
	components: {
		CoreForm,
		FormValidation,
		FormInput
	},
	data() {
		return {
			bankName: '',
			bic: '',
			iban: ''
		}
	},
	methods: {
		save() {
			this.$refs.form.factory.bankData.postBankData(this.bankName, this.bic, this.iban)
				.then(result => {
					this.$emit('saved', result.data);
					this.$fhcAlert.alertSuccess(this.$p.t('ui/gespeichert'));
				})
				.catch(error => {
					this.$fhcAlert.handleSystemError(error);
				});
		}
	},
	created() {
		this.$fhcApi.factory.bankData.getBankData()
			.then(result => {
				if (result.data.length > 0)
				{
					this.bankName = result.data[0].name;
					this.bic = result.data[0].bic;
					this.iban = result.data[0].iban;
				}
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div>
		<core-form ref="form" @submit.prevent="save">
			<fieldset class="overflow-hidden">
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="Bank name"
						type="text"
						v-model="bankName"
						name="bankName"
					>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="BIC"
						type="text"
						v-model="bic"
						name="bic"
					>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="IBAN"
						type="text"
						v-model="iban"
						name="iban"
					>
					</form-input>
				</div>
			</fieldset>
			<div class="btn-group flex-grow-0" role="group" aria-label="Save">
				<button type="button" class="btn btn-outline-secondary" @click="save">Save</button>
			</div>
		</core-form>
	</div>`
};

