
export const FhcChart = {
	name: 'FhcChart',
	props: {
		chartOptions: {
			type: Object,
		}
	},
	template: `
	<div style="width:100%;height:100%;overflow:auto">
		<div role="group" aria-label="Chart Modus">
			<Button :class="(chartOptions.chart.type === 'pie' ? 'active ' : '') + 'btn btn-outline-secondary'" style="width: 48px;" @click="chartOptions.chart.type='pie'"><i class="fa-solid fa-chart-pie"></i></Button>
			<Button :class="(chartOptions.chart.type === 'bar' ? 'active ' : '') + 'btn btn-outline-secondary'" style="width: 48px;" @click="chartOptions.chart.type='bar'"><i class="fa-solid fa-chart-bar"></i></Button>
			<Button :class="(chartOptions.chart.type === 'column' ? 'active ' : '') + 'btn btn-outline-secondary'" style="width: 48px;" @click="chartOptions.chart.type='column'"><i class="fa-solid fa-chart-simple"></i></Button>
			<Button :class="(chartOptions.chart.type === 'line' ? 'active ' : '') + 'btn btn-outline-secondary'" style="width: 48px;" @click="chartOptions.chart.type='line'"><i class="fa-solid fa-chart-line"></i></Button>
		</div>
		<figure>
			<highcharts class="chart" :options="chartOptions"></highcharts>
		</figure>
	</div>
`
};

export default FhcChart