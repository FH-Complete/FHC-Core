import vertragsbestandteilstunden from './vertragsbestandteil_stunden.js';
import vertragsbestandteilzeitaufzeichnung from './vertragsbestandteil_zeitaufzeichnung.js';
import vertragsbestandteilfunktion from './vertragsbestandteil_funktion.js';
import vertragsbestandteilfreitext from './vertragsbestandteil_freitext.js';
import presetable from '../../mixins/vbform/presetable.js';
import uuid from '../../helpers/vbform/uuid.js';

export default {
  template: `
      <div>
        <div class="row py-2 border-bottom mb-3">
          <div class="col">
            <select v-model="vertragsbestandteiltyp" class="form-select form-select-sm" aria-label=".form-select-sm example">
              <option value="" selected disabled>Vertragsbestandteil wählen</option>
              <option value="vertragsbestandteilstunden">Vertragsbestandteil Stunden</option>
              <option value="vertragsbestandteilzeitaufzeichnung">Vertragsbestandteil Zeitaufzeichnung</option>
              <option value="vertragsbestandteilfunktion">Vertragsbestandteil Funktion</option>
              <option value="vertragsbestandteilfreitext">Vertragsbestandteil Freitext</option>
            </select>
          </div>
          <div class="col">
            <button class="btn btn-primary btn-sm" @click="addVB" v-bind:disabled="(this.vertragsbestandteiltyp === '')">Vertragsbestandteil hinzufuegen</button>
          </div>
          <div class="col">
            <button class="btn btn-secondary btn-sm float-end" @click="getJSON">get JSON</button>
          </div>
        </div>
        <component v-bind:ref="config.guioptions.id" v-bind:is="config.type" v-for="config in children"
          v-bind:config="config" :key="config.guioptions.id" @removeVB="removeVB"></component>
      </div>
  `,
  data: function() {
    return {
      vertragsbestandteiltyp: '',
      payload: {
        type: 'formdata',
        vbs: []
      }
    };
  },
  components: {
    'vertragsbestandteilstunden': vertragsbestandteilstunden,
    'vertragsbestandteilzeitaufzeichnung': vertragsbestandteilzeitaufzeichnung,
    'vertragsbestandteilfunktion': vertragsbestandteilfunktion,
    'vertragsbestandteilfreitext': vertragsbestandteilfreitext,
  },
  mixins: [
    presetable
  ],
  emits: {
    vbhjsonready: null
  },
  methods: {
    addVB: function(e) {
      e.preventDefault();
      e.stopPropagation();

      if( this.vertragsbestandteiltyp === '') {
        return;
      }

      this.children.unshift({
        type: this.vertragsbestandteiltyp,
        guioptions: {
          id: uuid.get_uuid(),
          removeable: true
        }
      });
    },
    removeVB: function(payload) {
      var children = this.children.filter(function(vb) {
        return vb.guioptions.id !== payload.id;
      });
      this.children = children;
    },
    getJSON: function(e) {
      e.preventDefault();
      e.stopPropagation();

      var children = this.children;
      var that = this;

      this.payload = {
        type: 'formdata',
        vbs: []
      };
      children.forEach(function(vb) {
        that.payload.vbs.push(that.$refs[vb.guioptions.id][0].getPayload());
      });

      this.$emit('vbhjsonready', JSON.stringify(this.payload, null, 2));
    }
  }
}
