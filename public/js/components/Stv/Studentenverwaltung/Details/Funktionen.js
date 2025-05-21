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
        <h4>{{ $p.t('person', 'funktionen') }}</h4>

		<person-functions
			:readonlyMode="false"
			:personID="modelValue.person_id"
			:personUID="modelValue.uid"
			:showDvCompany="true"
			:saveFunctionAsCopy="true"
			:filterOeStudent="true"
			:oeDropdownAutocomplete="true"
			:stylePv21="false"
		>
		</person-functions>

	</div>`,
};
