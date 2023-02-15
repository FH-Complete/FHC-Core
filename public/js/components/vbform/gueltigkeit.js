export default {
  data: function() {
    return {
      gueltig_ab: '',
      gueltig_bis: ''
    }
  },
  template: `
  <div class="col-2">
    <input v-model="gueltig_ab" type="text" class="form-control form-control-sm" placeholder="gültig ab" aria-label="gueltig ab">
  </div>
  <div class="col-2">
    <input v-model="gueltig_bis" type="text" class="form-control form-control-sm" placeholder="gültig bis" aria-label="gueltig bis">
  </div>
  `,
  components: {},
  methods: {
    getPayload: function() {
      return {
        gueltig_ab: this.gueltig_ab,
        gueltig_bis: this.gueltig_bis,
      };
    }
  }
}
