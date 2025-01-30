export default {

    getBookmarks: function () {
      return this.$fhcApi.get(
        `/api/frontend/v1/Bookmark/getBookmarks`
        ,{}
      );
    },
  
    delete: function (bookmark_id) {
      return this.$fhcApi.post(
        `/api/frontend/v1/Bookmark/delete/${bookmark_id}`
        ,{}
      ); 
    },

	update: function ({ bookmark_id, url, title, tag=null}) {
		return this.$fhcApi.post(
			`/api/frontend/v1/Bookmark/update/${bookmark_id}`
			, {
				url: url,
				title: title
			}
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