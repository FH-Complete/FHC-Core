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
      zeitaufzeichnung: '',
      azgrelevant: '',
      homeoffice: '',
      gueltig_ab: '',
      gueltig_bis: ''
    }
  },
  template: `
  <div v-bind:id="id" class="border-bottom py-2 mb-3">
    <div class="row g-2 flex-row-reverse">
      <div class="col">
        <button type="button" class="btn-close btn-sm p-2 float-end" @click="removeVB" aria-label="Close"></button>
      </div>
    </div>
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
      <gueltigkeit ref="gueltigkeit"></gueltigkeit>
    </div>
  </div>
  `,
  components: {
    'gueltigkeit': gueltigkeit
  },
  methods: {
    removeVB: function() {
      this.$emit('removeVB', {id: this.id});
    },
    getPayload: function() {
      return {
        zeitaufzeichnung: this.zeitaufzeichnung,
        azgrelevant: this.azgrelevant,
        homeoffice: this.homeoffice,
        gueltigkeit: this.$refs.gueltigkeit.getPayload(),
      };
    }
  }
}
