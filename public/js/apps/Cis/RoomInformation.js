import FhcCalendar from "../../components/Calendar/Calendar.js";

const app = Vue.createApp({
	components: {
		FhcCalendar
	},
	data() {
		return {
			stunden: [],
			events: null
		}
	},
	created() {
		axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Stundenplan/Stunden').then(res => {
			
            
            res.data.retval.forEach(std => {
				this.stunden[std.stunde] = std; // TODO(chris): geht besser
			});
			console.log("this are the loaded stunden", this.stunden)
		});

        axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Stundenplan').then(res => {
            
            let events;
            if (res.data.retval && res.data.retval.forEach) {
                res.data.retval.forEach((el, i) => {
                    el.id = i;
                    el.color = '#' + (el.farbe || 'CCCCCC');
                    el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
                    el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
                    el.title = el.lehrfach;
                    if (el.lehrform)
                        el.title += '-' + el.lehrform;
                });
                events = res.data.retval;
            }

            console.log("this are the loaded events",events)
            // TODO(chris): do we need that

        }).catch((e)=>{console.log(e,"this is the exception")})


        axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Stundenplan/RoomInformation').then(res => {
            console.log(res)
        console.log("this string got printed after the get was successfully finished")
        }).catch((e)=>{console.log(e,"this is the exception")})

       

	},
    template: /*html*/`
    <div>
    <!--initialDate="2023-5-12"-->
        <fhc-calendar  :events="events" initial-mode="week" show-weeks></fhc-calendar>
    </div>
    `,
});
app.config.unwrapInjectedRef = true;
app.mount('#content');