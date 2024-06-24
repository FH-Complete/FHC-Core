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
            calendarWeek: new CalendarDate(new Date()),
            events_loaded:false,
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
            this.$fhcApi.factory.stundenplan.getRoomInfo('EDV_F4.26', this.weekFirstDay, this.weekLastDay).then(res =>{
                let events;
                if (res.data && res.data.forEach) {
                    res.data.forEach((el, i) => {

                        el.id = i;
                        if(el.type === 'reservierung')
                        {
                            el.color = '#' + (el.farbe || 'FFFFFF');
                        }else{
                            el.color = '#' + (el.farbe || 'CCCCCC');
                        }
                        
                        el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
                        el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
                        el.title = el.lehrfach;
                        if (el.lehrform)
                            el.title += '-' + el.lehrform;
                    });
                    
                    this.events = res.data;
                }

                // reservierungen are loaded with the stundenplan
                /* this.$fhcApi.factory.stundenplan.getReservierungen('EDV_F4.26', this.weekFirstDay, this.weekLastDay).then(res => {
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
                    this.events = [...(this.events?this.events:[]),...reservierungs_events];
                    
                    
                }); */
            });

           
        });

	},
    template: /*html*/`
    <div>
        <fhc-calendar  v-slot="{event,day}" :initialDate="currentDate" :events="events" initial-mode="week" show-weeks>
           
            <a class="text-decoration-none text-dark" href="#" :title="event.orig.title + ' - ' + event.orig.lehrfach_bez + ' [' + event.orig.ort_kurzbz+']'"   >    
                <div class="d-flex flex-column align-items-center justify-content-evenly h-100" :style="{'background-color':event.orig.color}">
                    
                        <!-- render content for stundenplan -->
                        <span >{{event.orig.topic}}</span>
                        <span v-for="gruppe in event.orig.gruppe.split('/')" :key="gruppe">{{gruppe}}</span>	
                        <span v-for="lektor in event.orig.lektor.split('/')" :key="lektor">{{lektor}}</span>
                        <!-- add the beschreibung if the event is a reservierung -->
                        <span v-if="event.orig.type === 'reservierung'">{{event.orig.beschreibung}}</span>
                        
                    
                </div>
            </a>
        </fhc-calendar>
    </div>
    `,
});
app.config.unwrapInjectedRef = true;
app.use(FhcApi);
app.use(Phrasen);
app.mount('#content');