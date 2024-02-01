export default {
  //! API Calls for Profil Views

  insertFile: function (dms) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/Cis/Profil/insertFile`;

    return axios.post(url, dms, {
      headers: { "Content-Type": "multipart/form-data" },
    });
  },

  deleteOldVersionFiles: function (files) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/Cis/Profil/deleteOldVersionFiles`;

    return axios.post(url, files);
  },

  getProfilRequestFiles: function (requestID) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/Cis/Profil/getProfilRequestFiles`;

    return axios.post(url, requestID);
  },

  selectProfilRequest: function (uid = null, id = null) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/Cis/Profil/selectProfilRequest`;

    return axios.get(url, { uid: uid, id: id });
  },

  insertProfilRequest: function (topic, payload) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/Cis/Profil/insertProfilRequest`;

    return axios.post(url, { topic, payload });
  },

  updateProfilRequest: function (topic, payload, ID) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/Cis/Profil/updateProfilRequest`;

    return axios.post(url, { topic, payload, ID });
  },

  deleteProfilRequest: function (requestID) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/Cis/Profil/deleteProfilRequest`;

    return axios.post(url, requestID);
  },

  getEditProfil: function () {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      `/Cis/Profil/getEditProfil`;
    return axios.get(url);
  },

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
