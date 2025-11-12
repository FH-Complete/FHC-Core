/**
 * Copyright (C) 2025 fhcomplete.org
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

import BsModal from "../../Bootstrap/Modal.js";
import FhcForm from "../../Form/Form.js";
import FormInput from "../../Form/Input.js";

import ApiStvConfig from '../../../api/factory/stv/config.js';


export default {
	name: 'StvConfig',
	components: {
		BsModal,
		FhcForm,
		FormInput
	},
	emits: [
		'update:modelValue'
	],
	props: {
		modelValue: Object
	},
	data() {
		return {
			setup: {},
			tempValues: {}
		};
	},
	methods: {
		update() {
			this.$refs.form
				.call(ApiStvConfig.set(this.tempValues))
				.then(() => {
					// TODO(chris): phrase
					this.$emit('update:modelValue', { ...this.tempValues });
					this.$refs.modal.hide();
					this.$fhcAlert.alertSuccess('config updated');
				})
				.catch(this.$fhcAlert.handleSystemErrors);
		}
	},
	created() {
		this.$api
			.call(ApiStvConfig.get())
			.then(res => {
				this.setup = {};
				Object.keys(res.data).forEach(key => {
					const binding = { ...res.data[key] };
					delete binding.value;
					delete binding.options;
					const options = res.data[key].options;
					this.tempValues[key] = res.data[key].value;
					this.setup[key] = {
						binding,
						options
					};
				});
				this.$emit('update:modelValue', { ...this.tempValues });
			})
			.catch(this.$fhcAlert.handleSystemErrors);
	},
	template: /* html */`
	<fhc-form class="stv-config" ref="form" @submit.prevent="update">
		<bs-modal
			ref="modal"
			class="fade"
			id="configModal"
			dialog-class="modal-lg"
			@hidden-bs-modal="tempValues = { ...modelValue }"
		>
			<template #title>{{ $p.t('ui/settings') }}</template>
			<template #default>
				<div class="d-flex flex-column gap-5">
					<form-input
						v-for="(value, key) in setup"
						v-model="tempValues[key]"
						v-bind="value.binding"
					>
						<option
							v-for="(label, val) in value.options"
							:key="val"
							:value="val"
						>{{ label }}</option>
					</form-input>
				</div>
			</template>
			<template #footer>
				<button class="btn btn-primary" type="submit">
					{{ $p.t('ui/speichern') }}
				</button>
			</template>
		</bs-modal>
	</fhc-form>`
};
