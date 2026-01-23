import Phrasen from './BasePhrasen.js';
import Alert from './BaseAlert.js';
import { BaseApi } from './BaseApi.js';

export default {

	install(app, options = {}) {
		// init in order
		const $p = Phrasen.init(app);
		const $fhcAlert = Alert.init(app, $p);
		
		// try to reuse existing CoreRESTClient api instance if one has been active since before
		// fhcBase Plugin install
		let $api = app.config.globalProperties.$api;
		if (!($api instanceof BaseApi)) {
			$api = new BaseApi({ $fhcAlert, $p }, options);
		} else {
			// If api existed pre-app install
			$api.setDependencies({ $fhcAlert, $p });
		}

		// set ready promise for awaiting async functions
		$p.setDeps({ $api, $fhcAlert });
		$fhcAlert.setDeps({ $api });

		// globalProperties Binding & provide
		app.config.globalProperties.$p = $p;
		app.config.globalProperties.$fhcAlert = $fhcAlert;
		app.config.globalProperties.$api = $api;
		app.config.globalProperties.$fhcApi = $api;
		app.provide('$api', $api);
		app.provide('$fhcApi', $api);
		app.provide('$p', $p);
		app.provide('$fhcAlert', $fhcAlert);
	}

};