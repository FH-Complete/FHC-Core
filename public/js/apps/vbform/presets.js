import uuid from '../../helpers/vbform/uuid.js';

export default [
  {
    type: 'preset',
    guioptions: {
      id: 'leer',
      label: 'Leer',
      description: 'keine vordefinierten Vertrags- und Gehaltsbestandteile. Alles kann/muss manuell angelegt werden.'
    },
    data: {
      dienstverhaeltnisid: null
    },
    vbs: []
  },
  {
    type: 'preset',
    guioptions: {
      id: 'neustd',
      label: 'Neuanlage Standard DV',
      description: 'Standard Dienstvertrag Vorlage'
    },
    data: {
      dienstverhaeltnisid: null,
      unternehmen: 'fhtw',
      vertragsart_kurzbz: 'echterDV'
    },
    vbs: [
      {
        type: 'vertragsbestandteilstunden',
        guioptions: {
          id: uuid.get_uuid(),
          removable: false
        },
        gbs: [
          {
            type: 'gehaltsbestandteil',
            guioptions: {
              id: uuid.get_uuid(),
              removable: false
            },
            data: {
              gehaltstyp: 'basis',
              valorisierung: true
            }
          }
        ]
      },
      {
        type: 'vertragsbestandteilzeitaufzeichnung',
        guioptions: {
          id: uuid.get_uuid(),
          removable: false
        },
        data: {
          zeitaufzeichnung: true,
          azgrelevant: true,
          homeoffice: true
        }
      },
      {
        type: 'vertragsbestandteilkuendigungsfrist',
        guioptions: {
          id: uuid.get_uuid(),
          removable: false
        },
        data: {
          arbeitgeber_frist: 6,
          arbeitnehmer_frist: 4
        }
      },
      {
        type: 'vertragsbestandteilfunktion',
        guioptions: {
          id: uuid.get_uuid(),
          removable: false
        }
      }
    ]
  },
  {
    type: 'preset',
    guioptions: {
      id: 'allin',
      label: 'AllIn',
      description: 'AllIn Vertrag'
    },
    data: {
      dienstverhaeltnisid: null
    },
    vbs: [
      {
        type: 'vertragsbestandteilstunden',
        guioptions: {
          id: uuid.get_uuid(),
          removable: false
        },
        gbs: [
          {
            type: 'gehaltsbestandteil',
            guioptions: {
              id: uuid.get_uuid(),
              removable: false
            },
            data: {
              gehaltstyp: 'grund',
              valorisierung: true
            }
          }
        ]
      },
      {
        type: 'vertragsbestandteilzeitaufzeichnung',
        guioptions: {
          id: uuid.get_uuid(),
          removable: false
        },
        data: {
          zeitaufzeichnung: true,
          azgrelevant: false,
          homeoffice: true
        }
      },
      {
        type: 'vertragsbestandteilfreitext',
        guioptions: {
          id: uuid.get_uuid(),
          removable: false,
        },
        data: {
          freitexttyp: 'allin',
          titel: 'AllIn Vereinbarung',
          freitext: 'Es wird AllIn vereinbart.'
        },
        gbs: [
          {
            type: 'gehaltsbestandteil',
            guioptions: {
              id: uuid.get_uuid(),
              removable: false
            },
            data: {
              gehaltstyp: 'zulage',
              valorisierung: false
            }
          }
        ]
      }
    ]
  },
  {
    "type": "preset",
    "guioptions": {
      "id": "savedallin",
      "label": "Test Zwischenspeichern",
      "description": "generiertes JSON aus ausgeflltem Formular als POC für das Zwischenspeichern"
    },
    "data": {
      "dienstverhaeltnisid": 135,
      "gueltigkeit": {
        "guioptions": {
          "sharedstatemode": "set"
        },
        "data": {
          "gueltig_ab": "01.03.2023",
          "gueltig_bis": ""
        }
      }
    },
    "vbs": [
      {
        "type": "vertragsbestandteilstunden",
        "guioptions": {
          "id": "44c4d3bc-ee1f-4edf-8b2a-82736e09d287",
          "removable": false
        },
        "data": {
          "stunden": "38,5",
          "gueltigkeit": {
            "guioptions": {
              "sharedstatemode": "reflect"
            },
            "data": {
              "gueltig_ab": "01.03.2023",
              "gueltig_bis": ""
            }
          }
        },
        "gbs": [
          {
            "type": "gehaltsbestandteil",
            "guioptions": {
              "id": "cc38c689-5c79-4a82-899a-000fd77ed582",
              "removable": false
            },
            "data": {
              "gehaltstyp": "grund",
              "betrag": "3500",
              "gueltigkeit": {
                "guioptions": {
                  "sharedstatemode": "reflect"
                },
                "data": {
                  "gueltig_ab": "01.03.2023",
                  "gueltig_bis": ""
                }
              },
              "valorisierung": true
            }
          }
        ]
      },
      {
        "type": "vertragsbestandteilzeitaufzeichnung",
        "guioptions": {
          "id": "b55a558e-458c-4ea5-bebf-dd9cbe247ca6",
          "removable": false
        },
        "data": {
          "zeitaufzeichnung": true,
          "azgrelevant": false,
          "homeoffice": true,
          "gueltigkeit": {
            "guioptions": {
              "sharedstatemode": "reflect"
            },
            "data": {
              "gueltig_ab": "01.03.2023",
              "gueltig_bis": ""
            }
          }
        }
      },
      {
        "type": "vertragsbestandteilfreitext",
        "guioptions": {
          "id": "6f5756d9-2128-48b8-8954-408f96816b22",
          "removable": false
        },
        "data": {
          "freitexttyp": "allin",
          "titel": "AllIn Vereinbarung",
          "freitext": "Es wird AllIn vereinbart.",
          "kuendigungsrelevant": "",
          "gueltigkeit": {
            "guioptions": {
              "sharedstatemode": "reflect"
            },
            "data": {
              "gueltig_ab": "01.03.2023",
              "gueltig_bis": ""
            }
          }
        },
        "gbs": [
          {
            "type": "gehaltsbestandteil",
            "guioptions": {
              "id": "d0c8946f-1c64-4981-9c15-e09ac5c9ba0a",
              "removable": false
            },
            "data": {
              "gehaltstyp": "zulage",
              "betrag": "500",
              "gueltigkeit": {
                "guioptions": {
                  "sharedstatemode": "reflect"
                },
                "data": {
                  "gueltig_ab": "01.03.2023",
                  "gueltig_bis": ""
                }
              },
              "valorisierung": false
            }
          }
        ]
      }
    ]
  }
]
