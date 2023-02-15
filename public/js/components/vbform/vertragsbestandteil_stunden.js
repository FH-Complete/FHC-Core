import gehaltsbestandteilhelper from './gehaltsbestandteilhelper.js';
import gueltigkeit from './gueltigkeit.js';

export default {
  props: [
    'id'
  ],
  emits: {
    removeVB: null
  },
  data: function () {
    return {
      stunden: ''
    }
  },
  "template": `
  <div v-bind:id="id" class="border-bottom py-2 mb-3">
    <div class="row g-2 flex-row-reverse">
      <div class="col">
        <button type="button" class="btn-close btn-sm p-2 float-end" @click="removeVB" aria-label="Close"></button>
      </div>
    </div>
    <div class="row g-2">
      <div class="col">
        <input v-model="stunden" type="text" class="form-control form-control-sm" placeholder="Stunden" aria-label="stunden">
      </div>
      <gueltigkeit ref="gueltigkeit"></gueltigkeit>
    </div>
    <gehaltsbestandteilhelper ref="gbh"></gehaltsbestandteilhelper>
  </div>
  `,
  components: {
    'gehaltsbestandteilhelper': gehaltsbestandteilhelper,
    'gueltigkeit': gueltigkeit
  },
  methods: {
    removeVB: function() {
      this.$emit('removeVB', {id: this.id});
    },
    getPayload: function() {
      return {
        stunden: this.stunden,
        gueltigkeit: this.$refs.gueltigkeit.getPayload(),
        gehaltsbestandteile: this.$refs.gbh.getPayload()
      };
    }
  }
}
