export default {
    getStudentenMail(lehreinheit_id) {
          return this.$fhcApi.get(
              FHC_JS_DATA_STORAGE_OBJECT.app_root +
              FHC_JS_DATA_STORAGE_OBJECT.ci_router +
              "/api/frontend/v1/Lehre/lvStudentenMail",
              { lehreinheit_id: lehreinheit_id }
          );
      },
	getLvInfo(studiensemester_kurzbz, lehrveranstaltung_id) {
		return this.$fhcApi.get(
			`/api/frontend/v1/Lehre/LV/${studiensemester_kurzbz}/${lehrveranstaltung_id}`
			, {}
		);
	},
	getStudentPruefungen(lehrveranstaltung_id){
		return this.$fhcApi.get(
			`/api/frontend/v1/Lehre/Pruefungen/${lehrveranstaltung_id}`
			, {}
		);
	},
	getStudentProjektarbeiten(uid) {
		return this.$fhcApi.get(
			`/api/frontend/v1/Lehre/getStudentProjektarbeiten/${uid}`
			, {}
		);
	},
	getStudentProjektabgaben(detail) {
		return this.$fhcApi.get(
			`/api/frontend/v1/Lehre/getStudentProjektabgaben`
			, {
				projektarbeit_id: detail.projektarbeit_id,
				student_uid: detail.student_uid
			}
		);
	},
	postStudentProjektarbeitEndupload(formData) {
		const url = '/api/frontend/v1/Lehre/postStudentProjektarbeitEndupload';
		const headers = {Headers: { "Content-Type": "multipart/form-data" }}
		return this.$fhcApi.post(url, formData, headers)
	},
	postStudentProjektarbeitZwischenabgabe(formData) {
		const url = '/api/frontend/v1/Lehre/postStudentProjektarbeitZwischenabgabe';
		const headers = {Headers: { "Content-Type": "multipart/form-data" }}
		return this.$fhcApi.post(url, formData, headers)
	},
	getStudentProjektarbeitAbgabeFile(paabgabe_id, student_uid) {
		const url = `/Cis/Abgabetool/getStudentProjektarbeitAbgabeFile?paabgabe_id=${paabgabe_id}&student_uid=${student_uid}`;

		window.location = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + url
	},
	getMitarbeiterProjektarbeiten(uid, all) {
		return this.$fhcApi.get(
			`/api/frontend/v1/Lehre/getMitarbeiterProjektarbeiten?showall=${all}`
			, {}
		);
	},
	postProjektarbeitAbgabe(termin) {
		const payload = {
			paabgabe_id: termin.paabgabe_id,
			paabgabetyp_kurzbz: termin.bezeichnung.paabgabetyp_kurzbz,
			datum: termin.datum,
			fixtermin: termin.fixtermin,
			insertvon: termin.insertvon,
			kurzbz: termin.kurzbz,
			projektarbeit_id: termin.projektarbeit_id
		}
		const url = '/api/frontend/v1/Lehre/postProjektarbeitAbgabe';

		return this.$fhcApi.post(url, payload, null)
		
	},
	deleteProjektarbeitAbgabe(paabgabe_id) {
		const payload = {
			paabgabe_id
		}
		const url = '/api/frontend/v1/Lehre/deleteProjektarbeitAbgabe';

		return this.$fhcApi.post(url, payload, null)
	},
	postSerientermin(datum, paabgabetyp_kurzbz, bezeichnung, kurzbz, projektarbeit_ids) {
		const payload = {
			datum, paabgabetyp_kurzbz, bezeichnung, kurzbz, projektarbeit_ids
		}
		const url = '/api/frontend/v1/Lehre/postSerientermin';

		return this.$fhcApi.post(url, payload, null)
	},
	fetchDeadlines(person_id) {
		const payload = {
			person_id
		}
		const url = '/api/frontend/v1/Lehre/fetchDeadlines';

		return this.$fhcApi.post(url, payload, null)
	}
  }