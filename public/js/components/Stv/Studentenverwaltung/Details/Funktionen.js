import PersonFunctions from "../../../Funktionen/Funktionen.js";

export default {
  components: {
    PersonFunctions,
  },
  props: {
    modelValue: Object,
  },
  template: `
	<div class="stv-details-functions h-100 d-flex flex-column">

		<person-functions
			:readonlyMode="false"
			:personID="modelValue.person_id"
			:personUID="modelValue.uid"
			:showDvCompany="false"
			:saveFunctionAsCopy="true"
			:stylePv21="false"
		>
		</person-functions>

	</div>`,
};
