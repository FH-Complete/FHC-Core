import gueltigkeit from './gueltigkeit.js';
import configurable from '../../mixins/vbform/configurable.js';

export default {
  template: `
  <div class="border-bottom py-2 mb-3">
    <div class="row g-2">
      <div class="col-2 form-check form-control-sm">
        <input v-model="zeitaufzeichnung" class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
        <label class="form-check-label" for="flexCheckDefault">
          Zeitaufzeichnung
        </label>
      </div>
      <div class="col-2 form-check form-control-sm">
        <input v-model="azgrelevant" class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
        <label class="form-check-label" for="flexCheckDefault">
          AZG-relevant
        </label>
      </div>
      <div class="col-2 form-check form-control-sm">
        <input v-model="homeoffice" class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
        <label class="form-check-label" for="flexCheckDefault">
          Home-Office
        </label>
      </div>
      <div class="col-1 form-check form-control-sm">&nbsp;</div>
      <gueltigkeit ref="gueltigkeit"></gueltigkeit>
      <div class="col-1">
        <button v-if="isremoveable" type="button" class="btn-close btn-sm p-2 float-end" @click="removeVB" aria-label="Close"></button>
      </div>
    </div>
  </div>
  `,
  components: {
    'gueltigkeit': gueltigkeit
  },
  mixins: [
    configurable
  ],
  emits: {
    removeVB: null
  },
  data: function () {
    return {
      zeitaufzeichnung: '',
      azgrelevant: '',
      homeoffice: '',
      gueltig_ab: '',
      gueltig_bis: ''
    }
  },
  created: function() {
    this.setDataFromConfig();
  },
  methods: {
    setDataFromConfig: function() {
      if( typeof this.config.data === 'undefined' ) {
        return;
      }

      if( typeof this.config.data.zeitaufzeichnung !== 'undefined' ) {
        this.zeitaufzeichnung = this.config.data.zeitaufzeichnung;
      }
      if( typeof this.config.data.azgrelevant !== 'undefined' ) {
        this.azgrelevant = this.config.data.azgrelevant;
      }
      if( typeof this.config.data.homeoffice !== 'undefined' ) {
        this.homeoffice = this.config.data.homeoffice
      }
    },
    removeVB: function() {
      this.$emit('removeVB', {id: this.config.guioptions.id});
    },
    getPayload: function() {
      return {
        type: this.config.type,
        guioptions: this.config.guioptions,
        data: {
          zeitaufzeichnung: this.zeitaufzeichnung,
          azgrelevant: this.azgrelevant,
          homeoffice: this.homeoffice,
          gueltigkeit: this.$refs.gueltigkeit.getPayload()
        }
      };
    }
  }
}
