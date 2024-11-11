export default {
  isBerechtigt: function(berechtigung_kurzbz, art, oe_kurzbz, kostenstelle_id) {
    var url = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router 
            + '/api/frontend/v1/Permission/isBerechtigt';
    var payload = {
        "berechtigung_kurzbz": berechtigung_kurzbz,
        "art": art,
        "oe_kurzbz": oe_kurzbz,
        "kostenstelle_id": kostenstelle_id
    };
    return axios.post(url, payload, {
      headers: {
        'Content-Type': 'application/json'
      }
    });
  }
}