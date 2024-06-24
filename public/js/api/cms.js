export default {
    content(content_id, version=null, sprache=null, sichtbar=null) {
          return this.$fhcApi.get(
              FHC_JS_DATA_STORAGE_OBJECT.app_root +
              FHC_JS_DATA_STORAGE_OBJECT.ci_router +
              "/api/frontend/v1/Cms/content",
              { content_id: content_id,
                ...(version?{version}:{}),
                ...(sprache?{sprache}:{}),
                ...(sichtbar?{sichtbar}:{}),
              }
          );
      },
      news(limit) {
        return this.$fhcApi.get(
            FHC_JS_DATA_STORAGE_OBJECT.app_root +
            FHC_JS_DATA_STORAGE_OBJECT.ci_router +
            "/api/frontend/v1/Cms/news",
            { limit: limit}
        );
    },
   
  }