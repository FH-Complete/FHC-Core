import FormInput from '../../../../Form/Input.js';
import ReportAggregator from './Aggregators/Aggregator.js';

let counter = 0;

export default {
	name: "WidgetsGenteratorReportAggregators",
	components: {
		FormInput,
		ReportAggregator,
	},
	props: {
		modelValue: {
			type: Object,
			required: true
		},
	},
	emits: [
		"update:modelValue",
	],
	data() {
		return {
			myId: 0,
		};
	},
	computed: {
		aggregatorDefault: {
			get() {
				return this.modelValue.findIndex(agg => agg.default);
			},
			set(v) {
				let curr = this.modelValue.find(agg => agg.default);
				delete curr.default;
				this.modelValue[v].default = true;
			},
		},
	},
	methods: {
		addAggregator() {
			this.modelValue.push({});
		},
		preventOnCheckboxClick(evt) {
			if (evt.explicitOriginalTarget.classList.contains('form-check-label')) {
				evt.preventDefault();
			}
		},
	},
	created() {
		this.myId = counter++;
	},
	template: /*html*/ `
	<div class="widgets-report-config-aggregators">
		<label>{{ $p.t('dashboard/widget_report_kpi_aggregators') }}</label>
		<div
			class="accordion"
			@[\`show.bs.collapse\`]="preventOnCheckboxClick"
			@[\`hide.bs.collapse\`]="preventOnCheckboxClick"
		>
			<div
				v-for="(agg, i) in modelValue"
				:key="i"
				class="accordion-item"
			>
				<h3 class="accordion-header">
					<button
						type="button"
						class="accordion-button collapsed"
						data-bs-toggle="collapse"
						:data-bs-target="'#aggregator_' + myId + '_' + i"
						aria-expanded="false"
						:aria-controls="'aggregator_' + myId + '_' + i"
					>
						<span v-if="agg.label">{{ agg.label }}</span>
						<i v-else>new</i>
						<div class="flex-grow-1">
							<form-input
								type="radio"
								name="aggregatordefault"
								v-model="aggregatorDefault"
								class="btn-check"
								:label="$p.t('ui/defaultoption')"
								:value="i"
								label-class="btn"
								container-class="text-end pe-4"
							/>
						</div>
					</button>
				</h3>
				<div :id="'aggregator_' + myId + '_' + i" class="accordion-collapse collapse">
					<report-aggregator v-model="agg" class="accordion-body" />
				</div>
			</div>
		</div>
		<div>
			<button type="button" class="btn btn-primary" @click="addAggregator">
				<i class="fa fa-plus" />
				{{ $p.t('dashboard/widget_report_kpi_aggregator') }}
			</button>
		</div>
	</div>
	`,
};
