export default {
  _uuidsbyname: {},
  get_uuid: function() {
    var uuidValue = "", k, randomValue;
    for (k = 0; k < 32; k++) {
      randomValue = Math.random() * 16 | 0;

      if (k == 8 || k == 12 || k == 16 || k == 20) {
        uuidValue += "-"
      }
      uuidValue += (k == 12 ? 4 : (k == 16 ? (randomValue & 3 | 8) : randomValue)).toString(16);
    }
    return uuidValue;
  },
  get_uuidbyname: function(name) {
    if( this._uuidsbyname[name] === undefined ) {
      this._uuidsbyname[name] = this.get_uuid();
    }
    return this._uuidsbyname[name];
  }
}
