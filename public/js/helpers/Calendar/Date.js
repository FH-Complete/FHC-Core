const CalendarDate = {
	msPerDay: 86400000, // = 1000*60*60*24

	/**
	 * Returns UTC timestam for a date
	 *
	 * @param Date			date
	 * @param boolean		stripTime
	 *
	 * @return integer
	 */
	UTC(date, stripTime) {
		if (stripTime)
			return Date.UTC(date.getFullYear(), date.getMonth(), date.getDate());
		return Date.UTC(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes());
	},
	/**
	 * Get a date as string, using locale conventions.
	 *
	 * @see https://developer.mozilla.org/de/docs/Web/JavaScript/Reference/Global_Objects/Intl/DateTimeFormat/DateTimeFormat#syntax
	 *
	 * @param Date			date
	 * @param object		options
	 * @param string		lang
	 *
	 * @return string
	 */
	format(date, options, lang) {
		return date.toLocaleString(lang, options);
	},
	/**
	 * Returns the weekday number (Date.getDay()) on which the week starts depending on the locale.
	 * This can be Saturday(6), Sunday(0) or Monday(1)
	 * 
	 * @see https://stackoverflow.com/questions/53382465/how-can-i-determine-if-week-starts-on-monday-or-sunday-based-on-locale-in-pure-j
	 * 
	 * @param string		locale
	 * 
	 * @return integer
	 */
	getWeekStart(locale) {
		locale = locale || navigator.language;
		const parts = locale.match(/^([a-z]{2,3})(?:-([a-z]{3})(?=$|-))?(?:-([a-z]{4})(?=$|-))?(?:-([a-z]{2}|\d{3})(?=$|-))?/i);

		const language_code = parts[1];
		const language_starting_Sat = ['ar','arq','arz','fa'];
		const language_starting_Sun = 'amasbndzengnguhehiidjajvkmknkolomhmlmrmtmyneomorpapssdsmsnsutatethtnurzhzu'.match(/../g);

		const region_code = parts[4];
		const region_starting_Sat = 'AEAFBHDJDZEGIQIRJOKWLYOMQASDSY'.match(/../g);
		const region_starting_Sun = 'AGARASAUBDBRBSBTBWBZCACNCODMDOETGTGUHKHNIDILINJMJPKEKHKRLAMHMMMOMTMXMZNINPPAPEPHPKPRPTPYSASGSVTHTTTWUMUSVEVIWSYEZAZW'.match(/../g);

		if (region_code) {
			if (region_starting_Sun.includes(region_code))
				return 0;
			if (region_starting_Sat.includes(region_code))
				return 6;
			return 1;
		}
		if (language_code) {
			if (language_starting_Sun.includes(language_code))
				return 0;
			if (language_starting_Sat.includes(language_code)) 
				return 6;
			return 1;
		}
		return 1;
	},
	/**
	 * Get the first day of the week according to the locale.
	 *
	 * @param Date			someDayInWeek
	 * @param string		locale
	 *
	 * @param Date
	 */
	getFirstDayOfWeek(someDayInWeek, locale) {
		const weekStart = this.getWeekStart(locale);
		const offsetFromFirstDayOfWeek = (someDayInWeek.getDay() + 7 - weekStart)%7;
		return this.addDays(someDayInWeek, -offsetFromFirstDayOfWeek);
	},
	/**
	 * Counts the weeks of a year
	 *
	 * @param integer		year
	 * @param string		locale
	 *
	 * @param integer
	 */
	countWeeksOfYear(year, locale) {
		const weekStart = this.getWeekStart(locale);
		if (weekStart == 1) {
			const weekStartOfTheYear = this.getFirstDayOfWeek(new Date(year, 0, 4), locale);
			const weekStartOfTheNextYear = this.getFirstDayOfWeek(new Date(year+1, 0, 4), locale);
			return this.getWeekNumber(weekStartOfTheYear, weekStartOfTheNextYear) - 1;
		} else {
			const weekStartOfTheYear = this.getFirstDayOfWeek(new Date(year, 0, 1), locale);
			return this.getWeekNumber(weekStartOfTheYear, new Date(year, 11, 31));
		}
	},
	/**
	 * Returns the week number and year for the date depending on the locale.
	 * 
	 * @see https://stackoverflow.com/questions/53382465/how-can-i-determine-if-week-starts-on-monday-or-sunday-based-on-locale-in-pure-j
	 * 
	 * @param Date			date
	 * @param string		locale
	 * 
	 * @return object
	 */
	getWeek(date, locale) {
		const weekStart = this.getWeekStart(locale);
		let year = date.getFullYear();

		if (weekStart == 1) { // Use ISO rules
			const month = date.getMonth();
			const day = date.getDate();
			const weekday = date.getDay();

			if (month == 11 && day > 28 && weekday && weekday <= day-28) {
				return {number: 1, year: year + 1};
			}
			if (month == 0 && day < 4 && 3-(weekday || 7) <= -day) {
				year -= 1;
			}

			const dayThatMustBeInFirstWeek = new Date(year, 0, 4);
			const weekStartOfTheYear = this.getFirstDayOfWeek(dayThatMustBeInFirstWeek, locale);
			const number = this.getWeekNumber(weekStartOfTheYear, date);

			return { number, year };
		} else { // Either Northamerican or Arabic rules
			const dayThatMustBeInFirstWeek = new Date(year, 0, 1);
			const weekStartOfTheYear = this.getFirstDayOfWeek(dayThatMustBeInFirstWeek, locale);
			const number = this.getWeekNumber(weekStartOfTheYear, date);

			return { number, year };
		}
	},
	/**
	 * Returns all days in a week.
	 * 
	 * @param integer		number
	 * @param integer		year
	 * @param string		locale
	 * 
	 * @return array
	 */
	getDaysInWeek(number, year, locale) {
		const weekStart = this.getWeekStart(locale);
		let dayThatMustBeInFirstWeek;
		if (weekStart == 1) { // Use ISO rules
			dayThatMustBeInFirstWeek = new Date(year, 0, 4);
		} else { // Either Northamerican or Arabic rules
			dayThatMustBeInFirstWeek = new Date(year, 0, 1);
		}
		const weekStartOfTheYear = this.getFirstDayOfWeek(dayThatMustBeInFirstWeek, locale);
		const firstDayInWeek = this.addDays(weekStartOfTheYear, (number-1) * 7);
		
		const days = [
			firstDayInWeek
		];
		for (var i = 1; i < 7; i++)
			days.push(this.addDays(firstDayInWeek, i));

		return days;
	},
	/**
	 * Counts the weeks since the start of the year and returns the week number starting with 1.
	 *
	 * @param Date			weekStartOfTheYear
	 * @param Date			target
	 *
	 * @return integer
	 */
	getWeekNumber(weekStartOfTheYear, target) {
		// NOTE(chris): use UTC time for difference because there is a
		// possibility one of the dates is in daylightsaving time while the
		// other is not
		const start = Date.UTC(target.getFullYear(), target.getMonth(), target.getDate(), 0, 0, 0);
		const end = Date.UTC(weekStartOfTheYear.getFullYear(), weekStartOfTheYear.getMonth(), weekStartOfTheYear.getDate(), 0, 0, 0);
		const diffInMillisec = start - end;

		const fullDays = Math.floor(diffInMillisec / this.msPerDay);

		return Math.ceil((fullDays + 1) / 7);
	},
	/**
	 * Adds or substracts years from a given date and returns the result.
	 * Returns the 28th Feb if the result would be the 29th in an
	 * non-leap-year.
	 *
	 * @param Date			date
	 * @param integer		years
	 *
	 * @return Date
	 */
	addYears(date, years) {
		const oldYear = date.getFullYear();
		const oldMonth = date.getMonth();
		const oldDay = date.getDate();
		
		if (oldMonth == 1 && oldDay == 29)
			return new Date(oldYear + years, 2, 0);
		
		return new Date(oldYear + years, oldMonth, oldDay);
	},
	/**
	 * Adds or substracts months from a given date and returns the result.
	 * Returns the last of the month if the resulting month has fewer days
	 * than the original month.
	 *
	 * @param Date			date
	 * @param integer		months
	 *
	 * @return Date
	 */
	addMonths(date, months) {
		const oldYear = date.getFullYear();
		const oldMonth = date.getMonth();
		const oldDay = date.getDate();
		let years = 0;
		
		if (oldMonth + months < 0) {
			years = Math.floor((oldMonth + months) / 12);
			months += years * -12;
		} else if (oldMonth + months > 11) {
			years = Math.floor((oldMonth + months) / 12);
		}
		
		const newMonth = (oldMonth + months) % 12;
		const newYear = oldYear + years;
		
		if (oldDay > 28) {
			const test = new Date(newYear, newMonth+1, 0);

			if (test.getDate() <= oldDay)
				return test;
		}

		return new Date(newYear, newMonth, oldDay);
	},
	/**
	 * Adds or substracts days from a given date and returns the result.
	 *
	 * @param Date			date
	 * @param integer		days
	 *
	 * @return Date
	 */
	addDays(date, days) {
		return new Date(date.getFullYear(), date.getMonth(), date.getDate() + days);
	}
};

export default CalendarDate;
