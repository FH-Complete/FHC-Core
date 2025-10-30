var search = {
  search: function (searchsettings) {
    const url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/SearchBar/search';
    return axios.post(url, searchsettings);
  }
};

export { search as default };
//# sourceMappingURL=search.js.map
