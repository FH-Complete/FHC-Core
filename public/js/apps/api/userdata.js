export default {


    //! API Calls for Profil Views

    editProfil: function(payload) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                   `/Cis/Profil/editProfil`;
        return axios.post(url,payload);
    },

    getEditProfil: function() {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                   `/Cis/Profil/getEditProfil`;
        return axios.get(url);
    },
    
    isMitarbeiterOrStudent: function(uid) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + `cis.php/Cis/Profil/isMitarbeiterOrStudent/${uid}`;
        return axios.get(url);
    },

    getView: function(uid) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + `cis.php/Cis/Profil/getView/${uid}`;
        return axios.get(url);
    },
 
    sperre_foto_function: function(value) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + `cis.php/Cis/Profil/foto_sperre_function/${value}`;
        return axios.get(url);
    },

    
    //! API Calls for Profil Update View

    getProfilUpdateRequest: function(){
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/Cis/ProfilUpdate/getAllRequests';
        return axios.get(url);
    }
   


    
 
};