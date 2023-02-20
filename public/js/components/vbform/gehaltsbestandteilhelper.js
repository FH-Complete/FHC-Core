import gehaltsbestandteil from './gehaltsbestandteil.js';
import presetable from '../../mixins/vbform/presetable.js';
import uuid from '../../helpers/vbform/uuid.js';

export default {
  template: `
  <div class="row g-2 py-2">
    <div class="col">
      <a class="fs-6 fw-light" href="javascript:void(0);" @click="addGB"><i class="fas fa-plus"></i> Gehaltsbestandteil hinzufuegen</a>
    </div>
  </div>
  <div class="row">
    <div class="col">
        <gehaltsbestandteil v-bind:ref="config.guioptions.id" v-for="config in children"
          v-bind:config="config" :key="config.guioptions.id" @removeGB="removeGB"></gehaltsbestandteil>
    </div>
  </div>
  `,
  data: function() {
    return {
      payload: []
    };
  },
  components: {
    'gehaltsbestandteil': gehaltsbestandteil,
  },
  mixins: [
    presetable
  ],
  methods: {
    addGB: function(e) {
      e.preventDefault();
      e.stopPropagation();

      this.children.unshift({
        type: 'gehaltsbestandteil',
        guioptions: {
          id: uuid.get_uuid(),
          removeable: true
        }
      });
    },
    removeGB: function(payload) {
      var children = this.children.filter(function(gb) {
        return gb.guioptions.id !== payload.id;
      });
      this.children = children;
    },
    getPayload: function() {
      var children = this.children;
      var that = this;

      this.payload = [];
      children.forEach(function(gb) {
        that.payload.push(that.$refs[gb.guioptions.id][0].getPayload());
      });

      return this.payload;
    }
  }
}
