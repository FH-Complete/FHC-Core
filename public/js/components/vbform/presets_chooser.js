export default {
  template: `
  <div class="row g-2 py-2">
    <div class="col-4">

      <select v-model="selectedpresetidx" @change="selectpreset">
        <option v-for="(preset, idx) in presets" :key="idx"
                :value="idx"
                :selected="idx === selectedpresetidx">
                  {{ preset.guioptions.label }}
                </option>
      </select>

    </div>
    <div class="col-8">&nbsp;</div>
  </div>
  `,
  props:[
    'presets'
  ],
  data: function() {
    return {
      selectedpresetidx: 1
    }
  },
  emits: [
    "presetselected"
  ],
  methods: {
    selectpreset: function() {
      var preset = this.presets[this.selectedpresetidx];
      this.$emit("presetselected", preset);
    }
  }
}
