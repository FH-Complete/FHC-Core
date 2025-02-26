export default {
	tabulatorConfig(config, self) {
		config.ajaxURL = 'api/frontend/v1/stv/archiv/get';
		config.ajaxParams = () => {
			const params = {
				person_id: self.modelValue.person_id || self.modelValue.map(e => e.person_id)
			};
			return params;
		};
		config.ajaxRequestFunc = (url, config, params) => this.$fhcApi.post(url, params, config);
		config.ajaxResponse = (url, params, response) => response.data;

		return config;
	},
	getArchivVorlagen() {
		return this.$fhcApi.post('api/frontend/v1/stv/archiv/getArchivVorlagen');
	},
	archive(data) {
		return this.$fhcApi.post(
			'api/frontend/v1/documents/archiveSigned',
			data
		);
	},
	update(data) {
		return this.$fhcApi.post('api/frontend/v1/stv/archiv/update', data);
	},
	delete({akte_id, studiengang_kz}) {
		return this.$fhcApi.post('api/frontend/v1/stv/archiv/delete', {akte_id, studiengang_kz});
	}
};
