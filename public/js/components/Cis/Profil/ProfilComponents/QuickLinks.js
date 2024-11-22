export default {
	//TODO: To be implemented
	props: {
		data: {
			type: String,
		},
		title: {
			type: String,
			required: true,
		},
		mobile: {
			type: Boolean,
			default: false,
		},
	},
	methods: {
		hideCollapse: function () {
			this.collapseOpen = false;
		},
		showCollapse: function () {
			this.collapseOpen = true;
		},
	},
	data() {
		return {
			collapseOpen: false,
		};
	},
	template: /*html*/ `
<div class="card">
    <template v-if="mobile">
        <button class="btn btn-outline-primary"  data-bs-toggle="collapse"  data-bs-target="#quickLinks" :aria-expanded="collapseOpen" aria-controls="quickLinks" >
            {{title}}
            <i class="fa " :class="collapseOpen?'fa-chevron-up':'fa-chevron-down'"></i> 
        </button>
        <div @[\`show.bs.collapse\`]="collapseOpen=true;" @[\`hide.bs.collapse\`]="collapseOpen=false;" class="mt-1 collapse" id="quickLinks">
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-action">{{$p.t('profil','zeitwuensche')}}</a>
                <a href="#" class="list-group-item list-group-item-action">{{$p.t('profil','lehrveranstaltungen')}}</a>
                <a href="#" class="list-group-item list-group-item-action ">{{$p.t('profil','zeitsperren')}}</a>
            </div>
        </div>
    </template>
    <template v-else>
        <div class="card-header">{{title}}</div>
        <div class="card-body">
            <a style="text-decoration:none" class="my-1 d-block" href="#">{{$p.t('profil','zeitwuensche')}}</a>
            <a style="text-decoration:none" class="my-1 d-block" href="#">{{$p.t('profil','lehrveranstaltungen')}}</a>
            <a style="text-decoration:none" class="my-1 d-block" href="#">{{$p.t('profil','zeitsperren')}}</a>
        </div>
    </template>
</div>`,
};
