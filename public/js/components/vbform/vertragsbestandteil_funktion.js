import gehaltsbestandteilhelper from './gehaltsbestandteilhelper.js'
import gueltigkeit from './gueltigkeit.js';
import configurable from '../../mixins/vbform/configurable.js';

export default {
  template: `
  <div class="py-2" :class="vbcssclasses">
    <div class="row g-2">
      <div class="col">
        <input v-model="funktion" :disabled="isinputdisabled('funktion')" type="text" class="form-control form-control-sm" placeholder="Funktion" aria-label="funktion">
      </div>
      <div class="col">
        <input v-model="orget" type="text" class="form-control form-control-sm" placeholder="Organisations-Einheit" aria-label="orget">
      </div>
      <gueltigkeit ref="gueltigkeit" :config="getgueltigkeit"></gueltigkeit>
      <div class="col-1">
        <button v-if="isremoveable" type="button" class="btn-close btn-sm p-2 float-end" @click="removeVB" aria-label="Close"></button>
      </div>
    </div>
    <gehaltsbestandteilhelper v-if="canhavegehaltsbestandteile" ref="gbh" v-bind:preset="getgehaltsbestandteile"></gehaltsbestandteilhelper>
  </div>
  `,
  components: {
    'gehaltsbestandteilhelper': gehaltsbestandteilhelper,
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
      funktion: '',
      orget: ''
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

      if( typeof this.config.data.funktion !== 'undefined' ) {
        this.funktion = this.config.data.funktion;
      }
    },
    removeVB: function() {
      this.$emit('removeVB', {id: this.config.guioptions.id});
    },
    getGehaltsbestandteilePayload: function() {
      return (!this.$refs?.gbh === undefined) ? this.$refs.gbh.getPayload() : [];
    },
    getPayload: function() {
      return {
        type: this.config.type,
        guioptions: this.config.guioptions,
        data: {
          funktion: this.funktion,
          orget: this.orget,
          gueltigkeit: this.$refs.gueltigkeit.getPayload()
        },
        gbs: this.getGehaltsbestandteilePayload()
      };
    }
  }
}
