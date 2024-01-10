export default {
  props: [
    'preset'
  ],
  template: `
  <div v-for="(id, label) in preset.guioptions.tabs" :key="id" class="sections">
    <div>{{label}}</div>
  </div>
  `
}
