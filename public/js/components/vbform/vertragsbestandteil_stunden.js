import gehaltsbestandteilhelper from './gehaltsbestandteilhelper.js';
import gueltigkeit from './gueltigkeit.js';
import configurable from '../../mixins/vbform/configurable.js';

export default {
  template: `
  <div class="border-bottom py-2 mb-3">
    <div class="row g-2">
      <div class="col-3">
        <div class="input-group input-group-sm mb-3">
          <input v-model="stunden" type="text" class="form-control form-control-sm" placeholder="Stunden" aria-label="stunden">
          <span class="input-group-text">Std/Woche</span>
        </div>
      </div>
      <div class="col-4">&nbsp;</div>
      <gueltigkeit ref="gueltigkeit"></gueltigkeit>
      <div class="col-1">
        <button v-if="isremoveable" type="button" class="btn-close btn-sm p-2 float-end" @click="removeVB" aria-label="Close"></button>
      </div>
    </div>
    <gehaltsbestandteilhelper ref="gbh" v-bind:preset="getgehaltsbestandteile"></gehaltsbestandteilhelper>
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
      stunden: ''
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

      if( typeof this.config.data.stunden !== 'undefined' ) {
        this.stunden = this.config.data.stunden;
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
          stunden: this.stunden,
          gueltigkeit: this.$refs.gueltigkeit.getPayload()
        },
        gbs: this.$refs.gbh.getPayload()
      };
    }
  }
}
