export default {
	getStudentProjektarbeiten(uid) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Abgabe/getStudentProjektarbeiten',
			params: { uid }
		};
	},
	getStudentProjektabgaben(detail) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Abgabe/getStudentProjektabgaben',
			params: { projektarbeit_id: detail.projektarbeit_id, student_uid: detail.student_uid }
		};
	},
	postStudentProjektarbeitEndupload(formData) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Abgabe/postStudentProjektarbeitEndupload',
			params: { formData },
			config: {Headers: { "Content-Type": "multipart/form-data" }}
		};
		
		// const url = '/api/frontend/v1/Lehre/postStudentProjektarbeitEndupload';
		// const headers = {Headers: { "Content-Type": "multipart/form-data" }}
		// return this.$fhcApi.post(url, formData, headers)
	},
	postStudentProjektarbeitZwischenabgabe(formData) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Abgabe/postStudentProjektarbeitZwischenabgabe',
			params: { formData },
			config: {Headers: { "Content-Type": "multipart/form-data" }}
		};
		
		// const url = '/api/frontend/v1/Lehre/postStudentProjektarbeitZwischenabgabe';
		// const headers = {Headers: { "Content-Type": "multipart/form-data" }}
		// return this.$fhcApi.post(url, formData, headers)
	},
	getStudentProjektarbeitAbgabeFile(paabgabe_id, student_uid) {
		// TODO: check if this is fine with new api scheme
		
		const url = `/Cis/Abgabetool/getStudentProjektarbeitAbgabeFile?paabgabe_id=${paabgabe_id}&student_uid=${student_uid}`;

		window.location = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + url
	},
	getMitarbeiterProjektarbeiten(all) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Abgabe/getMitarbeiterProjektarbeiten',
			params: { showall: all }
		};
	},
	postProjektarbeitAbgabe(termin) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Abgabe/postProjektarbeitAbgabe',
			params: { 
				paabgabe_id: termin.paabgabe_id,
				paabgabetyp_kurzbz: termin.bezeichnung.paabgabetyp_kurzbz,
				datum: termin.datum,
				fixtermin: termin.fixtermin,
				insertvon: termin.insertvon,
				kurzbz: termin.kurzbz,
				projektarbeit_id: termin.projektarbeit_id 
			}
		};
	},
	deleteProjektarbeitAbgabe(paabgabe_id) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Abgabe/deleteProjektarbeitAbgabe',
			params: { paabgabe_id }
		};
	},
	postSerientermin(datum, paabgabetyp_kurzbz, bezeichnung, kurzbz, projektarbeit_ids) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Abgabe/postSerientermin',
			params: { datum, paabgabetyp_kurzbz, bezeichnung, kurzbz, projektarbeit_ids }
		};
	},
	fetchDeadlines(person_id) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Abgabe/fetchDeadlines',
			params: { person_id }
		};
	},
	getPaAbgabetypen() {
		return {
			method: 'get',
			url: '/api/frontend/v1/Abgabe/getPaAbgabetypen'
		};
	}
};