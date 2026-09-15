import CoreForm from "../../Form/Form.js";
import EditBasics from "./Edit/Basics.js";
import EditSetup from "./Edit/Setup.js";

import ApiWidget from "../../../api/factory/dashboard/widget.js";

export default {
	name: 'WidgetsAdminEdit',
	components: {
		CoreForm,
		EditBasics,
		EditSetup,
	},
	props: {
		originalData: Object,
		modelValue: Object,
		unsavedProgress: Boolean,
	},
	emits: [
		"saved",
		"remove",
		"update:modelValue",
	],
	data() {
		return {
			generator: null,
			saving: false,
		};
	},
	computed: {
		value: {
			get() {
				return this.modelValue;
			},
			set(modelValue) {
				this.$emit('update:modelValue', modelValue);
			},
		},
		setup: {
			get() {
				return this.modelValue.setup;
			},
			set(setup) {
				this.$emit('update:modelValue', { ...this.modelValue, setup });
			},
		},
	},
	watch: {
		'originalData.setup.generator': {
			async handler(gen) {
				if (!gen)
					return this.generator = null;

				gen = gen.split(':').reverse();
				let file = '../../DashboardWidget/Generators/' + gen[0] + '.js';
				if (gen.length > 1)
					file = '../../../../extensions/' + gen[1] + '/js/components' + file.substr(5);

				this.generator = Vue.markRaw((await import(file)).default);
			},
			immediate: true,
		}
	},
	methods: {
		save() {
			this.saving = true;
			this.$refs.form.clearValidation();
			const data = JSON.parse(JSON.stringify(this.modelValue));
			this.$refs.form
				.call(ApiWidget.update(data))
				.then(() => {
					this.$emit('update:modelValue', data);
					this.$emit('saved');
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => this.saving = false);
		},
		remove() {
			this.$fhcAlert.confirmDelete()
				.then(result => {
					if (!result)
						return Promise.reject({ handled: true });
					
					this.saving = true;
					return this.$api
						.call(ApiWidget.remove(this.modelValue.widget_id));
				})
				.then(() => {
					this.$emit('remove', this.modelValue);
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => this.saving = false);
		},
	},
	template: /* html */`
	<core-form
		v-if="modelValue"
		ref="form"
		class="widgets-admin-edit mx-2 pb-5"
		@submit.prevent="save"
	>
		<edit-basics v-model="value" :original="originalData" class="border-bottom mb-3" />
		<template v-if="generator">
			<component
				:is="generator"
				v-model="value"
				:original="originalData"
				:key="modelValue.widget_id"
			/>
		</template>
		<template v-else>
			<edit-setup v-model="setup" />
		</template>
		<div class="position-absolute mx-2 my-1 bottom-0 end-0 z-3">
			<button
				v-if="generator"
				type="button"
				:disabled="saving"
				class="btn btn-danger me-2"
				@click="remove"
			>{{ $p.t('ui/loeschen') }}</button>
			<button
				type="submit"
				:disabled="saving || !unsavedProgress"
				class="btn btn-primary"
			>{{ $p.t('ui/speichern') }}</button>
		</div>
	</core-form>
	`
}
