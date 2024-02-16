export default {

    //! API calls for profil update requests

    getProfilUpdateRequest: function(){
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/Cis/ProfilUpdate/getAllRequests';
        return axios.get(url);
    },

    acceptProfilRequest: function(payload){
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/Cis/ProfilUpdate/acceptProfilRequest';
        return axios.post(url,payload);
    },

    denyProfilRequest: function(payload){
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/Cis/ProfilUpdate/denyProfilRequest';
        return axios.post(url,payload);
    },

    replaceProfilUpdateAttachment: function (dms) {
        const url =
          FHC_JS_DATA_STORAGE_OBJECT.app_root +
          FHC_JS_DATA_STORAGE_OBJECT.ci_router +
          `/Cis/ProfilUpdate/replaceProfilUpdateAttachment`;
    
          return axios.post(url, dms, {
            headers: { "Content-Type": "multipart/form-data" },
          });
      },


      //? new reuquests
      insertFile: function (dms,replace=null) {
        const url =
          FHC_JS_DATA_STORAGE_OBJECT.app_root +
          FHC_JS_DATA_STORAGE_OBJECT.ci_router +
          `/Cis/ProfilUpdate/insertFile/${replace}`;
    
        return axios.post(url, dms, {
          headers: { "Content-Type": "multipart/form-data" },
        });
      },   
   
    
      getProfilRequestFiles: function (requestID) {
        const url =
          FHC_JS_DATA_STORAGE_OBJECT.app_root +
          FHC_JS_DATA_STORAGE_OBJECT.ci_router +
          `/Cis/ProfilUpdate/getProfilRequestFiles`;
    
        return axios.post(url, requestID);
      },
    
      selectProfilRequest: function (uid = null, id = null) {
        const url =
          FHC_JS_DATA_STORAGE_OBJECT.app_root +
          FHC_JS_DATA_STORAGE_OBJECT.ci_router +
          `/Cis/ProfilUpdate/selectProfilRequest`;
    
        return axios.get(url, { uid: uid, id: id });
      },
    
      
    
    
      insertProfilRequest: function (topic, payload, fileID=null) {
        const url =
          FHC_JS_DATA_STORAGE_OBJECT.app_root +
          FHC_JS_DATA_STORAGE_OBJECT.ci_router +
          `/Cis/ProfilUpdate/insertProfilRequest`;
    
        return axios.post(url, { topic, payload, ...(fileID?{fileID:fileID}:{}) });
      },
    
      updateProfilRequest: function (topic, payload, ID, fileID=null) {
        const url =
          FHC_JS_DATA_STORAGE_OBJECT.app_root +
          FHC_JS_DATA_STORAGE_OBJECT.ci_router +
          `/Cis/ProfilUpdate/updateProfilRequest`;
    
        return axios.post(url, { topic, payload, ID, ...(fileID?{fileID:fileID}:{}) });
      },
    
      deleteProfilRequest: function (requestID) {
        const url =
          FHC_JS_DATA_STORAGE_OBJECT.app_root +
          FHC_JS_DATA_STORAGE_OBJECT.ci_router +
          `/Cis/ProfilUpdate/deleteProfilRequest`;
    
        return axios.post(url, requestID);
      },

  
    
    

}