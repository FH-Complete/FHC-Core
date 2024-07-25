export default {
    
    getNonConfirmedActiveAmpeln: function () {
        return this.$fhcApi.get(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/Ampeln/getNonConfirmedActiveAmpeln`,{});
    },

    getAllActiveAmpeln: function () {
        return this.$fhcApi.get(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/Ampeln/getAllActiveAmpeln`,{});
    },

    

    confirmAmpel: function (ampel_id) {
        return this.$fhcApi.get(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/Ampeln/confirmAmpel/${ampel_id}`,{});
    },

    alleAmpeln: function () {
        return this.$fhcApi.get(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/Ampeln/alleAmpeln`,{});
    },

}