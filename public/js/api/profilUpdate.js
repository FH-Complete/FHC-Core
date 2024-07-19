export default {
    //! API calls for profil update requests
  
    getStatus: function () {
        return this.$fhcApi.get(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/ProfilUpdate/getStatus`,{});
    },
  
    getTopic: function () {
        return this.$fhcApi.get(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/ProfilUpdate/getTopic`,{});
    },
  
    acceptProfilRequest: function ({profil_update_id, uid, status_message, topic, requested_change}) {

      return this.$fhcApi.post(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        "/api/frontend/v1/ProfilUpdate/acceptProfilRequest",{profil_update_id, uid, status_message, topic, requested_change});
    },
  
    denyProfilRequest: function ({profil_update_id, uid, topic, status_message}) {
      return this.$fhcApi.post(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        "/api/frontend/v1/ProfilUpdate/denyProfilRequest",{profil_update_id,uid,topic,status_message});
    },
  
    //TODO post request
    replaceProfilUpdateAttachment: function (dms) {
      const url =
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/Cis/ProfilUpdate/replaceProfilUpdateAttachment`;
  
      return axios.post(url, dms, {
        headers: { "Content-Type": "multipart/form-data" },
      });
    },
  
    //TODO post request
    //? new requests
    insertFile: function (dms, replace = null) {
      const url =
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/Cis/ProfilUpdate/insertFile/${replace}`;
  
      return axios.post(url, dms, {
        headers: { "Content-Type": "multipart/form-data" },
      });
    },
    
    getProfilRequestFiles: function (requestID) {
      return this.$fhcApi.get(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/ProfilUpdate/getProfilRequestFiles/${requestID}`,{});
    },
  
    selectProfilRequest: function (uid = null, id = null) {

        return this.$fhcApi.get(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/ProfilUpdate/selectProfilRequest`,
        {...(uid?{uid}:{}),
          ...(id?{id}:{})
        });
    },
  
    //TODO post request
    insertProfilRequest: function (topic, payload, fileID = null) {
      const url =
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/Cis/ProfilUpdate/insertProfilRequest`;
  
      return axios.post(url, {
        topic,
        payload,
        ...(fileID ? { fileID: fileID } : {}),
      });
    },
  
    //TODO post request
    updateProfilRequest: function (topic, payload, ID, fileID = null) {
      const url =
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/Cis/ProfilUpdate/updateProfilRequest`;
  
      return axios.post(url, {
        topic,
        payload,
        ID,
        ...(fileID ? { fileID: fileID } : {}),
      });
    },
  
    //TODO post request
    deleteProfilRequest: function (requestID) {
      const url =
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/Cis/ProfilUpdate/deleteProfilRequest`;
  
      return axios.post(url, requestID);
    },
  };
  