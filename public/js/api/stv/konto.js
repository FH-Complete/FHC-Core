export default {
	tabulatorConfig(config, self) {
		config.ajaxURL = 'api/frontend/v1/stv/konto/get';
		config.ajaxParams = () => {
			const params = {
				person_id: self.modelValue.person_id || self.modelValue.map(e => e.person_id),
				only_open: self.filter,
				studiengang_kz: self.studiengang_kz_intern ? self.stg_kz : ''
			};
			return params;
		};
		config.ajaxRequestFunc = (url, config, params) => this.$fhcApi.post(url, params, config);
		config.ajaxResponse = (url, params, response) => response.data;

		return config;
	},
	checkDoubles(form, data) {
		return this.$fhcApi.post(form, 'api/frontend/v1/stv/konto/checkDoubles', data, {
			confirmErrorHandler: error => true
		});
	},
	insert(form, data) {
		return this.$fhcApi.post(form, 'api/frontend/v1/stv/konto/insert', data);
	},
	counter(data) {
		return this.$fhcApi.post('api/frontend/v1/stv/konto/counter', data);
	},
	edit(form, data) {
		return this.$fhcApi.post(form, 'api/frontend/v1/stv/konto/update', data);
	},
	delete(buchungsnr) {
		return this.$fhcApi.post('api/frontend/v1/stv/konto/delete', {buchungsnr});
	},
	getBuchungstypen() {
		return this.$fhcApi.get('api/frontend/v1/stv/konto/getBuchungstypen');
	}
};