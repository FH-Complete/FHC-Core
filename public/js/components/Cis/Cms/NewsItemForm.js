import CoreForm from "../../Form/Form.js";
import FormInput from "../../Form/Input.js";
import FhcTabs from "../../Tabs.js";

const BASE_COMPONENT_URL =
  "https://c3p0.dev.technikum-wien.at/ma1433/core/FHC-Core/public/js/components/Cis/Cms/";

export default {
  name: "NewsItemForm",
  components: {
    CoreForm,
    FormInput,
    FhcTabs,
  },
  data() {
    return {
      contentFormItems: {
        germanContentForm: {
          title: "German Content Form",
          component: BASE_COMPONENT_URL + "NewsItemContentForm.js?123",
          config: {
            language: "de",
            type: "news",
          },
          key: "germanContentForm",
        },
        englishContentForm: {
          title: "English Content Form",
          component: BASE_COMPONENT_URL + "NewsItemContentForm.js?1233",
          config: {
            language: "en",
            type: "news",
          },
          key: "englishContentForm",
        },
      },
    };
  },
  watch: {
    "$p.user_language.value": function (sprache) {
      this.fetchNews();
    },
  },
  computed: {
    sprache: function () {
      return this.$p.user_language.value;
    },
  },
  methods: {},
  created() {},
  template: /*html*/ `
	<div :class="{'pb-3': isMobile}" class="overflow-x-hidden">
  		<h2 ref="newsPageHeading" class="fhc-primary-color">News Form</h2>
		<div>
			<core-form>
				<div class="d-flex flex-row flex-md-row align-items-md-end gap-3">
					<div class="d-flex flex-row flex-md-row align-items-md-end gap-3">
						<form-input
						:label="$p.t('ui', 'validityPeriod') + ' ' + $p.t('global', 'bis')"
						:teleport="true"
						:enable-time-picker="false"
						type="datePicker"
						name="validityPeriodTo"  
						format="dd.MM.yyyy"
						auto-apply
						/>
						<form-input
						:label="$p.t('ui', 'validityPeriod') + ' ' + $p.t('global', 'bis')"
						:teleport="true"
						:enable-time-picker="false"
						type="datePicker"
						name="validityPeriodTo"  
						format="dd.MM.yyyy"
						auto-apply
						/>
					</div>
					<div class="d-flex flex-row flex-md-row align-items-md-end gap-3">
						<form-input
							:label="$capitalize($p.t('lehre/studiensemester'))"
							:suggestions="filteredSemesters"
							:optionValue="(option) => option.value"
							:optionLabel="(option) => option.label"
							@complete="filterSemesters($event)"
							type="autocomplete"
							name="selectedSemester"
							dropdown 
							forceSelection
							>
						</form-input>
						<form-input
							:label="$capitalize($p.t('lehre/studiensemester'))"
							:suggestions="filteredSemesters"
							:optionValue="(option) => option.value"
							:optionLabel="(option) => option.label"
							@complete="filterSemesters($event)"
							type="autocomplete"
							name="selectedSemester"
							dropdown 
							forceSelection
							>
						</form-input>
					</div>
  				</div>
				<fhc-tabs
					ref="tabs" 
					:useprimevue="true"
					:config="contentFormItems"
					style="flex: 1 1 0%; height: 0%"
					class="mt-3"
					>
				</fhc-tabs>
			</core-form>
		</div>
	</div>
    `,
};
