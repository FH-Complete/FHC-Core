class CalendarDates{
	
	static subscribers_array = [];
	
	static subscribe(subscriber){
		this.subscribers_array.push(subscriber);
	}
	static unsubscribe(subscriber){
		this.subscribers_array = this.subscribers_array.filter(sub => !sub.compare(subscriber));
	}
	static cleanup(){
		this.subscribers_array.forEach(sub => { sub.cleanup() });
	}
}

export default CalendarDates;