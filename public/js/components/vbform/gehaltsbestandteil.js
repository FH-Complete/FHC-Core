import gueltigkeit from './gueltigkeit.js';
import configurable from '../../mixins/vbform/configurable.js';

export default {
  template: `
  <div class="row g-2 py-2">
    <div class="col-3">
      <select v-model="gehaltstyp" class="form-select form-select-sm" aria-label=".form-select-sm example">
        <option value="" selected disabled>Gehaltstyp wählen</option>
        <option value="basis">Basisgehalt</option>
        <option value="grund">Grundgehalt</option>
        <option value="zulage">Zulage</option>
      </select>
    </div>
    <div class="col-2">
      <div class="input-group input-group-sm mb-3">
        <input v-model="betrag" type="text" class="form-control form-control-sm" placeholder="betrag" aria-label="betrag">
        <span class="input-group-text">&euro;</span>
      </div>
    </div>
    <div class="col-2 form-check form-control-sm">
      <input v-model="valorisierung" class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
      <label class="form-check-label" for="flexCheckDefault">
        Valorisierung
      </label>
    </div>
    <gueltigkeit ref="gueltigkeit"></gueltigkeit>
    <div class="col-1">
      <button v-if="isremoveable" type="button" class="btn-close btn-sm p-2 float-end" @click="removeGB" aria-label="Close"></button>
    </div>
  </div>
  `,
  data: function() {
    return {
      gehaltstyp : '',
      betrag: '',
      gueltig_ab: '',
      gueltig_bis: '',
      valorisierung: ''
    }
  },
  components: {
    'gueltigkeit': gueltigkeit
  },
  mixins: [
    configurable
  ],
  created: function() {
    this.setDataFromConfig();
  },
  methods: {
    setDataFromConfig: function() {
      if( typeof this.config.data === 'undefined' ) {
        return;
      }

      if( typeof this.config.data.gehaltstyp !== 'undefined' ) {
        this.gehaltstyp = this.config.data.gehaltstyp;
      }
      if( typeof this.config.data.betrag !== 'undefined' ) {
        this.betrag = this.config.data.betrag;
      }
      if( typeof this.config.data.valorisierung !== 'undefined' ) {
        this.valorisierung = this.config.data.valorisierung;
      }
    },
    removeGB: function() {
      this.$emit('removeGB', {id: this.config.guioptions.id});
    },
    getPayload: function() {
      return {
        type: this.config.type,
        guioptions: this.config.guioptions,
        data: {
          gehaltstyp: this.gehaltstyp,
          betrag: this.betrag,
          gueltigkeit: this.$refs.gueltigkeit.getPayload(),
          valorisierung: this.valorisierung
        }
      };
    }
  }
}
