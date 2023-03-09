import presetable from '../../mixins/vbform/presetable.js';
import tab from './tab.js';

export default {
  template: `
  <div class="d-flex align-items-start">
    <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
      <button v-for="(child, idx) in children"
              :key="idx"
              class="nav-link"
              :class="(this.activetab === child.guioptions.id) ? 'active' : ''"
              :id="'v-pills-' + child.guioptions.id + 'tab'"
              data-bs-toggle="pill"
              :data-bs-target="'#v-pills-' + child.guioptions.id"
              type="button"
              role="tab"
              :aria-controls="'v-pills-' + child.guioptions.id"
              :aria-selected="(this.activetab === child.guioptions.id) ? 'true' : 'false'">
        {{ child.guioptions.title }}
      </button>
    </div>

    <div class="tab-content w-100" id="v-pills-tabContent">
      <component ref="parts" v-for="(child, idx) in children" :is="child.type" :key="idx" :preset="child" :activetab="activetab"></component>
    </div>
  </div>
  `,
  components: {
    "tab": tab
  },
  mixins: [
    presetable
  ],
  created: function() {
    if( this.children.length > 0 ) {
      this.activetab = this.children[0].guioptions.id;
    }
  },
  data: function() {
    return {
      activetab: ''
    }
  },
  methods: {
    getPayload: function() {
      var children = [];
      for( var i in this.$refs.parts ) {
        children.push(this.$refs.parts[i].getPayload());
      }
      var payload = {
        "type": "tabs",
        "guioptions": JSON.parse(JSON.stringify(this.preset.guioptions)),
        "children": children
      }
      return payload;
    }
  }
}
