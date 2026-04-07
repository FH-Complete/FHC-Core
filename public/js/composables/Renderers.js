import ApiRenderers from '../api/factory/renderers.js';

/**
 * @return object						{ renderers: Object }
 */
export function useRenderers() {
	/* Result Vars */
	const renderers = Vue.ref(null);

	/* Helper Vars */
	const $api = Vue.inject('$api');
	const $fhcAlert = Vue.inject('$fhcAlert');

	/* Main Logic */
	$api
		.call(ApiRenderers.loadRenderers())
		.then(res => {
			const head = document.head;
			for (const rendertype of Object.keys(res.data)) {
				const renderersForType = {};
				for (const name of Object.keys(res.data[rendertype])) {
					const rendererUrl = res.data[rendertype][name];
					if (rendererUrl.substr(-4) == ".css") {
						// add to head
						if (!head.querySelector(`link[href="${rendererUrl}"]`)) {
							var link = document.createElement("link");
							link.type = "text/css";
							link.rel = "stylesheet";
							link.href = rendererUrl;
							head.appendChild(link);
						}
					} else {
						renderersForType[name] = Vue.markRaw(
							Vue.defineAsyncComponent(() => import(rendererUrl))
						);
					}
				}
				if (Object.keys(renderersForType).length) {
					if (renderers.value === null)
						renderers.value = {};
					renderers.value[rendertype] = renderersForType;
				}
			}
		})
		.catch($fhcAlert.handleSystemErrors);

	return {
		renderers
	};
}