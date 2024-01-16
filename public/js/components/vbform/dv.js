import presetable from '../../mixins/vbform/presetable.js';
import dvneuanlage from './dvneuanlage.js';
import dvaenderung from './dvaenderung.js';
import store from './vbsharedstate.js';

export default {
  template:`
    <div class="row g-2 py-2 border-bottom mb-3">
      <dvaenderung ref="formheader" :config="data" v-if="isaenderung"></dvaenderung>
      <dvneuanlage ref="formheader" :config="data" v-else=""></dvneuanlage>
    </div>
  `,
  components: {
    'dvneuanlage': dvneuanlage,
    'dvaenderung': dvaenderung
  },
  mixins: [
    presetable
  ],
  data: function() {
    return {
      store: store,
      data: {}
    }
  },
  created: function() {
    this.data = this.store.getDV();
  },
  methods: {
    getPayload: function() {
      this.store.setDV(this.$refs.formheader.getPayload());
      return JSON.parse(JSON.stringify(this.preset));
    }
  },
  computed: {
    isaenderung: function() {
      var ret = ((this.data?.dienstverhaeltnisid !== undefined)
        && !isNaN(parseInt(this.data.dienstverhaeltnisid))
        && parseInt(this.data.dienstverhaeltnisid) > 0);
      return ret;
    }
  }
}
