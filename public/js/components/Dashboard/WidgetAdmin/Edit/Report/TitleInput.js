import FormInput from '../../../../Form/Input.js';

export default {
	name: "WidgetsGenteratorReportTitleInput",
	components: {
		FormInput,
		PvOverlayPanel: primevue.overlaypanel,
	},
	props: {
		modelValue: {
			type: String,
			default: ''
		},
		label: {
			type: String,
			required: true
		},
		id: {
			type: String,
			required: true
		},
		details: {
			type: Array
		},
	},
	emits: [
		"update:modelValue",
	],
	data() {
		return {
			labelInputId: undefined,
		};
	},
	computed: {
		value: {
			get() {
				return this.modelValue;
			},
			set(v) {
				this.$emit('update:modelValue', v);
			}
		},
		tooltipTable() {
			if (!this.details)
				return null;
			if (!this.details.length)
				return null;
			return this.details.map(detail => {
				const { title, kurzbz } = detail;
				return {
					title,
					kurzbz,
					options: detail.options
						? Object.keys(detail.options.find(Boolean).original)
						: []
				};
			});
		},
	},
	methods: {
		showTooltip(event) {
			this.$refs.tooltip.toggle(event);
		},
	},
	mounted() {
		if (this.$refs?.labelInput?.$refs?.input?.id)
			this.labelInputId = this.$refs.labelInput.$refs.input.id;
	},
	template: /*html*/ `
	<div class="widgets-report-title-input">
		<label :for="labelInputId" class="form-label">{{ label }}</label>
		<div class="input-group">
			<form-input
				ref="labelInput"
				v-model="value"
				type="text"
				:id="id"
				input-group
			/>
			<button type="button" class="input-group-text" @click="showTooltip">
				<i class="fa-solid fa-info-circle" />
			</button>
		</div>
		<pv-overlay-panel ref="tooltip" class="w-50">
			<p>{{ $p.t('dashboard/widget_report_title_tooltip') }}</p>
			<table v-if="tooltipTable" class="table w-100 mb-0">
				<thead>
					<tr>
						<th scope="col">{{ $p.t('dashboard/widget_report_vars') }}</th>
						<th scope="col">{{ $p.t('dashboard/widget_report_title_token') }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="variable in tooltipTable">
						<th scope="row">{{ variable.title }}</th>
						<td>
							<div>{<i>{{ variable.kurzbz }}</i>}</div>
							<div v-for="opt in variable.options">
								{<i>{{ variable.kurzbz }}</i>:<i>{{ opt }}</i>}
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</pv-overlay-panel>
	</div>
	`,
};
