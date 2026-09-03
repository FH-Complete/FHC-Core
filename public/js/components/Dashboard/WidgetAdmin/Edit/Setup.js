import FormInput from "../../../Form/Input.js";
import EditSize from "./Size.js";

export default {
	name: 'WidgetsAdminEditSetup',
	components: {
		FormInput,
		EditSize,
	},
	props: {
		modelValue: Object,
		editName: Boolean,
		editSize: Boolean,
		editHideFooter: Boolean,
	},
	emits: [
		"update:modelValue"
	],
	template: /* html */`
	<div class="widgets-admin-edit-setup border-bottom mb-3">
		<form-input
			v-model="modelValue.name"
			type="text"
			container-class="mb-3"
			:label="$p.t('ui/name')"
			:disabled="!editName"
		/>
		<label>{{ $p.t('ui/size') }}</label>
		<div class="d-flex gap-3 mb-3">
			<edit-size v-model="modelValue.width" :disabled="!editSize">
				{{ $p.t('ui/width') }}
			</edit-size>
			<edit-size v-model="modelValue.height" :disabled="!editSize">
				{{ $p.t('ui/height') }}
			</edit-size>
		</div>
		<form-input
			v-model="modelValue.hidefooter"
			type="checkbox"
			container-class="form-switch mb-3"
			role="switch"
			:label="$p.t('dashboard/widget_setup_hidefooter')"
			:disabled="!editHideFooter"
		/>
	</div>
	`
}
