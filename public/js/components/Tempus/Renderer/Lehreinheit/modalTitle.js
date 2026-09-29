export default {
	props:{
		event: {
			type: Object,
			required: true,
		}
	},
	computed: {
		titel()
		{
			return this.event.lehrveranstaltung_infos.map(lehrveranstaltung => `[${lehrveranstaltung.lehrform_kurzbz}] ${lehrveranstaltung.lehrfach_bezeichnung}`).join(', ');
		},
	},
	template:  /*html*/`
		{{titel}}
		
	`
}