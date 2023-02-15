import gueltigkeit from './gueltigkeit.js';

export default {
  props: [
    'id'
  ],
  data: function() {
    return {
      gehaltstyp : '',
      betrag: '',
      gueltig_ab: '',
      gueltig_bis: '',
      valorisierung: ''
    }
  },
  template: `
  <div class="row g-2 py-2">
    <div class="col-3">
      <select v-model="gehaltstyp" class="form-select form-select-sm" aria-label=".form-select-sm example">
        <option value="" selected>Gehaltstyp wählen</option>
        <option value="1">Basisgehalt</option>
        <option value="2">Grundgehalt</option>
        <option value="3">Zulage</option>
      </select>
    </div>
    <div class="col-2">
      <input v-model="betrag" type="text" class="form-control form-control-sm" placeholder="betrag" aria-label="betrag">
    </div>
    <gueltigkeit ref="gueltigkeit"></gueltigkeit>
    <div class="col-2 form-check form-control-sm">
      <input v-model="valorisierung" class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
      <label class="form-check-label" for="flexCheckDefault">
        Valorisierung
      </label>
    </div>
    <div class="col-1">
      <button type="button" class="btn-close btn-sm p-2 float-end" @click="removeGB" aria-label="Close"></button>
    </div>
  </div>
  `,
  components: {
    'gueltigkeit': gueltigkeit
  },
  methods: {
    removeGB: function() {
      this.$emit('removeGB', {id: this.id});
    },
    getPayload: function() {
      return {
        gehaltstyp: this.gehaltstyp,
        betrag: this.betrag,
        gueltigkeit: this.$refs.gueltigkeit.getPayload(),
        valorisierung: this.valorisierung
      };
    }
  }
}
