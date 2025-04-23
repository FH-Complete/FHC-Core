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
	}
  }