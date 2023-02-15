import vertragsbestandteilstunden from './vertragsbestandteil_stunden.js';
import vertragsbestandteilzeitaufzeichnung from './vertragsbestandteil_zeitaufzeichnung.js';
import vertragsbestandteilfunktion from './vertragsbestandteil_funktion.js';
import vertragsbestandteilfreitext from './vertragsbestandteil_freitext.js';

export default {
  template: `
  <div class="row">
    <div class="col-7">
      <form>
        <div class="row">
          <div class="col">
            <select v-model="vertragsbestandteiltyp" class="form-select form-select-sm" aria-label=".form-select-sm example">
              <option value="vertragsbestandteilstunden">Vertragsbestandteil Stunden</option>
              <option value="vertragsbestandteilzeitaufzeichnung">Vertragsbestandteil Zeitaufzeichnung</option>
              <option value="vertragsbestandteilfunktion">Vertragsbestandteil Funktion</option>
              <option value="vertragsbestandteilfreitext">Vertragsbestandteil Freitext</option>
            </select>
          </div>
          <div class="col">
            <button class="btn btn-primary btn-sm" @click="addVB">Vertragsbestandteil hinzufuegen</button>
          </div>
          <div class="col">
            <button class="btn btn-secondary btn-sm" @click="getJSON">get JSON</button>
          </div>
        </div>
        <component v-bind:ref="vb.id" v-bind:is="vb.type" v-for="vb in vbs" v-bind:id="vb.id" @removeVB="removeVB"></component>
      </form>
    </div>
    <div class="col-5">
      <pre style="background-color: #000; color: #0f0; padding: .5em; height: 90vh;">
{{resjson}}
      </pre>
    </div>
  </div>
  `,
  data: function() {
    return {
      vertragsbestandteiltyp: 'vertragsbestandteil',
      payload: {
        vbs: []
      },
      vbs: [
        {
          type: 'vertragsbestandteilstunden',
          id: 'test1'
        },
        {
          type: 'vertragsbestandteilzeitaufzeichnung',
          id: 'test2'
        }
      ],
    };
  },
  components: {
    'vertragsbestandteilstunden': vertragsbestandteilstunden,
    'vertragsbestandteilzeitaufzeichnung': vertragsbestandteilzeitaufzeichnung,
    'vertragsbestandteilfunktion': vertragsbestandteilfunktion,
    'vertragsbestandteilfreitext': vertragsbestandteilfreitext,
  },
  methods: {
    addVB: function(e) {
      e.preventDefault();
      e.stopPropagation();

      var vbid = 'test' + (this.vbs.length + 1);
      this.vbs.push({
        type: this.vertragsbestandteiltyp,
        id: vbid
      });
    },
    removeVB: function(payload) {
      var vbs = this.vbs.filter(function(vb) {
        return vb.id !== payload.id;
      });
      this.vbs = vbs;
    },
    getJSON: function(e) {
      e.preventDefault();
      e.stopPropagation();

      var vbs = this.vbs;
      var that = this;

      this.payload = {
        vbs: []
      };
      vbs.forEach(function(vb) {
        that.payload.vbs.push(that.$refs[vb.id][0].getPayload());
      });
    }
  },
  computed: {
    resjson: function() {
      return JSON.stringify(this.payload, null, 2);
    }
  }
}
