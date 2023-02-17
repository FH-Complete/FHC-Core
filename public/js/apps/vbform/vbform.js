import presets_chooser from '../../components/vbform/presets_chooser.js';
import presets from './presets.js';

Vue.createApp({
  template: `
  <div class="container-fluid">
    <h1>{{ title }}</h1>
    <presets_chooser v-bind:presets="presets"></presets_chooser>
  </div>
  `,
  data: function() {
    return {
      "title": "Vertragsbestandteil Form",
      presets: presets
    };
  },
  components: {
    'presets_chooser': presets_chooser
  },
  created: function() {
  },
  methods: {
  },
  computed: {
  }
}).mount('#main');
