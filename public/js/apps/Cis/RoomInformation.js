import FhcCalendar from "../../components/Calendar/Calendar.js";
import CalendarDate from "../../composables/CalendarDate.js";
import FhcApi from "../../plugin/FhcApi.js";

const app = Vue.createApp({
	components: {
		FhcCalendar
	},
	data() {
		return {
			stunden: [],
			events: null,
            calendarWeek: new CalendarDate(new Date("2024-06-06")),
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
                }
            });

            this.$fhcApi.factory.stundenplan.getReservierungen('SEM_E0.04', this.weekFirstDay, this.weekLastDay).then(res => {
                if (res.data && res.data.forEach) {
                    res.data.forEach((el, i) => {
                        el.reservierung = true;
                        el.color = '#CCCCCC';
                        el.start = new Date(el.datum + ' ' + this.stunden[el.stunde].beginn);
                        el.end = new Date(el.datum + ' ' + this.stunden[el.stunde].ende);
                        el.title = el.titel;
                        if (el.lehrform)
                            el.title += '-' + el.lehrform;
                    });
                    
                }

                let reservierungs_events = res.data;
                console.log(reservierungs_events, " this are the reserverungs event")
                this.events = [...this.events,...reservierungs_events];
                
            });
        });

	},
    template: /*html*/`
    <div>
        <fhc-calendar v-slot="{event}" :initialDate="currentDate" :events="events" initial-mode="week" show-weeks>
            <div class="d-flex flex-column align-items-center justify-content-evenly h-100">
                

                <span>{{event.orig.reservierung? event.orig.title :event.orig.lv_info}}</span>	
                <span v-if="event.orig.reservierung">{{'this is a reservierung'}}</span>
                <span v-else v-for="(item, index) in event.orig.stg.split('/')" :key="index">{{item}}</span>
                <span>{{event.orig.reservierung? event.orig.uid : event.orig.lektor}}</span>
             
			</div>
        </fhc-calendar>
    </div>
    `,
});
app.config.unwrapInjectedRef = true;
app.use(FhcApi);
app.mount('#content');