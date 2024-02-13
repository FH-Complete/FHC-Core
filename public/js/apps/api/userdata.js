export default {
  //! API Calls for Profil Views



  getMitarbeiter: function (student_id) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      `cis.php/api/v1/crm/Student/getStudent`;
    return axios.get(url,{student_id:student_id});
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
};
