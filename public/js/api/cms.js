export default {
    content(content_id, version=null, sprache=null, sichtbar=null) {
        return this.$fhcApi.get(
			"/api/frontend/v1/Cms/content",
			{ 
				content_id: content_id,
				...(version?{version}:{}),
				...(sprache?{sprache}:{}),
				...(sichtbar?{sichtbar}:{}),
            }
        );
    },

	getNews(page = 1, page_size = 10) {
		return this.$fhcApi.get(
			"/api/frontend/v1/Cms/getNews",
			{
				page,
				page_size,
			},
		);
	},

	getNewsRowCount: function () {
		return this.$fhcApi.get(
			"/api/frontend/v1/Cms/getNewsRowCount",
			{}
		);
	},

	getNewsExtra: function(){
		return this.$fhcApi.get(
			"/api/frontend/v1/Cms/getStudiengangInfoForNews",
			{}
		);
	}
   
  }