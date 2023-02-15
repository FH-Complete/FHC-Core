import vertragsbestandteilhelper from '../../components/vbform/vertragsbestandteilhelper.js';

Vue.createApp({
  template: `
  <div class="container-fluid">
    <h1>{{ title }}</h1>
    <vertragsbestandteilhelper></vertragsbestandteilhelper>
  </div>
  `,
  data: function() {
    return {
      "title": "Vertragsbestandteil Form",
    };
  },
  components: {
    'vertragsbestandteilhelper': vertragsbestandteilhelper
  },
  created: function() {
  },
  methods: {
  }
}).mount('#main');
