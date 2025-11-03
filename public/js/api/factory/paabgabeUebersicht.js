export default {
	getPaAbgaben(studiengang_kz, abgabetyp_kurzbz, abgabedatum, personSearchString) {
		return {
			method: 'get',
			url: '/api/frontend/v1/education/PaabgabeUebersicht/getPaAbgaben',
			params: {
				studiengang_kz: studiengang_kz, abgabetyp_kurzbz: abgabetyp_kurzbz, abgabedatum: abgabedatum, personSearchString: personSearchString
			}
		};
	},
	//~ searchPaAbgabenByPerson(searchString) {
		//~ return {
			//~ method: 'get',
			//~ url: '/api/frontend/v1/education/PaabgabeUebersicht/searchPaAbgabenByPerson',
			//~ params: { searchString: searchString }
		//~ };
	//~ },
	getStudiengaenge() {
		return {
			method: 'get',
			url: '/api/frontend/v1/education/PaabgabeUebersicht/getStudiengaenge'
		};
	},
	getTermine(studiengang_kz, abgabetyp_kurzbz) {
		return {
			method: 'get',
			url: '/api/frontend/v1/education/PaabgabeUebersicht/getTermine',
			params: { studiengang_kz: studiengang_kz, abgabetyp_kurzbz: abgabetyp_kurzbz }
		};
	},
	getPaAbgabetypen() {
		return {
			method: 'get',
			url: '/api/frontend/v1/education/PaabgabeUebersicht/getPaAbgabetypen'
		};
	}
};