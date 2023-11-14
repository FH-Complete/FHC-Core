export default {

    getUser: function() {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + 'Cis/Profil/getUser';
        return axios.get(url);
    },
    getUserDumy: function(){
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                + 'public/js/apps/api/dummyapi.php/getUser';
        return axios.get(url); 
    }
};