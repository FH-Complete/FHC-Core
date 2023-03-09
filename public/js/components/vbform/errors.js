export default {
  template: `
  <div v-if="errors.length > 0" class="row g-2">
    <div class="col-12">
        <div class="alert py-1 alert-danger" v-for="(error, idx) in errors" :key="idx">{{ error }}</div>
    </div>
  </div>
  `,
  props: [
    'errors'
  ]
}
