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
        `/Cis/ProfilUpdate/getTopic`,{});
    },
  
    getProfilUpdateRequest: function () {

        return this.$fhcApi.get(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/Cis/ProfilUpdate/getAllRequests`,{});
      
    },
  

    //TODO post request
    acceptProfilRequest: function (payload) {

        const url =
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        "/Cis/ProfilUpdate/acceptProfilRequest";
      return axios.post(url, payload); 
    },
  
    //TODO post request
    denyProfilRequest: function (payload) {
      const url =
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        "/Cis/ProfilUpdate/denyProfilRequest";
      return axios.post(url, payload);
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
    //? new reuquests
    insertFile: function (dms, replace = null) {
      const url =
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/Cis/ProfilUpdate/insertFile/${replace}`;
  
      return axios.post(url, dms, {
        headers: { "Content-Type": "multipart/form-data" },
      });
    },
  
    //TODO post request
    getProfilRequestFiles: function (requestID) {
      const url =
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/Cis/ProfilUpdate/getProfilRequestFiles`;
  
      return axios.post(url, requestID);
    },
  
    selectProfilRequest: function (uid = null, id = null) {

        return this.$fhcApi.get(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/Cis/ProfilUpdate/selectProfilRequest`,{ uid: uid, id: id });
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
  