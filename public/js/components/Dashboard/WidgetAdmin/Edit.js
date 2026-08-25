import CoreForm from "../../Form/Form.js";
import EditBasics from "./Edit/Basics.js";

import ApiWidget from "../../../api/factory/dashboard/widget.js";

export default {
	name: 'WidgetsAdminEdit',
	components: {
		CoreForm,
		EditBasics,
	},
	props: {
		originalData: Object,
		modelValue: Object,
		unsavedProgress: Boolean,
	},
	emits: [
		"saved",
		"update:modelValue",
	],
	data() {
		return {
			generator: null,
			saving: false,
		};
	},
	watch: {
		'originalData.setup.generator': {
			async handler(gen) {
				if (!gen)
					return this.generator = null;

				// TODO(chris): extensions
				let file = '../../DashboardWidget/Generators/' + gen + '.js';

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
	},
	template: /* html */`
	<core-form
		v-if="modelValue"
		ref="form"
		class="widgets-admin-edit mx-2"
		@submit.prevent="save"
	>
		<edit-basics v-model="modelValue" :original="originalData" />
		<template v-if="generator">
			<component :is="generator" v-model="modelValue" :original="originalData" :key="modelValue.widget_id" />
		</template>
		<div class="position-absolute bottom-0 end-0 z-1">
			<button
				type="submit"
				:disabled="saving || !unsavedProgress"
				class="btn btn-primary"
			>{{ $p.t('ui/speichern') }}</button>
		</div>
	</core-form>
	`
}
