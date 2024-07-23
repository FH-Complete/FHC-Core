export default {
    
    getAmpeln: function () {
        return this.$fhcApi.get(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/Ampeln/getAmpeln`,{});
    },

}