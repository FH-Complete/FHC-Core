export default {

    getUser: function() {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + 'cis.php/Cis/Profil/getUser';
        return axios.get(url);
    },
    isMitarbeiterOrStudent: function(uid) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + `cis.php/Cis/Profil/isMitarbeiterOrStudent/${uid}`;
        return axios.get(url);
    },
    getMitarbeiterAnsicht: function() {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + `cis.php/Cis/Profil/getMitarbeiterAnsicht`;
        return axios.get(url);
    },
    sperre_foto_function: function(value) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + `cis.php/Cis/Profil/foto_sperre_function/${value}`;
        return axios.get(url);
    },
    getBenutzerFunktionen: function() {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                   "/Cis/Profil/getBenutzerFunktionen";
        
        return axios.get(url);
    },
    
    indexProfilInformaion: function() {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                   "/Cis/Profil/indexProfilInformaion";
        
        return axios.get(url);
    },
    mitarbeiterProfil: function() {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                   "/Cis/Profil/mitarbeiterProfil";
        
        return axios.get(url);
    },
    studentProfil: function() {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                   "/Cis/Profil/studentProfil";
        
        return axios.get(url);
    },


    
 
};