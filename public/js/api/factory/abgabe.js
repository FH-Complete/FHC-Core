export default {
	getConfig() {
		return {
			method: 'get',
			url: '/api/frontend/v1/Abgabe/getConfig'
		};
	},
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
			params: formData,
			config: {Headers: { "Content-Type": "multipart/form-data" }}
		};
	},
	postStudentProjektarbeitZwischenabgabe(formData) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Abgabe/postStudentProjektarbeitZwischenabgabe',
			params: formData,
			config: {Headers: { "Content-Type": "multipart/form-data" }}
		};
	},
	getMitarbeiterProjektarbeiten(all) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Abgabe/getMitarbeiterProjektarbeiten',
			params: { showall: all }
		};
	},
	postProjektarbeitAbgabe(termin) {
		
		let dateString = termin.datum
		if(termin.datum instanceof Date) {
			const year = termin.datum.getFullYear();
			const month = String(termin.datum.getMonth() + 1).padStart(2, '0');
			const day = String(termin.datum.getDate()).padStart(2, '0');

			dateString = `${year}-${month}-${day}`
		}
		
		return {
			method: 'post',
			url: '/api/frontend/v1/Abgabe/postProjektarbeitAbgabe',
			params: { 
				paabgabe_id: termin.paabgabe_id,
				paabgabetyp_kurzbz: termin.bezeichnung.paabgabetyp_kurzbz,
				datum: dateString,
				note: termin.note_pk,
				upload_allowed: !!termin.upload_allowed,
				beurteilungsnotiz: termin.beurteilungsnotiz ?? '',
				fixtermin: termin.fixtermin,
				insertvon: termin.insertvon,
				kurzbz: termin.kurzbz,
				projektarbeit_id: termin.projektarbeit_id,
				betreuer_person_id: termin.betreuer_person_id
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
	postSerientermin(datum, paabgabetyp_kurzbz, bezeichnung, kurzbz, upload_allowed, projektarbeit_ids, fixtermin) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Abgabe/postSerientermin',
			params: { datum, paabgabetyp_kurzbz, bezeichnung, kurzbz, upload_allowed, projektarbeit_ids, fixtermin }
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
	},
	//TODO: SWITCH TO NOTEN API ONCE NOTENTOOL IS IN MASTER TO AVOID DUPLICATE API
	getNoten(){
		return {
			method: 'get',
			url: '/api/frontend/v1/Abgabe/getNoten'
		};
	},
	getProjektarbeitenForStudiengang(studiengang_kz, benotet = 0) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Abgabe/getProjektarbeitenForStudiengang',
			params: { studiengang_kz, benotet }
		};
	},
	// TODO: this could also very well be generic info api
	getStudiengaenge() {
		return {
			method: 'get',
			url: '/api/frontend/v1/Abgabe/getStudiengaenge'
		};
	},
	postStudentProjektarbeitZusatzdaten(formData) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Abgabe/postStudentProjektarbeitZusatzdaten',
			params: formData,
			config: {Headers: { "Content-Type": "multipart/form-data" }}
		};
	}
};