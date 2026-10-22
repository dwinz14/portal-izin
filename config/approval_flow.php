<?php

return [
    // Aturan pengganti per role saat mengajukan izin/cuti:
    //  *  - required : wajib memilih pengganti
    //  *  - optional : boleh memilih pengganti atau tanpa pengganti
    //  *  - none     : tidak ada alur pengganti (field tidak ditampilkan)
    'replacement' => [
        'staff'        => 'optional',
        'kasie'        => 'optional',
        'kabag-pincab' => 'optional',
        'hrd'          => 'none',
        'direksi'      => 'none',
    ],
];
