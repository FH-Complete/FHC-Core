import FhcCalendar from "../../components/Calendar/Calendar.js";

const app = Vue.createApp({
	components: {
		FhcCalendar
	},
	data() {
		return {
			stunden: [],
			events: null,
            testDate: new Date('2024-05-21'),
		}
	},
	created() {
		axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Stundenplan/Stunden').then(res => {
			
            
            res.data.retval.forEach(std => {
				this.stunden[std.stunde] = std; // TODO(chris): geht besser
			});
			console.log("this are the loaded stunden", this.stunden)

            axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Stundenplan/RoomInformation').then(res => {
                let events;
                console.log(" this is the res of the api call room information",res);
                if (res.data && res.data.forEach) {
                    res.data.forEach((el, i) => {

                        console.log(el,"this is the element that gets changed")
                        el.id = i;
                        el.color = '#' + (el.farbe || 'CCCCCC');
                        el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
                        el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
                        el.title = el.lehrfach;
                        if (el.lehrform)
                            el.title += '-' + el.lehrform;
                    });
                    events = res.data;
                    //console.log("this are the room events",events)
                    //this.events = events;
                }
            }).catch((e)=>{console.log(e,"this is the exception")})
		});

        axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Stundenplan').then(res => {
            
            let events;
            console.log(" this is the res of the api call stundenplan",res);
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
                console.log("this are the events of the stundenplan",events)
            }

            // TODO(chris): do we need that

        }).catch((e)=>{console.log(e,"this is the exception")}) 


        

       

	},
    template: /*html*/`
    <div>
    <!--initialDate="2023-5-12"-->
        <fhc-calendar :initialDate="testDate" :events="events" initial-mode="week" show-weeks></fhc-calendar>
    </div>
    `,
});
app.config.unwrapInjectedRef = true;
app.mount('#content');