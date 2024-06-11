import FhcCalendar from "../../components/Calendar/Calendar.js";
import CalendarDate from "../../composables/CalendarDate.js";
import FhcApi from "../../plugin/FhcApi.js";
import Phrasen from "../../plugin/Phrasen.js";
const app = Vue.createApp({
	components: {
		FhcCalendar
	},
	data() {
		return {
			stunden: [],
			events: null,
            calendarWeek: new CalendarDate(new Date("2024-05-07")),
            reservierungenLoaded:false,
            stundenplanLoaded:false,
        }
	},
    computed:{
        currentDate: function(){
            return new Date(this.calendarWeek.y, this.calendarWeek.m, this.calendarWeek.d);
        },
        weekFirstDay: function(){
            return this.calendarDateToString(this.calendarWeek.cdFirstDayOfWeek);
        },
        weekLastDay: function(){
            return this.calendarDateToString(this.calendarWeek.cdLastDayOfWeek);
        },
    },
    methods:{
        // returns the string YYYY-MM-DD if param is instance of CalendarDate and null otherwise
        calendarDateToString: function(calendarDate){
            
            return calendarDate instanceof CalendarDate? 
            [calendarDate.y, calendarDate.m+1, calendarDate.d].join('-'):
            null; 
            
        },
        
    },
	created() {

        this.$fhcApi.factory.stundenplan.getStunden().then(res =>{
            res.data.forEach(std => {
				this.stunden[std.stunde] = std; // TODO(chris): geht besser
			});


           // old testing room EDV_A6.09
            this.$fhcApi.factory.stundenplan.getRoomInfo('SEM_E0.04', this.weekFirstDay, this.weekLastDay).then(res =>{
                let events;
                if (res.data && res.data.forEach) {
                    res.data.forEach((el, i) => {

                        el.id = i;
                        el.color = '#' + (el.farbe || 'CCCCCC');
                        el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
                        el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
                        el.title = el.lehrfach;
                        if (el.lehrform)
                            el.title += '-' + el.lehrform;
                    });
                    
                    this.events = res.data;
                    this.stundenplanLoaded = true;
                    console.log(this.events,"this are the events")
                }
            });

           /*  this.$fhcApi.factory.stundenplan.getReservierungen('EDV_F4.26', this.weekFirstDay, this.weekLastDay).then(res => {
                if (res.data && res.data.forEach) {
                    res.data.forEach((el, i) => {
                        el.reservierung = true;
                        el.color = '#' + (el.farbe || 'ffffff');
                        el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
                        el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
                        el.title = el.titel;
                        if (el.lehrform)
                            el.title += '-' + el.lehrform;
                    });
                    
                }

                let reservierungs_events = res.data;

                // adding the last reservierung twice for testing purposes
                let last_reservierung=Object.assign({}, reservierungs_events[reservierungs_events.length-1]);
                last_reservierung.person_kurzbz="drSimml";
                reservierungs_events.push(last_reservierung);

                console.log(reservierungs_events, " this are the reserverungs event")
                this.events = [...(this.events?this.events:[]),...reservierungs_events];
                this.reservierungenLoaded=true;
                console.log(reservierungs_events,"this are the reservierungs events")
            }); */
        });

	},
    template: /*html*/`
    <div>
        <fhc-calendar v-if="stundenplanLoaded || reservierungenLoaded" v-slot="{event,day}" :initialDate="currentDate" :events="events" initial-mode="week" show-weeks>
           
           <template v-if="event.orig.eintrags_type != 'reservierungs_eintrag'">
            <a class="text-decoration-none text-dark" href="#" :title="event.orig.title + ' - ' + event.orig.lehrfach_bez + ' [' + event.orig.ort_kurzbz+']'"   >    
                <div class="d-flex flex-column align-items-center justify-content-evenly h-100" :style="{'background-color':event.orig.color}">
                    <span>{{event.orig.reservierung? event.orig.title :event.orig.lv_info}}</span>	
                    <span v-if="event.orig.reservierung">{{event.orig.stg}}</span>
                    <span v-else v-for="(item, index) in event.orig.stg.split('/')" :key="index">{{item}}</span>
                    <span>{{event.orig.reservierung? event.orig.person_kurzbz : event.orig.lektor}}</span>
                </div>
            </a>
            </template>
            <p v-else>this is a reservierung</p>
        </fhc-calendar>
    </div>
    `,
});
app.config.unwrapInjectedRef = true;
app.use(FhcApi);
app.use(Phrasen);
app.mount('#content');