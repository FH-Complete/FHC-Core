export default {

 
    
    isMitarbeiterOrStudent: function(uid) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + `cis.php/Cis/Profil/isMitarbeiterOrStudent/${uid}`;
        return axios.get(url);
    },

    getView: function(payload) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + `cis.php/Cis/Profil/getView`;
        return axios.post(url,payload);
    },
 
    sperre_foto_function: function(value) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + `cis.php/Cis/Profil/foto_sperre_function/${value}`;
        return axios.get(url);
    },

    
    indexProfilInformaion: function(uid, view=false) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                   `/Cis/Profil/indexProfilInformaion/${uid}/${view}`;
        
        return axios.get(url);
    },
    mitarbeiterProfil: function() {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                   `/Cis/Profil/mitarbeiterProfil/`;
        
        return axios.get(url);
    },
    studentProfil: function(uid, view=false) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                   `/Cis/Profil/studentProfil/${uid}/${view}`;
        
        return axios.get(url);
    },


    
 
};