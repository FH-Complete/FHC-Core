export default {
	name: "WidgetsReportKpiSetup",
	components:{
	},
	inject: {
		adminMode: {
			from: 'adminMode',
			default: false
		}
	},
	props: {
		config: {
			type: Object,
			required: true
		}
	},
	data() {
		return {
			report_details: null
		};
	},
	computed: {
		statistik_kurzbz: {
			get() {
				return this.config.statistik_kurzbz || "";
			},
			set(v) {
				this.config.statistik_kurzbz = v;
			}
		},
		variables() {
			if (!this.report_details)
				return '';
			return Array.from(new Set(this.report_details.sql.match(/\$\w+/g) || [])).map(i => i.substr(1));
		}
	},
	methods: {
	},
	mounted() {
	},
	template: /*html*/ `
	<div class="widgets-report-kpi-config-kpi">
		<div>
			<report-picker v-model="statistik_kurzbz" v-model:details="report_details" />
		</div>
		<div>
			Config KPI
		</div>
		<div>
			{{ variables }}
		</div>
	</div>
	`,
};
