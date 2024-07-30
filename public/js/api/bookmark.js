export default {

    getBookmarks: function (uid) {
      return this.$fhcApi.get(
        FHC_JS_DATA_STORAGE_OBJECT.app_root + 
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/Bookmark/getBookmarks`
        ,{}
      );
    },
  
    deleteBookmark: function (bookmark_id) {
      
      return this.$fhcApi.get(
        FHC_JS_DATA_STORAGE_OBJECT.app_root + 
        FHC_JS_DATA_STORAGE_OBJECT.ci_router +
        `/api/frontend/v1/Bookmark/delete/${bookmark_id}`
        ,{}
      ); 
    },
}