export default {

    getUser: function() {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + 'Cis/Profil/getUser';
        return axios.get(url);
    },
    isMitarbeiterOrStudent: function(uid) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + `Cis/Profil/isMitarbeiterOrStudent/${uid}`;
        return axios.get(url);
    },
    getPersonInformation: function(uid) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + `Cis/Profil/getPersonInformation/${uid}`;
        return axios.get(url);
    },
    
    getUser2: function(uid) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + 'models/person/Benutzer_model/getFromPersonId';
        return axios.get(url,{params: {uid}});
    },
    getUserDumy: function(){
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                + 'public/js/apps/api/dummyapi.php/getUser';
        return axios.get(url); 
    }
};