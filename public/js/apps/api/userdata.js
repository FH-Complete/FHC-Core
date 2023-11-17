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
    
 
};