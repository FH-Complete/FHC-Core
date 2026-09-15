export default {
	name: "timelocksMitarbeiteruid",
	props: {
		maUid: {
			type: String
		},
		days: {
			type: Number
		},
		title: {
			type: String
		}

	},
	methods: {
		link(maUid, days){
			this.$router.push({
				name: 'ZeitsperrenMa',
				params: {
					type: 'ma',
					id: maUid,
					days: days
				}
			});
		}
	},
	template: `
	<div>
		<a href="#" @click.prevent="link(maUid, days)">
			{{title}}
		</a>
	</div>
	`,
}