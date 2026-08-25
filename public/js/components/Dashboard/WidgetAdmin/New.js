import BsModal from '../../Bootstrap/Modal.js';
import CoreForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';

import ApiWidget from '../../../api/factory/dashboard/widget.js';

export default {
	name: 'WidgetsAdminNew',
	components: {
		BsModal,
		CoreForm,
		FormInput,
	},
	emit: [
		"create",
	],
	data() {
		return {
			generators: [],
			result: { setup: {} },
		};
	},
	methods: {
		show() {
			this.result = {
				widget_kurzbz: '',
				setup: {
					generator: null,
				},
			};
			this.$refs.form.clearValidation();
			this.$refs.modal.show();
		},
		finish() {
			this.$refs.form
				.call(ApiWidget.create(this.result))
				.then(res => {
					this.$emit('create', { ...this.result, widget_id: res.data });
					this.$refs.modal.hide();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
	},
	created() {
		this.$api
			.call(ApiWidget.generators())
			.then(res => {
				this.generators = res.data
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: /* html */`
	<core-form
		class="widgets-admin-new"
		ref="form"
		@submit.prevent="finish"
	>
		<bs-modal ref="modal">
			<template #default>
				<form-input
					type="select"
					:label="'Generator:'"
					v-model="result.setup.generator"
				>
					<option v-for="(generator, name) in generators" :key="name" :value="generator">
						{{ generator }}
					</option>
				</form-input>
				<form-input
					type="text"
					name="widget_kurzbz"
					:label="$p.t('mobility/kurzbz') + ':'"
					v-model="result.widget_kurzbz"
				/>
			</template>
			<template #footer>
				<button type="submit" class="btn btn-primary">OK</button>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			</template>
		</bs-modal>
	</core-form>
	`
}
