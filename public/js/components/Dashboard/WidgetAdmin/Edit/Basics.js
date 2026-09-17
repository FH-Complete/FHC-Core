import FormInput from "../../../Form/Input.js";

export default {
	name: 'WidgetsAdminEditBasics',
	components: {
		FormInput,
	},
	inject: {
		imageSrc: "imageSrc",
	},
	props: {
		original: Object,
		modelValue: Object,
	},
	emits: [
		"update:modelValue",
	],
	computed: {
		img() {
			return this.imageSrc(this.modelValue.setup?.icon);
		},
	},
	template: /* html */`
	<div class="widgets-admin-edit-basics">
		<div class="d-flex gap-3 my-2">
			<div class="col-auto">
				<img
					:src="img"
					class="object-fit-contain"
					style="height: calc(3em + 1.75rem + var(--bs-border-width) * 2 + 2 * var(--bs-body-font-size) * var(--bs-body-line-height)); width: calc(3em + 1.75rem + var(--bs-border-width) * 2 + 2 * var(--bs-body-font-size) * var(--bs-body-line-height))"
				>
			</div>
			<div class="col">
				<div class="d-flex gap-3 mb-2">
					<div class="col">
						<b>{{ $p.t('dashboard/widget_kurzbz') }}: </b>
						{{ modelValue.widget_kurzbz }}
					</div>
					<div v-if="modelValue.widget_id !== undefined" class="col-auto">
						<b>{{ $p.t('dashboard/widget_id') }}: </b>
						{{ modelValue.widget_id }}
					</div>
				</div>
				<div>
					<form-input
						:label="$p.t('global/beschreibung') + ':'"
						type="textarea"
						v-model="modelValue.beschreibung"
					/>
				</div>
			</div>
		</div>
	</div>
	`
}
