import gueltigkeit from './gueltigkeit.js';
import configurable from '../../mixins/vbform/configurable.js';

export default {
  template: `
  <div class="col-3">
    <select v-model="unternehmen" class="form-select form-select-sm" aria-label=".form-select-sm example">
      <option value="" selected disabled>Unternehmen wählen</option>
      <option value="gst">FH Technikum Wien</option>
      <option value="gmbh">Technikum Wien GmbH</option>
    </select>
  </div>
  <div class="col-3">
    <select v-model="vertragsart_kurzbz" class="form-select form-select-sm" aria-label=".form-select-sm example">
      <option value="" selected disabled>Vertragsart wählen</option>
      <option value="echterDV">Echter DV</option>
      <option value="freierDV">Freier DV</option>
      <option value="Gastlektor">Gastlektor</option>
      <option value="Werkvertrag">Werkvertrag</option>
      <option value="StudHilfskraft">Stud. Hilfskraft</option>
    </select>
  </div>
  <div class="col-1">&nbsp;</div>
  <gueltigkeit ref="gueltigkeit" :initialsharedstatemode="'set'" :config="getgueltigkeit"></gueltigkeit>
  <div class="col-1">&nbsp;</div>
  `,
  data: function() {
    return {
      'unternehmen': '',
      'vertragsart_kurzbz': ''
    }
  },
  components: {
    'gueltigkeit': gueltigkeit
  },
  mixins: [
    configurable
  ],
  watch: {
    config: function() {
      this.setDataFromConfig();
    }
  },
  methods: {
    setDataFromConfig: function() {
      if( this.config?.unternehmen !== undefined ) {
        this.unternehmen = this.config.unternehmen;
      } else {
        this.unternehmen = '';
      }

      if( this.config?.vertragsart_kurzbz !== undefined ) {
        this.vertragsart_kurzbz = this.config.vertragsart_kurzbz;
      } else {
        this.vertragsart_kurzbz = '';
      }
    },
    getPayload: function() {
      return {
        dienstverhaeltnisid: null,
        unternehmen: this.unternehmen,
        vertragsart_kurzbz: this.vertragsart_kurzbz,
        gueltigkeit: this.$refs.gueltigkeit.getPayload()
      }
    }
  }
}
