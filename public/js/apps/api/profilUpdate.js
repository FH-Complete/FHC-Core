export default {

    //! API calls for profil update requests

    getProfilUpdateRequest: function(){
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/Cis/ProfilUpdate/getAllRequests';
        return axios.get(url);
    },

    acceptProfilRequest: function(payload){
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/Cis/ProfilUpdate/acceptProfilRequest';
        return axios.post(url,payload);
    },

    denyProfilRequest: function(payload){
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/Cis/ProfilUpdate/denyProfilRequest';
        return axios.post(url,payload);
    }

}