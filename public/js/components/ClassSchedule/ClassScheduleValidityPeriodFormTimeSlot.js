import { CoreFilterCmpt } from "../filter/Filter.js";
import ApiClassSchedule from "../../../js/api/factory/classSchedule.js";

import BsModal from "../Bootstrap/Modal.js";
import CoreForm from "../Form/Form.js";
import FormInput from "../Form/Input.js";

export default {
  name: "ClassScheduleValidityPeriodForm",
  components: {
    BsModal,
    CoreForm,
    FormInput,
    CoreFilterCmpt,
  },
  props: {
    modelValue: Object,
    classTimeSlotTypes: Array,
  },
  emits: ["update:modelValue", "removeClassTimeSlot"],
  data: () => {
    return {
      classTimeSlots: [],
    };
  },
  computed: {
    selectedClassTimeSlotType() {
      return this.classTimeSlotTypes.find(
        (type) =>
          type.unterrichtszeitentyp_kurzbz ===
          this.classTimeSlot.classTimeSlotTypeShortcode,
      );
    },
    classTimeSlot: {
      get() {
        return this.modelValue;
      },
      set(value) {
        this.$emit("update:modelValue", value);
      },
    },
  },
  methods: {},
  template: `
  <div class="d-flex align-items-center mb-2">
    <div
      :style='[
        {backgroundColor: selectedClassTimeSlotType ? selectedClassTimeSlotType.hintergrundfarbe : "#fff"}
      ]' 
      class="p-2 shadow d-flex flex-column gap-2 position-relative">
      <a class="text-classTimeSlot position-absolute top-0 end-0 p-2 z-3" @click="$emit('removeClassTimeSlot', 'monday', classTimeSlot.identifier)"><i class="fa fa-trash text-danger"></i></a>
      <form-input
          type="select"
          name="classTimeSlotType"  
          :label="$p.t('ui/classTimeSlotType')"
          v-model="classTimeSlot.classTimeSlotTypeShortcode"
          >
          <option
            v-for="classTimeSlotType in $props.classTimeSlotTypes"
            :key="classTimeSlotType.unterrichtszeitentyp_kurzbz"
            :value="classTimeSlotType.unterrichtszeitentyp_kurzbz"
            >
            {{classTimeSlotType.unterrichtszeitentyp_kurzbz}} - {{classTimeSlotType.bezeichnung_mehrsprachig[0].value}} / ({{classTimeSlotType.bezeichnung_mehrsprachig[1].value}})
          </option>
        </form-input>
      <div class="d-flex rounded align-items-center justify-content-between gap-1">
        <form-input
          type="time"
          name="classTimeSlotValidityPeriodFrom"  
          v-model="classTimeSlot.startTime"
          
          />
        <div > - </div>
        <form-input
          type="time"
          name="classTimeSlotValidityPeriodTo"  
          v-model="classTimeSlot.endTime"
          
          />
      </div> 
    </div>
  </div>
  `,
};
