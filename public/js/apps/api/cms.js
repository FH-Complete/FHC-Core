export default {
  getNews: function (page = 1) {
    console.log("this is the page that was passed", page);
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      "/CisHtml/Cms/getNews";
    return axios.get(url, {
      params: {
        page,
      },
    });
  },
  getNewsMaxPage: function () {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      "/CisHtml/Cms/getNewsMaxPage";
    return axios.get(url);
  },
};
