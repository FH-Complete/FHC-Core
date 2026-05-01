
export const FhcChart = {
	name: 'FhcChart',
	props: {
		chartOptions: {
			type: Object,
		}
	},
	template: `
	<div style="width:100%;height:100%;overflow:auto">
		<figure>
			<highcharts class="chart" :options="chartOptions"></highcharts>
		</figure>
	</div>
`
};

export default FhcChart