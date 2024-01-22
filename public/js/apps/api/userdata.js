export default {


    //! API Calls for Profil Views

    selectProfilRequest: function(uid=null,id=null) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                   `/Cis/Profil/selectProfilRequest?uid=${uid}&id=${id}`;
                   
        return axios.get(url);
    },
    
    insertProfilRequest: function(topic, payload) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                   `/Cis/Profil/insertProfilRequest`;
                   
        return axios.post(url,{topic, payload});
    },

    updateProfilRequest: function(topic, payload) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                   `/Cis/Profil/updateProfilRequest`;
                   
        return axios.post(url,{topic, payload});
    },

    deleteProfilRequest: function(requestID){
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
        `/Cis/Profil/deleteProfilRequest`;

        return axios.post(url,requestID);
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

    
   


    
 
};