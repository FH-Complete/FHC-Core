export function getTempusSearchbarOptions(self)
{
	return {
		origin: 'tempus',
		cssclass: "position-relative",
		calcheightonly: true,
		types: [
			//"student",
			"raum",
			"mitarbeiter",
			"mitarbeiter_ohne_zuordnung"
		],
		actions: {
			raum: {
				defaultaction: {
					type: "function",
					action: self.setOrt
				},
				childactions: [
					{
						label: "zum Filter hinzufügen",
						icon: "fas fa-plus",
						type: "function",
						action: (data) => {
							self.addToFilter(data, 'ort');
						}
					},
				]
			},
			employee: {
				defaultaction: {
					type: "function",
					action: (data) => {
						self.setEmp(data);
					}
				},
				childactions: [
					{
						label: "zum Filter hinzufügen",
						icon: "fas fa-plus",
						type: "function",
						action: (data) => {
							self.addToFilter(data, 'mitarbeiter');
						}
					},
					{
						label: "zum Course Picker hinzufügen",
						icon: "fas fa-plus",
						type: "function",
						action: (data) => {
							self.addToCoursePicker(data);
						}
					},
				]
			},
		}
	};
}