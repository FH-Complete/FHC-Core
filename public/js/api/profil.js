export default {

  getView: function (uid) {
    return this.$fhcApi.get(
      FHC_JS_DATA_STORAGE_OBJECT.app_root + 
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/api/frontend/v1/Profil/getView/${uid}`,{}
    );
  },

  fotoSperre: function (value) {
    return this.$fhcApi.get(
      FHC_JS_DATA_STORAGE_OBJECT.app_root + 
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/api/frontend/v1/Profil/fotoSperre/${value}`,
      {}
    );
    
  },

  isStudent: function (uid) {
    return this.$fhcApi.get(
      FHC_JS_DATA_STORAGE_OBJECT.app_root + 
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/api/frontend/v1/Profil/isStudent`,
      {
        uid:uid,
      }
    );
  },

  isMitarbeiter: function (uid) {
    return this.$fhcApi.get(
      FHC_JS_DATA_STORAGE_OBJECT.app_root + 
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/api/frontend/v1/Profil/isMitarbeiter/${uid}`,
      {}
    );
  },

  getZustellAdresse: function () {
    return this.$fhcApi.get(
      FHC_JS_DATA_STORAGE_OBJECT.app_root + 
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/api/frontend/v1/Profil/getZustellAdresse`,{}
    );
  },

  getZustellKontakt: function () {
    return this.$fhcApi.get(
      FHC_JS_DATA_STORAGE_OBJECT.app_root + 
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/api/frontend/v1/Profil/getZustellKontakt`,{}
    );
  },

  getGemeinden: function(nation,zip){
    return this.$fhcApi.get(
      FHC_JS_DATA_STORAGE_OBJECT.app_root + 
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/api/frontend/v1/Profil/getGemeinden/${nation}/${zip}`,
      {}
    );
    
  },
  getAllNationen:function(){
    return this.$fhcApi.get(
      FHC_JS_DATA_STORAGE_OBJECT.app_root + 
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      "/api/frontend/v1/Profil/getAllNationen",{}
    );
  },
}