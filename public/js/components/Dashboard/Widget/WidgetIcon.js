export default {
	data(){
		return {

		}
	},
	props:{
		widget:{
			type:Object,
			required:true,
		}
	},
	methods:{
		path(src) {
			if (src[0] == '/')
				return FHC_JS_DATA_STORAGE_OBJECT.app_root + src;
			return src;
		}
	},
	emits:["select"],
	template: /*html */`
	<div class="card h-100" @click="$emit('select',widget.widget_id);">
		<img class="card-img-top" :src="path(widget.setup.icon)" :alt="'pictogram for ' + (widget.setup.name || widget.widget_kurzbz)">
		<div class="card-body">
			<h5 class="card-title">{{ widget.setup.name || widget.widget_kurzbz }}</h5>
			<p class="card-text">{{ widget.beschreibung }}</p>
		</div>
	</div>`,
}