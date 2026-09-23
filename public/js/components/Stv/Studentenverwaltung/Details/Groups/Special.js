import BsModal from "../../../../Bootstrap/Modal.js";
import FhcForm from "../../../../Form/Form.js";
import FormValidation from "../../../../Form/Validation.js";
import FormInput from "../../../../Form/Input.js";

import ApiStvGroups from '../../../../../api/factory/stv/group.js';

export default {
	name: 'TabGroupsSpecial',
	components: {
		BsModal,
		FhcForm,
		FormValidation,
		FormInput,
		PvAutocomplete: primevue.autocomplete
	},
	props: {
		defaultStg: Number
	},
	emits: [
		"chosen"
	],
	data() {
		return {
			value: '',
			groupSuggestions: []
		};
	},
	methods: {
		show() {
			this.$refs.popup.show();
		},
		hide() {
			this.$refs.popup.hide();
		},
		getGroupSuggestions({ query }) {
			this.$api
				.call(ApiStvGroups.search(query, this.defaultStg))
				.then(result => this.groupSuggestions = result.data)
				.catch(this.$fhcAlert.handleSystemError);
		},
		onSubmit(evt) {
			if (!evt.defaultPrevented) {
				evt.preventDefault();
				this.hide();
			}
		},
		onEnter() {
			/**
			 * NOTE(chris): PrimeVue: AutoComplete: Enter does not submit form #5618
			 * @see https://github.com/primefaces/primevue/issues/5618
			 *
			 * this is fixed in 3.52.0
			 * until then this function fill fix it
			 */
			if (!this.$refs.autocomplete.$refs.input.overlayVisible) {
				this.$refs.form.$el.requestSubmit();
			}
		},
		modalOpened() {
			this.$refs.autocomplete.$refs.input.$refs.focusInput.focus();
		},
		modalClosed() {
			this.value = '';
			this.$refs.form.clearValidation();
		}
	},
	template: /* html */`
	<bs-modal
		ref="popup"
		class="stv-details-groups-special"
		@hide-bs-modal="modalClosed"
		@shown-bs-modal="modalOpened"
	>
		<template #title>
			{{ $p.t('gruppenmanagement/add_group') }}
		</template>
		<fhc-form ref="form" @submit="onSubmit">
			<form-validation />
			<div class="input-group">
				<form-input
					ref="autocomplete"
					type="autocomplete"
					name="gruppe_kurzbz"
					v-model="value"
					container-class="flex-grow-1"
					input-class="w-100"
					:suggestions="groupSuggestions"
					:option-label="el => el.gruppe_kurzbz + (el.bezeichnung ? ' (' + el.bezeichnung + ')' : '')"
					@complete="getGroupSuggestions"
					@keydown.enter.capture="onEnter"
				/>
				<button type="submit" class="btn btn-primary">{{ $p.t('ui/hinzufuegen') }}</button>
			</div>
		</fhc-form>
	</bs-modal>`
};