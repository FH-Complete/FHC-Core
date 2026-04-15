import ApiWidget from "../../api/factory/dashboard/widget.js";

const promises = Vue.ref([]);
const stateRef = Vue.ref([]);
const state = Vue.readonly(stateRef);

export function useCachedWidgetLoader() {
	const $api = Vue.inject('$api');
	const $fhcAlert = Vue.inject('$fhcAlert');

	function load(id) {
		if (state.value[id])
			return Promise.resolve(state.value[id]);

		if (!promises.value[id])
			promises.value[id] = new Promise((resolve, reject) => {
				$api
					.call(ApiWidget.get(id))
					.then(res => {
						stateRef.value[id] = res.data;
						promises.value[id] = undefined;
						resolve(state.value[id]);
					})
					.catch($fhcAlert.handleSystemError);
			});

		return promises.value[id];
	}

	return {
		state,
		actions: {
			load
		}
	};
}