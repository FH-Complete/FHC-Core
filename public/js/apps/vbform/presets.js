import uuid from '../../helpers/vbform/uuid.js';

export default [
  {
    type: 'preset',
    guioptions: {
      id: 'leer',
      label: 'Leer',
      description: 'Leere Vorlage. Alles muss manuell definiert werden.'
    },
    children: [
      {
        type: 'dv',
        guioptions: {
        },
        children: []
      },
      {
        type: 'vertragsbestandteillist',
        guioptions: {
          title: 'Arbeitszeit',
          vertragsbestandteiltyp: 'vertragsbestandteilstunden'
        },
        children: []
      },
      {
        type: 'vertragsbestandteillist',
        guioptions: {
          title: 'Zeitaufzeichnung',
          vertragsbestandteiltyp: 'vertragsbestandteilzeitaufzeichnung'
        },
        children: []
      },
      {
        type: 'vertragsbestandteillist',
        guioptions: {
          title: 'Kündigungsfrist',
          vertragsbestandteiltyp: 'vertragsbestandteilkuendigungsfrist'
        },
        children: []
      },
      {
        type: 'vertragsbestandteillist',
        guioptions: {
          title: 'Funktionen',
          vertragsbestandteiltyp: 'vertragsbestandteilfunktion'
        },
        children: []
      },
      {
        type: 'vertragsbestandteillist',
        guioptions: {
          title: 'Sonstiges',
          vertragsbestandteiltyp: 'vertragsbestandteilfreitext'
        },
        children: []
      }
    ],
    data: {
      dienstverhaeltnisid: null
    },
    vbs: {

    }
  },
  {
    type: 'preset',
    guioptions: {
      id: 'echterdv',
      label: 'Echter DV',
      description: 'Standard Vorlage für echte Dienstverträge'
    },
    children: [
      {
        type: 'tabs',
        guioptions: {

        },
        children: [
          {
            type: 'tab',
            guioptions: {
              title: 'Allgemein',
              id: 'allgemein'
            },
            children: [
              {
                type: 'dv',
                guioptions: {
                },
                children: []
              },
              {
                type: 'vertragsbestandteillist',
                guioptions: {
                  title: 'Kündigungsfrist',
                  vertragsbestandteiltyp: 'vertragsbestandteilkuendigungsfrist'
                },
                children: []
              },
              {
                type: 'vertragsbestandteillist',
                guioptions: {
                  title: 'Befristung',
                  vertragsbestandteiltyp: 'vertragsbestandteilfreitext',
                  childdefaults: {
                    guioptions: {
                      canhavegehaltsbestandteile: false,
                      disabled: [
                        'freitexttyp'
                      ],
                      hidden: [
                        'titel',
                        'freitext'
                      ]
                    },
                    data: {
                      freitexttyp: "befristung",
                      titel: "Befristung",
                      freitext: "befristeter Dienstvertrag"
                    }
                  }
                },
                children: []
              }
            ]
          },
          {
            type: 'tab',
            guioptions: {
              title: 'Arbeitszeit & Basisgehalt',
              id: 'arbeitszeit'
            },
            children: [
              {
                type: 'vertragsbestandteillist',
                guioptions: {
                  title: 'Arbeitszeit',
                  vertragsbestandteiltyp: 'vertragsbestandteilstunden',
                  errors: [
                    'test1',
                    'test2'
                  ],
                  infos: []
                },
                children: [
                  uuid.get_uuidbyname('test1')
                ]
              },
              {
                type: 'vertragsbestandteillist',
                guioptions: {
                  title: 'Zeitaufzeichnung',
                  vertragsbestandteiltyp: 'vertragsbestandteilzeitaufzeichnung',
                  errors: [],
                  infos: []
                },
                children: [
                ]
              }
            ]
          },
          {
            type: 'tab',
            guioptions: {
              title: 'Funktionen',
              id: 'funktionen'
            },
            children: [
              {
                type: 'vertragsbestandteillist',
                guioptions: {
                  title: 'Funktion',
                  vertragsbestandteiltyp: 'vertragsbestandteilfunktion',
                  errors: [],
                  infos: []
                },
                children: [
                  uuid.get_uuidbyname('test2')
                ]
              }
            ]
          },
          {
            type: 'tab',
            guioptions: {
              title: 'Zusatzvereinbarungen',
              id: 'zusatzvereinbarungen'
            },
            children: [
              {
                type: 'vertragsbestandteillist',
                guioptions: {
                  title: 'Zusatzvereinbarungen',
                  vertragsbestandteiltyp: 'vertragsbestandteilfreitext'
                },
                children: []
              }
            ]
          },
          {
            type: 'tab',
            guioptions: {
              title: 'Sonstiges',
              id: 'sonstiges'
            },
            children: [
              {
                type: 'vertragsbestandteillist',
                guioptions: {
                  title: 'Kündigungsfrist',
                  vertragsbestandteiltyp: 'vertragsbestandteilkuendigungsfrist',
                  errors: [],
                  infos: []
                },
                children: [
                ]
              }
            ]
          }
        ]
      }
    ],
    data: {
      dienstverhaeltnisid: null
    },
    vbs: {
      [uuid.get_uuidbyname('test1')]: {
        type: 'vertragsbestandteilstunden',
        guioptions: {
          id: uuid.get_uuidbyname('test1'),
          infos: [
            'test info 1',
            'test info 2'
          ],
          errors: [
            'test error 1',
            'test error 2'
          ]
        },
        data: {
          stunden: '38,5'
        },
        gbs: [
          {
            type: 'gehaltsbestandteil',
            guioptions: {
              infos: [
                'test info 1',
                'test info 2'
              ],
              errors: [
                'test error 1',
                'test error 2'
              ]
            },
            data: {}
          }
      ]
      },
      [uuid.get_uuidbyname('test2')]: {
        type: 'vertragsbestandteilfunktion',
        guioptions: {
          id: uuid.get_uuidbyname('test2')
        },
        data: {
          funktion: 'Leitung',
          oe_kurzbz: 'core'
        },
        gbs: []
      }
    }
  },
  {
    type: 'preset',
    guioptions: {
      id: 'freierdv',
      label: 'Freier DV',
      description: 'freier Dienstvertrag'
    },
    children: [
      {
        type: 'tabs',
        guioptions: {

        },
        children: [
          {
            type: 'tab',
            guioptions: {
              title: 'Allgemein',
              id: 'allgemein'
            },
            children: [
              {
                type: 'dv',
                guioptions: {
                },
                children: []
              }
            ]
          },
          {
            type: 'tab',
            guioptions: {
              title: 'Zusatzvereinbarungen',
              id: 'zusatzvereinbarungen'
            },
            children: [
              {
                type: 'vertragsbestandteillist',
                guioptions: {
                  title: 'Zusatzvereinbarungen',
                  vertragsbestandteiltyp: 'vertragsbestandteilfreitext'
                },
                children: []
              }
            ]
          }
        ]
      }
    ],
    data: {
      dienstverhaeltnisid: null
    },
    vbs: {
    }
  }
]
