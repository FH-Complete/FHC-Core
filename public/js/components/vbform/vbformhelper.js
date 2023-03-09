import presetable from '../../mixins/vbform/presetable.js';
import tabs from './tabs.js';
import vertragsbestandteillist from './vertragsbestandteillist.js';
import dv from './dv.js';
import store from './vbsharedstate.js';

export default {
  template: `
    <div class="vbformhelper">
      <div class="border-bottom py-2 mb-3">
        <div class="row g-2 py-2">
          <div class="col-11">&nbsp;</div>
          <div class="col-1">
            <button class="btn btn-secondary btn-sm float-end" @click="getJSON">get JSON</button>
          </div>
        </div>
      </div>
      <component ref="parts" v-for="(child, idx) in children" :key="idx" :is="child.type" :preset="child"></component>
    </div>
  `,
  components: {
    "tabs": tabs,
    "dv": dv,
    "vertragsbestandteillist": vertragsbestandteillist
  },
  mixins: [
    presetable
  ],
  data: function() {
    return {
      store: store
    };
  },
  emits: [
    "vbhjsonready"
  ],
  methods: {
    getJSON: function() {
      var children = [];
      for ( var i in this.$refs.parts) {
        children.push(this.$refs.parts[i].getPayload());
      }
      var payload = {
        "type": "formdata",
        "children": children,
        "data": this.store.getDVPayload(),
        "vbs": this.store.getVBsPayload()
      };
      this.$emit('vbhjsonready', JSON.stringify(payload, null, 2));
    }
  }
}
