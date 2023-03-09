export default Vue.reactive({
  gueltigkeit: {
    gueltig_ab: '',
    gueltig_bis: ''
  },
  dv: {

  },
  vbs: {

  },
  getDV: function() {
    return this.dv;
  },
  setDV: function(dv) {
    this.dv = JSON.parse(JSON.stringify(dv));
  },
  getDVPayload: function() {
    return JSON.parse(JSON.stringify(this.dv));
  },
  addVB: function(uuid, vb) {
    this.vbs[uuid] = vb;
  },
  removeVB: function(uuid) {
    delete this.vbs[uuid];
  },
  getVB: function(uuid) {
    return this.vbs[uuid];
  },
  getVBsPayload: function() {
    return JSON.parse(JSON.stringify(this.vbs));
  }
});
