import gehaltsbestandteil from './gehaltsbestandteil.js';

export default {
  template: `
  <div class="row g-2 py-2">
    <div class="col">
      <a href="javascript:void(0);" @click="addGB"><i class="fas fa-plus"></i> Gehaltsbestandteil hinzufuegen</a>
    </div>
  </div>
  <div class="row">
    <div class="col">
        <gehaltsbestandteil v-bind:ref="gb.id" v-for="gb in gbs" v-bind:id="gb.id" @removeGB="removeGB"></gehaltsbestandteil>
    </div>
  </div>
  `,
  data: function() {
    return {
      payload: [],
      gbs: [],
    };
  },
  components: {
    'gehaltsbestandteil': gehaltsbestandteil,
  },
  methods: {
    addGB: function(e) {
      e.preventDefault();
      e.stopPropagation();

      var gbid = 'testgb' + (this.gbs.length + 1);
      this.gbs.push({
        id: gbid
      });
    },
    removeGB: function(payload) {
      var gbs = this.gbs.filter(function(gb) {
        return gb.id !== payload.id;
      });
      this.gbs = gbs;
    },
    getPayload: function() {
      var gbs = this.gbs;
      var that = this;

      this.payload = [];
      gbs.forEach(function(gb) {
        that.payload.push(that.$refs[gb.id][0].getPayload());
      });

      return this.payload;
    }
  }
}
