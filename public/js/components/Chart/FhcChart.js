
export const FhcChart = {
	name: 'FhcChart',
	props: {
		chartOptions: {
			type: Object,
		}
	},
	methods: {
		reflow() {
			if (this.$refs.chart?.chart)
				this.$refs.chart.chart.reflow();
		},
	},
	template: /* html */`
	<div style="width:100%;height:100%;overflow:auto">
		<figure class="h-100 m-0">
			<highcharts ref="chart" class="chart h-100" :options="chartOptions"></highcharts>
		</figure>
	</div>
`
};

export default FhcChart