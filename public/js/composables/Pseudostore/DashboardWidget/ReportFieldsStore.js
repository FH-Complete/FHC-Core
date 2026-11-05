import ApiReport from '../../../api/factory/report.js';

const reportsRef = Vue.ref({});
const activeRef = Vue.ref(null);
const fields = Vue.computed(() => {
	if (!activeRef.value)
		return [];
	if (!reportsRef.value[activeRef.value])
		return [];
	return reportsRef.value[activeRef.value];
});
const state = Vue.readonly(reportsRef);

export function useReportFieldsStore() {
	const $api = Vue.inject('$api');
	const $fhcAlert = Vue.inject('$fhcAlert');

	async function fetch(statistik_kurzbz) {
		try {
			reportsRef.value[statistik_kurzbz] = (
				await $api.call(ApiReport.fields(statistik_kurzbz))
			).data;
		} catch(error) {
			$fhcAlert.handleSystemError(error);
			return error;
		}
	}

	async function changeReport(statistik_kurzbz) {
		activeRef.value = statistik_kurzbz;
		if (!reportsRef.value[activeRef.value])
			await fetch(statistik_kurzbz);
	}

	return {
		report: state,
		getters: {
			fields,
		},
		actions: {
			fetch,
			changeReport,
		}
	};
}