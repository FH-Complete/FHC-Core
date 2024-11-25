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
  
    insertFile: function (dms, replace = null) {

      return this.$fhcApi.post(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/ProfilUpdate/insertFile/${replace}`,
        dms);

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
  
    insertProfilRequest: function (topic, payload, fileID = null) {

      return this.$fhcApi.post(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        "/api/frontend/v1/ProfilUpdate/insertProfilRequest",
        {
          topic,
          payload,
          ...(fileID ? { fileID } : {}),
        });
    },
  
    updateProfilRequest: function (topic, payload, ID, fileID = null) {

      return this.$fhcApi.post(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/ProfilUpdate/updateProfilRequest`,
        {
          topic,
          payload,
          ID,
          ...(fileID ? { fileID: fileID } : {}),
        });
    },
  
    deleteProfilRequest: function (requestID) {

      return this.$fhcApi.post(
        FHC_JS_DATA_STORAGE_OBJECT.app_root +
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/ProfilUpdate/deleteProfilRequest`,
        {
          requestID,
        });
    },
  };
  