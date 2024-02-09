export default {
  //! API Calls for Profil Views



  isMitarbeiterOrStudent: function (uid) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      `cis.php/Cis/Profil/isMitarbeiterOrStudent/${uid}`;
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
};
