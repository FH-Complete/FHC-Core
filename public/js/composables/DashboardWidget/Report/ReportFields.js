import { useReportFieldsStore } from '../../Pseudostore/DashboardWidget/ReportFieldsStore.js';

export function useReportFields(statistikKurzbz) {
	const { getters: { fields }, actions: { changeReport } } = useReportFieldsStore();

	Vue.watchEffect(() => {
		changeReport(statistikKurzbz.value);
	});

	return {
		fields,
	};
}