
import CoreForm from "../../Form/Form.js";
import FormInput from "../../Form/Input.js";
import Tabs from "../../Tabs.js";

export default {
  name: "NewsItemForm",
  components: {
    CoreForm,
    FormInput,
    Tabs,
  },
  data() {
    return {
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
  methods: {
  },
  created() {
  },
  template: /*html*/ `
	<div :class="{'pb-3': isMobile}" class="overflow-x-hidden">
  		test
	</div>
    `,
};
