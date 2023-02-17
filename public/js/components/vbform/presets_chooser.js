import vertragsbestandteilhelper from './vertragsbestandteilhelper.js';
import debug_viewer from './debug_viewer.js';

export default {
  template: `
  <div class="row g-2 py-2">
    <div class="col-2">

      <div class="d-flex align-items-start">
        <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
          <button v-for="(preset, idx) in presets" @click="selectpreset(idx)" class="nav-link"
            v-bind:class="isactive(idx)" data-bs-toggle="pill" type="button"
            :title="preset.guioptions.description">{{ preset.guioptions.label }}</button>
        </div>
      </div>

    </div>

    <div class="col-6">
      <vertragsbestandteilhelper v-bind:preset="selectedpreset" @vbhjsonready="process_json"></vertragsbestandteilhelper>
    </div>

    <div class="col-4">
      <debug_viewer v-bind:text="vbhjson"></debug_viewer>
    </div>
  </div>
  `,
  props:[
    'presets'
  ],
  data: function() {
    return {
      selectedpresetidx: 0,
      selectedpreset: [],
      vbhjson: ''
    }
  },
  components: {
    'vertragsbestandteilhelper': vertragsbestandteilhelper,
    'debug_viewer': debug_viewer
  },
  methods: {
    selectpreset: function(idx) {
      if( typeof this.presets[idx] !== 'undefined' ) {
        this.seletedpresetidx = idx;
        this.selectedpreset = this.presets[idx].vbs;
      }
    },
    isactive: function(idx) {
      return (idx === this.selectedpresetidx) ? 'active' : '';
    },
    process_json: function(payload) {
      this.vbhjson = payload;
    }
  },
  computed: {
  }
}
