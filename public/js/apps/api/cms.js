export default {
  getNews: function (page = 1, pageSize = 10) {
    const url =
      FHC_JS_DATA_STORAGE_OBJECT.app_root +
      FHC_JS_DATA_STORAGE_OBJECT.ci_router +
      "/CisHtml/Cms/getNews";
    return axios.get(url, {
      params: {
        page,
        pageSize,
      },
    });
  },
};
