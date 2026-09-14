import FormInput from '../../../../Form/Input.js';
import ReportAggregator from './Aggregators/Aggregator.js';

let counter = 0;

export default {
	name: "WidgetsGenteratorReportAggregators",
	components: {
		FormInput,
		ReportAggregator,
	},
	provide() {
		return {
			defaultAggregatorIndex: Vue.computed({
				get: () => this.defaultAggregatorIndex,
				set: v => {
					const curr = this.modelValue.find(agg => agg.default);
					if (curr)
						delete curr.default;
					this.modelValue[v].default = true;
				},
			}),
		};
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
		defaultAggregatorIndex() {
			return this.modelValue.findIndex(agg => agg.default);
		}
	},
	methods: {
		addAggregator() {
			this.modelValue.push({});
		},
		removeAggregator(index) {
			this.$emit('update:modelValue', this.modelValue.toSpliced(index, 1));
		},
		preventOnCheckboxClick(evt) {
			if (evt.explicitOriginalTarget.classList.contains('form-check-label')) {
				evt.preventDefault();
			}
		},
		getHeaderStyle(isDefault) {
			if (!isDefault)
				return undefined;
			return {
				'--bs-accordion-btn-bg': 'var(--bs-success-bg-subtle)',
				'--bs-accordion-btn-color': 'var(--bs-success-text-emphasis)',
				'--bs-accordion-active-bg': 'var(--bs-success)',
				'--bs-accordion-active-color': '#fff',
			};
		},
	},
	created() {
		this.myId = counter++;
	},
	template: /*html*/ `
	<div class="widgets-report-config-aggregators">
		<label class="form-label">{{ $p.t('dashboard/widget_report_kpi_aggregators') }}</label>
		<div
			class="accordion mb-2"
			@[\`show.bs.collapse\`]="preventOnCheckboxClick"
			@[\`hide.bs.collapse\`]="preventOnCheckboxClick"
		>
			<div
				v-for="(agg, i) in modelValue"
				:key="i"
				class="accordion-item"
			>
				<h3
					class="accordion-header"
					:style="getHeaderStyle(defaultAggregatorIndex == i)"
				>
					<button
						type="button"
						class="accordion-button collapsed"
						data-bs-toggle="collapse"
						:data-bs-target="'#aggregator_' + myId + '_' + i"
						aria-expanded="false"
						:aria-controls="'aggregator_' + myId + '_' + i"
					>
						<span v-if="agg.label">{{ agg.label }}</span>
						<i v-else>{{ $p.t('ui/neu') }}</i>
					</button>
				</h3>
				<div :id="'aggregator_' + myId + '_' + i" class="accordion-collapse collapse">
					<report-aggregator v-model="agg" class="accordion-body" :index="i" @remove="removeAggregator" />
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
