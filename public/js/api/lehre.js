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
  }