<?php

// Orang-orang di balik Ruang Pulang, dikelompokkan per bidang.
$member = fn (string $name, ?string $photo = null, ?string $instagram = null, array $extra = []) => [
    'name' => $name,
    'photo' => $photo ? "images/pengurus/foto-{$photo}.jpg" : null,
    'instagram' => $instagram ? "https://www.instagram.com/{$instagram}/" : null,
    ...$extra,
];

$alma = ['Fathia Salama Jacob (Alma)', '67edf9f3', 'fathiajcb_'];
$idho = ['Ridho Prasetio Ismail (Idho)', 'c4d9c767', 'do0x1yzz_'];
$fathur = ['Fatur Djafar (Fathur)', 'fc2ebb52', 'turhmndj'];
$hikmal = ['Mohammad Hikmal Tahir (Hikmal)', '9b86fb5c', 'repanjah___'];
$dhia = ['Suci Ramadia Dede (Dhia)', '24c39182', 'dhiaaa_ya07'];

return [
    [
        'title' => 'Para Pendiri',
        'members' => [
            $member(...$alma, extra: ['role' => 'Founder', 'bio' => 'Membangun Ruang Pulang dari sebuah pengalaman sederhana: pernah membutuhkan ruang untuk bercerita, lalu ingin menghadirkannya bagi orang lain.']),
            $member(...$idho, extra: ['role' => 'Founder', 'bio' => 'Born as an introvert itu memang menyedihkan. Dengan adanya Ruang Pulang, saya harap teman-teman yang lain tidak merasakan hal yang sama.']),
            $member(...$fathur, extra: ['role' => 'Founder', 'bio' => 'Being present.']),
            $member(...$hikmal, extra: ['role' => 'Founder', 'bio' => 'Masih mikir']),
            $member(...$dhia, extra: [
                'role' => 'Founder',
                'bio' => 'Dari ruang kecil ini, tempat di mana segala cerita, kenangan, kebahagiaan, dan mimpi disimpan dengan penuh kasih.',
                'bio_more' => 'Di sini aku belajar menikmati kehidupan, mencintai setiap perjalanan, dan menemukan keindahan dalam kesederhanaan. Sebuah ruang untuk menjadi diri sendiri tanpa perlu berpura-pura menjadi siapa-siapa.',
            ]),
        ],
    ],
    [
        'title' => 'Pengurus Inti',
        'members' => [
            $member('Alim Aprilianto (Alim)', 'fb214b80', 'aliemgates', ['role' => 'Ketua']),
            $member(...$alma, extra: ['role' => 'Wakil Ketua']),
            $member('Sari Wulandari Potabuga (Wulan)', 'a8912ff4', 'b.bulannku', ['role' => 'Sekretaris']),
            $member(...$dhia, extra: ['role' => 'Bendahara']),
        ],
    ],
    [
        'title' => 'Ruang Tumbuh',
        'description' => [
            'Kami bukan tenaga ahli atau psikolog. Namun kami dapat menjadi ruang untuk mendengarkan dan mendampingi anggota secara personal, membantu ketika ada persoalan, serta menjadi penghubung dalam proses mediasi.',
            'Kami ingin menjadi tempat yang aman dan nyaman untuk berbagi cerita, bertukar pikiran, pendapat, dan saling mendorong untuk terus bertumbuh.',
        ],
        'members' => [
            $member('Dessica Suhaedi Hartono (Dessica)', '12bc5ee3', 'dessicahartono'),
            $member('April'),
            $member('Roma Palilati (Roma)', '3903f871', 'kafeel4191'),
        ],
    ],
    [
        'title' => 'Ruang Temu',
        'description' => ['Berfokus pada perencanaan kegiatan, mulai dari agenda, jadwal, konsep, waktu, hingga membantu memastikan kegiatan berjalan dengan baik.'],
        'members' => [
            $member(...$idho),
            $member(...$hikmal),
        ],
    ],
    [
        'title' => 'Ruang Karya',
        'description' => ['Berfokus pada dokumentasi dan kreativitas, seperti foto, video, editing, konten, serta kebutuhan visual Ruang Pulang.'],
        'members' => [
            $member('Rey'),
            $member('Fay'),
            $member('Timothy Tongkeles Wirodigdoyo (Moty)', 'c6f33261', 'imoht22'),
        ],
    ],
    [
        'title' => 'Ruang Kenal',
        'description' => ['Berfokus pada hubungan dan kedekatan dengan anggota, membantu anggota baru beradaptasi, mengenal karakter dan kebutuhan anggota, menjaga komunikasi, serta membangun relasi yang baik di dalam maupun di luar komunitas.'],
        'members' => [
            $member('Ursula Kimberly Melania (Kim)', 'cb6a27ba', 'kimberlymelania'),
            $member(...$fathur),
            $member('Keren Kezia Makasala (Eyen)', 'cc4567a9', 'kerenaa_1'),
            $member('Randhy'),
        ],
    ],
    [
        'title' => 'Ruang Tertib',
        'description' => ['Ruang Tertib bertugas menjaga kedisiplinan, ketertiban, dan kenyamanan seluruh anggota Ruang Pulang dengan memastikan setiap anggota menaati aturan komunitas, menindaklanjuti pelanggaran, memberikan teguran secara bijak, serta membantu menjaga suasana kegiatan agar tetap aman, tertib, dan kondusif. Ruang Tertib mengedepankan sikap tegas, adil, dan humanis dalam menjalankan tugasnya.'],
        'members' => [
            $member('Agung'),
            $member('Opyan'),
        ],
    ],
];
