export default {
	components: {
		paginator: primevue.paginator,
	},
	emits: ["pageUpdated"],
	props: {
		maxPageCount: {
			type: Number,
			default: 0,
		},
		page_size: {
			type: Number,
			required: true,
		},
		page: {
			type: [Number, null],
			default: null,
		},
	},
	data() {
		return {
			first: 0,
			rowsPerPageOptions: [10, 20, 30],
		};
	},
	watch: {
		page(newValue) {
			this.first = (newValue - 1) * this.$props.page_size;
		},
	},
	methods: {
		afterPageUpdated(data) {
			this.$emit("pageUpdated", { ...data, page: data.page + 1 });
			this.first = data.page * this.$props.page_size;
		},
	},
	template: /*html*/ `
	<!-- Desktop -->
	<div class="d-none d-md-block">
        <paginator 
			@page="afterPageUpdated($event)"
			v-model:first="first"
			:rows="page_size"
			:totalRecords="maxPageCount"
			:rowsPerPageOptions="rowsPerPageOptions"
		></paginator>
	</div>
	<!-- Mobile -->
	<div class="d-block d-md-none">
		<paginator
			@page="afterPageUpdated($event)"
			v-model:first="first"
			:rows="page_size"
			:totalRecords="maxPageCount"
			:rowsPerPageOptions="rowsPerPageOptions"
			template="FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink RowsPerPageDropdown"
		></paginator>
	</div>
  `,
};
