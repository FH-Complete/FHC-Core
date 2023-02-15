import gehaltsbestandteilhelper from './gehaltsbestandteilhelper.js';
import gueltigkeit from './gueltigkeit.js';

export default {
  props: [
    'id'
  ],
  emits: {
    removeVB: null
  },
  data: function() {
    return {
      freitexttyp: '',
      titel: '',
      freitext: '',
      kuendigungsrelevant: ''
    }
  },
  template: `
  <div v-bind:id="id" class="border-bottom py-2 mb-3">
    <div class="row g-2 flex-row-reverse">
      <div class="col">
        <button type="button" class="btn-close btn-sm p-2 float-end" @click="removeVB" aria-label="Close"></button>
      </div>
    </div>
    <div class="row g-2 py-2">
      <div class="col-3">
        <select v-model="freitexttyp" class="form-select form-select-sm" aria-label=".form-select-sm example">
          <option value="" selected>Freitexttyp wählen</option>
          <option value="allin">AllIn</option>
          <option value="ersatzkraft">Ersatzarbeitskraft</option>
          <option value="zusatzvbg">Zusatzvereinbarung</option>
          <option value="sonstiges">Sonstiges</option>
        </select>
      </div>
      <gueltigkeit ref="gueltigkeit"></gueltigkeit>
    </div>
    <div class="row g-2 py-2">
      <div class="col">
        <input v-model="titel" type="text" class="form-control form-control-sm" placeholder="Titel" aria-label="Titel">
      </div>
    </div>
    <div class="row g-2 py-2">
      <div class="col">
        <textarea v-model="freitext" rows="5" class="form-control form-control-sm" placeholder="Freitext" aria-label="Freitext"></textarea>
      </div>
    </div>
    <div class="row g-2 py-2">
      <gehaltsbestandteilhelper ref="gbh"></gehaltsbestandteilhelper>
    </div>
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
        freitexttyp: this.freitexttyp,
        titel: this.titel,
        freitext: this.freitext,
        kuendigungsrelevant: this.kuendigungsrelevant,
        gueltigkeit: this.$refs.gueltigkeit.getPayload(),
        gehaltsbestandteile: this.$refs.gbh.getPayload()
      };
    }
  }
}
