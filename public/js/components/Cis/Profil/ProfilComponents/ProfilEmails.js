export default {
	data() {
		return {};
	},
	props: {
		title: {
			type: String,
			required: true,
		},
		data: {
			type: Array,
		},
	},
	template: /*html*/ `
<div class="card ">
    <div class="card-header">
        {{title}}
    </div>
    <div class="card-body">
        <!-- HIER SIND DIE EMAILS -->
        <div  class="gy-3 row justify-content-center ">
            <div v-for="email in data" class="col-12 ">
                <div class="row align-items-center">
                    <div class="col-1 text-center">
                        <i class="fa-solid fa-envelope" style="color:rgb(0, 100, 156)"></i>
                    </div>
                    <div class="col-11">
                        <div class="form-underline">
                            <div class="form-underline-titel">{{email.type}}</div>
                            <a :href="'mailto:'+email.email" class="form-underline-content">{{email.email}} </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>`,
};
