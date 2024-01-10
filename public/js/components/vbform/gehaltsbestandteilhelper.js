import gehaltsbestandteil from './gehaltsbestandteil.js';
import presetable from '../../mixins/vbform/presetable.js';
import uuid from '../../helpers/vbform/uuid.js';

export default {
  template: `
  <gehaltsbestandteil ref="parts" v-for="config in children"
    v-bind:config="config" :key="config.guioptions.id" @removeGB="removeGB"></gehaltsbestandteil>
  <div class="row">
    <div class="col-1">
      &nbsp;
    </div>
    <div class="col-11">
      <a class="fs-6 fw-light" href="javascript:void(0);" @click="addGB"><i class="fas fa-plus"></i> Gehaltsbestandteil hinzufuegen</a>
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

      this.children.push({
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
      var payload = [];

      for( var i in this.$refs.parts ) {
        payload.push(this.$refs.parts[i].getPayload());
      }

      return payload;
    }
  }
}
