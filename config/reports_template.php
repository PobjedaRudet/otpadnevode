<?php

return [
    // Map template row keys to matching company/instrument patterns.
    // In your Word template, place placeholders like:
    //   Long form (backward-compatible):
    //     ${<key>_first_value}, ${<key>_last_value}, ${<key>_diff}
    //   Short form (preferred):
    //     ${<alias>_f}, ${<alias>_l}, ${<alias>_d}
    // Then the exporter will fill them for the selected year/month.
    'rows' => [
        // POBJEDA-RUDET (revizioni šaht pored Objekta 120)
        'pobjeda_rudet_hromne' => [
            'company_contains' => 'POBJEDA-RUDET',
            'instrument_contains' => 'Hromne',
            'alias' => 'pb_h', // ${pb_h_f}, ${pb_h_l}, ${pb_h_d}
        ],
        'pobjeda_rudet_barijum_hromatne' => [
            'company_contains' => 'POBJEDA-RUDET',
            'instrument_contains' => 'Barijum',
            'alias' => 'pb_bh',
        ],
        'pobjeda_rudet_bazne' => [
            'company_contains' => 'POBJEDA-RUDET',
            'instrument_contains' => 'Bazne',
            'alias' => 'pb_b',
        ],

        // UNIS GINEX – Objekat 111A
        'ginex_111a_kisele' => [
            'company_contains' => 'GINEX',
            'instrument_contains' => 'Kisele',
            'alias' => 'g_k',
        ],
        'ginex_111a_bazne' => [
            'company_contains' => 'GINEX',
            'instrument_contains' => 'Bazne',
            'alias' => 'g_b',
        ],
        'ginex_111a_hromne' => [
            'company_contains' => 'GINEX',
            'instrument_contains' => 'Hromne',
            'alias' => 'g_h',
        ],

        // UNIS GINEX – Objekat 154
        'ginex_154_bazne' => [
            'company_contains' => 'GINEX',
            'instrument_contains' => 'Bazne',
            'alias' => 'g4_b',
        ],

        // POBJEDA TECHNOLOGY – Objekat 101
        'pobjeda_technology_kisele' => [
            'company_contains' => 'POBJEDA TECHNOLOGY',
            'instrument_contains' => 'Kisele',
            'alias' => 'pt_k',
        ],
        'pobjeda_technology_bazne' => [
            'company_contains' => 'POBJEDA TECHNOLOGY',
            'instrument_contains' => 'Bazne',
            'alias' => 'pt_b',
        ],
    ],
];
