export default {
  //! API Calls for Profil Views

  getGemeinden: function(nation,zip=null){
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      `cis.php/Cis/Profil/getGemeinden`;
    return axios.get(url,{params:{nation:nation,zip:zip}});
  },

  getAllNationen:function(){
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      `cis.php/Cis/Profil/getAllNationen`;
    return axios.get(url);
  },

  getView: function (uid) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root + `cis.php/Cis/Profil/getView/${uid}`;
    return axios.get(url);
  },

  sperre_foto_function: function (value) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      `cis.php/Cis/Profil/foto_sperre_function/${value}`;
    return axios.get(url);
  },

  isStudent: function (uid) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      `cis.php/Cis/Profil/isStudent/${uid}`;
    return axios.get(url);
  },

  isMitarbeiter: function (uid) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      `cis.php/Cis/Profil/isMitarbeiter/${uid}`;
    return axios.get(url);
  },

  getZustellAdresse: function () {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      `cis.php/Cis/Profil/getZustellAdresse`;
    return axios.get(url);
  },

  getZustellKontakt: function () {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      `cis.php/Cis/Profil/getZustellKontakt`;
    return axios.get(url);
  },
};
