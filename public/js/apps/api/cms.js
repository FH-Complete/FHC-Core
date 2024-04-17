export default {
  getNews: function (page = 1, page_size = 10) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      "/CisHtml/Cms/getNews";
    return axios.get(url, {
      params: {
        page,
        page_size,
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
