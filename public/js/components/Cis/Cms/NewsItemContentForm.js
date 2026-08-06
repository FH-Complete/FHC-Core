
import FormInput from "../../Form/Input.js";

export default {
  name: "NewsItemContentForm",
  components: {
    FormInput,
  },
  	props: {
		config: {
			type: Object,
			default: () => ({})
		}
	},
  methods: {
    initTinyMCE() {
			const vm = this;
			tinymce.init({
				target: this.$refs.editor.$refs.input, //Important: not selector: to enable multiple import of component
				//height: 800,
				//plugins: ['lists'],
				//toolbar: " blocks | bold italic underline | alignleft aligncenter alignright alignjustify",
				toolbar: 'styleselect | bold italic underline | alignleft aligncenter alignright alignjustify',
				style_formats: [
					{title: 'Blocks', block: 'div'},
					{title: 'Paragraph', block: 'p'},
					{title: 'Heading 1', block: 'h1'},
					{title: 'Heading 2', block: 'h2'},
					{title: 'Heading 3', block: 'h3'},
					{title: 'Heading 4', block: 'h4'},
					{title: 'Heading 5', block: 'h5'},
					{title: 'Heading 6', block: 'h6'},
				],
				autoresize_bottom_margin: 16,

				setup: (editor) => {
					vm.editor = editor;

					editor.on('input', () => {
						const newContent = editor.getContent();
						vm.notizData.text = newContent;
					});
				},
			});
		},
  },
  mounted() {
    this.initTinyMCE();
  },
  template: /*html*/ `
	<div :class="{'pb-3': isMobile}" class="overflow-x-hidden">
    <div class="d-flex flex-column flex-md-row align-items-md-end gap-3">
      <form-input
        type="text"
        :label="$p.t('wawi/nummer')"
        name="nummer"
        >
      </form-input>
      <form-input
        type="text"
        :label="$p.t('wawi/nummer')"
        name="nummer"
        >
      </form-input>
       <form-input
        type="checkbox"
        :label="$p.t('wawi/nummer')"
        name="nummer"
        >
      </form-input>
    </div>
    <form-input
       ref="editor"
          :label="$p.t('global','text')  + ' *'"
          type="textarea" 
          name="text"
          rows="5"
          cols="75"
        >
      >
    </form-input>

</div>
    `,
};
