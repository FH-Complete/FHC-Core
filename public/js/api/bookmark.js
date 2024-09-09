export default {

    getBookmarks: function () {
      return this.$fhcApi.get(
        `/api/frontend/v1/Bookmark/getBookmarks`
        ,{}
      );
    },
  
    delete: function (bookmark_id) {
      return this.$fhcApi.get(
        `/api/frontend/v1/Bookmark/delete/${bookmark_id}`
        ,{}
      ); 
    },

    insert: function ({url, title, tag}) {
      return this.$fhcApi.post(
        `/api/frontend/v1/Bookmark/insert`
        ,{
          url: url,
          title: title,
          tag: tag
        }
      ); 
    },
}