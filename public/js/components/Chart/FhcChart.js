
var _uuid = 0;
export const FhcChart = {
	name: 'FhcChart',
	components: {
		
	},
	emits: [
		'chartCreated',
		'chartDeleted',
		'chartUpdated'
	],
	data: function() {
		return {
			id: this.chart_id + _uuid
		};
	},
	props: {
		chart_id: {
			type: [Number, String]
		},
		preferences: {
			type: Object,
			default: {}
		},
		publish: {
			type: Boolean,
			default: false
		},
		statistik_kurzbz: {
			type: String,
			default: ''
		},
		fhcType: { // TODO: ist das notwendig?
			type: String,
			default: 'hcnorm',
			required: true,
			validator(val) {
				return ['hcnorm', 'hcgroupedstacked', 'hcdrill'].includes(val)
			}
		},
		type: {
			type: String,
			default: 'line',
			required: true,
			validator(val) {
				// actually used in tbl_rp_chart: 'column', 'line' & 'bubble'
				return ['column', 'bubble', 'line', 'pie', 'bar'].includes(val)
				
				// laut rp_chart.class.php
				// return ["column", "line", "spline", "pie",
				// 	"area", "bar", "bubble", "columnrange",
				// 	"errorbar", "funnel", "gauge", "polygon",
				// 	"pyramid", "scatter", "solidgauge", "treemap",
				// 	"waterfall" , "arearange", "areaspline", "areasplinerange", "boxplot"].includes(val)
				// 90% probably never used
			}
		},
		title: { // https://www.highcharts.com/docs/chart-concepts/title-and-subtitle
			type: String,
			default: ''
		},
		longtitle: {
			type: String,
			default: ''	
		},
		description: {
			type: String,
			default: ''
		},
		series: {
			type: Array,
			required: true,
			default: []
		},
		xAxis: { // https://www.highcharts.com/docs/chart-concepts/axes
			type: Object,
			required: true,
			validator(val) {
				return Array.isArray(val.categories) // https://api.highcharts.com/highcharts/xAxis.categories
					&& val.categories.every(c => typeof c === 'string')
					&& Array.isArray(val.title)
					&& Array.isArray(val.labels)
			}
		},
		yAxis: {
				
		},
		legend:{
				
		},
		tooltip: { // https://www.highcharts.com/docs/chart-concepts/tooltip
			backgroundColor: '#FCFFC5',
			borderColor: 'black',
			borderRadius: 10,
			borderWidth: 3
		},
		plotOptions: {
			
		}
	},
	methods: {
		recreateChart() {
			
		},
		deleteChart(id) {
			
		},
		createNewChart() {
			
		},
		setupGraph() {
			const wrapperDiv = document.getElementById(this.id)
			const containerCategory = document.createElement('div')
			containerCategory.id = this.id
			containerCategory.style.flex = '1 0 300px';
			containerCategory.style.margin = '10px';
			containerCategory.style.maxWidth = '500px';
			wrapperDiv.appendChild(containerCategory)

			Highcharts.chart(this.id, {
				chart: {
					type: this.type
				},
				title: this.title,
				subtitle: this.longtitle,
				tooltip: this.tooltip,
				plotOptions: this.plotOptions,
				xAxis: this.xAxis,
				yAxis: this.yAxis,
				series: this.series
			})
		}
	},
	created(){
	},
	mounted() {
		this.setupGraph()
	},
	template: `
		<figure>
			<div :id="id" style="display: flex; flex-wrap: wrap; align-content: flex-start; justify-content: center;">

			</div>
		</figure>
`
};

export default FhcChart