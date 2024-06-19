export default {
  getContentID(ort_kurbz) {
		return this.$fhcApi.get(
            FHC_JS_DATA_STORAGE_OBJECT.app_root +
            FHC_JS_DATA_STORAGE_OBJECT.ci_router +
            "/api/frontend/v1/Ort/ContentID",
			{ ort_kurzbz: ort_kurbz }
		);
	},
}