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

    getConfirmedActiveAmpeln: function () {
        return this.$fhcApi.get(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/Ampeln/getConfirmedActiveAmpeln`,{});
    },

}