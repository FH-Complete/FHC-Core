export default {
  getContentID(ort_kurbz) {
		return this.$fhcApi.get(
            FHC_JS_DATA_STORAGE_OBJECT.app_root +
            FHC_JS_DATA_STORAGE_OBJECT.ci_router +
            "/api/frontend/v1/Ort/ContentID",
			{ ort_kurzbz: ort_kurbz }
		);
	},
	getRooms(datum, von, bis, typ, personenanzahl = 0) {
		return this.$fhcApi.get(
			FHC_JS_DATA_STORAGE_OBJECT.app_root +
			FHC_JS_DATA_STORAGE_OBJECT.ci_router +
			"/api/frontend/v1/Ort/getRooms",
			{ datum, von, bis, typ, personenanzahl }
		);
	},
	getRoomTypes() {
		return this.$fhcApi.get(
			FHC_JS_DATA_STORAGE_OBJECT.app_root +
			FHC_JS_DATA_STORAGE_OBJECT.ci_router +
			"/api/frontend/v1/Ort/getTypes"
		);
	}
}