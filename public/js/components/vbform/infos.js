export default {
  template: `
  <div v-if="infos.length > 0" class="row g-2">
    <div class="col-12">
        <div class="alert py-1 alert-info" v-for="(error, idx) in infos" :key="idx">{{ error }}</div>
    </div>
  </div>
  `,
  props: [
    'infos'
  ]
}
