export default {
	getArchiv(person_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/archiv/getArchiv',
			params: { person_id }
		};
	},
	getArchivVorlagen() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/archiv/getArchivVorlagen'
		};
	},
	archive(data) {
		return {
			method: 'post',
			url: 'api/frontend/v1/documents/archive',
			params: data
		};
	},
	archiveSigned(data) {
		return {
			method: 'post',
			url: 'api/frontend/v1/documents/archiveSigned',
			params: data
		};
	},
	update(data) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/archiv/update',
			params: data
		};
	},
	delete(akte_id, studiengang_kz) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/archiv/delete',
			params: {akte_id, studiengang_kz}
		};
	}
};
