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
				projektarbeit_id: detail.projektarbeit_id
			}
		);
	}
  }