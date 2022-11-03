export default {
  search: function(searchsettings) {
      const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                + 'index.ci.php/components/SearchBar/search';
      return axios.post(url, searchsettings);
  },
  searchdummy: function(searchsettings) {
      const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                + 'public/js/apps/api/dummyapi.php/Search';
      return axios.post(url, searchsettings);
  }
};