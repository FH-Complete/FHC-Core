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
	//api function used for the news View that renders the html
	getNews(page = 1, page_size = 10, sprache) {
		return this.$fhcApi.get(
			"/api/frontend/v1/Cms/getNews",
			{
				page,
				page_size,
				sprache,
			},
		);
	},
	//api function used for the widget component
	news(limit) {
		return this.$fhcApi.get(
			"/api/frontend/v1/Cms/news",
			{
				limit: limit
			}
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