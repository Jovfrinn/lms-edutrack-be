<?php

namespace App\Services;

class SeederDataBank
{
    /**
     * Mengembalikan 10 data kursus realistis.
     */
    public static function getCourses(): array
    {
        return [
            // Kursus 1

            [
                'title' => 'Panduan Microsoft Excel untuk Akuntan',
                'level' => 'Level 2',
                'is_published' => false,
                'thumbnail' => 'thumbnails/panduan_microsoft.png',
                'contents' => [
                    ['title' => 'Pengenalan VLOOKUP dan HLOOKUP', 'type' => 'video', 'path' => 'course_content_files/Cara_Vlookup_Excel_Untuk_Pemula_(online-video-cutter.com.mp4'],
                    ['title' => 'Tips & Trik Excel', 'type' => 'audio', 'path' => 'course_content_files/eseku8fgpn220m8ckc9cno8n43gasi33m5towid6_jB1nGL1H.mp3'],
                    ['title' => 'Pivot Table', 'type' => 'ebook', 'path' => 'course_content_files/BVnu9n4i17soqr0akXfBXFR4ZAvI8eSTMIEz8uZ0.pdf'],
                    [
                        'title' => 'VLOOKUP',
                        'type' => 'quiz-pg',
                        'questions' => [
                            [
                                'text' => 'Fungsi VLOOKUP digunakan untuk...',
                                'choices' => [
                                    ['text' => 'Mencari data secara horizontal', 'correct' => false],
                                    ['text' => 'Mencari data secara vertikal', 'correct' => true],
                                    ['text' => 'Menjumlahkan data', 'correct' => false],
                                ],
                            ],
                            [
                                'text' => 'Argumen pertama dalam fungsi VLOOKUP adalah...',
                                'choices' => [
                                    ['text' => 'Tabel data', 'correct' => false],
                                    ['text' => 'Nilai yang ingin dicari', 'correct' => true],
                                    ['text' => 'Nomor kolom hasil', 'correct' => false],
                                ],
                            ],
                            [
                                'text' => 'Sintaks dasar fungsi VLOOKUP adalah...',
                                'choices' => [
                                    ['text' => '=VLOOKUP(nilai, tabel, nomor_kolom, [range_lookup])', 'correct' => true],
                                    ['text' => '=VLOOKUP(tabel, nilai, [range_lookup], nomor_kolom)', 'correct' => false],
                                    ['text' => '=VLOOKUP(nilai, tabel, [range_lookup], kolom)', 'correct' => false],
                                ],
                            ],
                            [
                                'text' => 'Jika ingin hasil pencarian VLOOKUP harus tepat sama, maka argumen terakhir diisi dengan...',
                                'choices' => [
                                    ['text' => 'TRUE', 'correct' => false],
                                    ['text' => 'FALSE', 'correct' => true],
                                    ['text' => '0', 'correct' => false],
                                ],
                            ],
                            [
                                'text' => 'Pada fungsi VLOOKUP, kolom yang dijadikan acuan pencarian harus terletak...',
                                'choices' => [
                                    ['text' => 'Di tengah tabel', 'correct' => false],
                                    ['text' => 'Di kolom terakhir', 'correct' => false],
                                    ['text' => 'Di kolom pertama dari tabel', 'correct' => true],
                                ],
                            ],
                        ],
                    ],

                ]
            ],
            // Kursus 2
            [
                'title' => 'Dasar K3 (Kesehatan & Keselamatan Kerja)',
                'is_published' => false,
                'level' => 'Level 1',
                'thumbnail' => 'thumbnails/dasark3.png',
                'contents' => [
                    ['title' => 'Pengenalan K3 dan APD', 'type' => 'video', 'path' => 'course_content_files/pengenalan-k3-&-apd.mp4'],
                    ['title' => 'Identifikasi Bahaya', 'type' => 'ebook', 'path' => 'course_content_files/k3-bahaya.pdf'],
                    ['title' => 'Pemahaman APD', 'type' => 'quiz-pg', 'questions' => [
                        [
                            'text' => 'Di bawah ini yang BUKAN termasuk Alat Pelindung Diri (APD) adalah...',
                            'choices' => [
                                ['text' => 'Helm Proyek', 'correct' => false],
                                ['text' => 'Sepatu Safety', 'correct' => false],
                                ['text' => 'Kemeja Lengan Panjang', 'correct' => true],
                                ['text' => 'Sarung Tangan Karet', 'correct' => false],
                            ]
                        ],
                    ]],
                ]
            ],

            // Kursus 3
            [
                'title' => 'Leadership 101: Menjadi Manajer Efektif',
                'is_published' => false,
                'level' => 'Level 3',
                'thumbnail' => 'thumbnails/leadership.png',
                'contents' => [
                    ['title' => 'Seni Memberi Feedback', 'type' => 'video', 'path' => 'course_content_files/feedback.mp4'],
                    ['title' => 'Teknik Coaching & Mentoring', 'type' => 'ebook', 'path' => 'course_content_files/coaching.pdf'],
                    [
                        'title' => 'Kuis Leadership',
                        'type' => 'quiz-pg',
                        'questions' => [
                            [
                                'text' => 'Feedback yang efektif seharusnya...',
                                'choices' => [
                                    ['text' => 'Menjatuhkan mental', 'correct' => false],
                                    ['text' => 'Spesifik dan membangun', 'correct' => true],
                                    ['text' => 'Disampaikan di depan umum', 'correct' => false],
                                ],
                            ],
                        ],
                    ],
                ]
            ],

            // Kursus 4
            [
                'title' => 'Public Speaking untuk Profesional',
                'is_published' => false,
                'level' => 'Level 1',
                'thumbnail' => 'thumbnails/public_speaking.png',
                'contents' => [
                    ['title' => 'Mengatasi Gugup', 'type' => 'video', 'path' => 'course_content_files/Tips-Berbicara-Di-Depan-Umum.mp4'],
                    ['title' => 'Teknik Dasar', 'type' => 'quiz-pg', 'questions' => [
                        [
                            'text' => 'Apa yang dimaksud dengan "body language" dalam public speaking?',
                            'choices' => [
                                ['text' => 'Bahasa yang digunakan', 'correct' => false],
                                ['text' => 'Gerak tubuh dan ekspresi wajah', 'correct' => true],
                                ['text' => 'Volume suara', 'correct' => false],
                            ]
                        ]
                    ]],
                ]
            ],
            // Kursus 5
            [
                'title' => 'Etika Email Profesional',
                'is_published' => false,
                'level' => 'Level 1',
                'thumbnail' => 'thumbnails/etika_email.png',
                'contents' => [
                    ['title' => 'Kapan Harus CC dan BCC', 'type' => 'ebook', 'path' => 'course_content_files/Kapan_Harus_CC_dan_BCC.pdf'],
                    ['title' => 'Etika Email', 'type' => 'quiz-pg', 'questions' => [
                        [
                            'text' => 'Kapan sebaiknya Anda menggunakan "BCC" (Blind Carbon Copy)?',
                            'choices' => [
                                ['text' => 'Saat mengirim ke atasan', 'correct' => false],
                                ['text' => 'Saat mengirim email massal tanpa ingin membagikan email penerima lain', 'correct' => true],
                                ['text' => 'Setiap saat', 'correct' => false],
                            ]
                        ]
                    ]],
                ]
            ],
            // Kursus 6
            [
                'title' => 'Dasar Pemasaran Digital (Digital Marketing)',
                'is_published' => false,
                'level' => 'Level 2',
                'is_published' => 0,
                'thumbnail' => 'thumbnails/dasar_pemsaaran_digital.png',
                'contents' => [
                    ['title' => 'Pengenalan SEO vs SEM', 'type' => 'video', 'path' => 'course_content_files/seo-sem.mp4'],
                    ['title' => 'Apa itu Content Marketing', 'type' => 'ebook', 'path' => 'course_content_files/content.pdf'],
                    [
                        'title' => 'Kuis Digital Marketing',
                        'type' => 'quiz-pg',
                        'questions' => [
                            [
                                'text' => 'SEO bertujuan untuk...',
                                'choices' => [
                                    ['text' => 'Iklan berbayar', 'correct' => false],
                                    ['text' => 'Meningkatkan ranking organik', 'correct' => true],
                                    ['text' => 'Desain website', 'correct' => false],
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            // Kursus 7
            [
                'title' => 'Manajemen Waktu (Time Management)',
                'is_published' => false,
                'level' => 'Level 1',
                'thumbnail' => 'thumbnails/manajemen_waktu.png',
                'contents' => [
                    ['title' => 'Teknik Pomodoro', 'type' => 'audio', 'path' => 'course_content_files/pomodoro.mp3'],
                    ['title' => 'Matriks Eisenhower', 'type' => 'ebook', 'path' => 'course_content_files/eisenhower.pdf'],
                    [
                        'title' => 'Kuis Manajemen Waktu',
                        'type' => 'quiz-pg',
                        'questions' => [
                            [
                                'text' => 'Teknik Pomodoro menggunakan interval kerja selama...',
                                'choices' => [
                                    ['text' => '15 menit', 'correct' => false],
                                    ['text' => '25 menit', 'correct' => true],
                                    ['text' => '60 menit', 'correct' => false],
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            // Kursus 8
            [
                'title' => 'Dasar Manajemen Proyek',
                'is_published' => false,
                'level' => 'Level 3',
                'thumbnail' => 'thumbnails/managemen_project.png',
                'contents' => [
                    ['title' => 'Pengenalan Agile vs Waterfall', 'type' => 'video', 'path' => 'course_content_files/pengenalan-agile-dan-waterfall.mp4'],
                    ['title' => 'Gantt Chart', 'type' => 'ebook', 'path' => 'course_content_files/gant-chart.pdf'],
                    ['title' => 'Terminologi Proyek', 'type' => 'quiz-pg', 'questions' => [
                        [
                            'text' => 'Apa itu "Scope Creep"?',
                            'choices' => [
                                ['text' => 'Proyek yang selesai tepat waktu', 'correct' => false],
                                ['text' => 'Penambahan fitur/pekerjaan di luar lingkup awal', 'correct' => true],
                                ['text' => 'Manajer proyek yang malas', 'correct' => false],
                            ]
                        ]
                    ]],
                ]
            ],
            // Kursus 9
            [
                'title' => 'Keamanan Siber (Cybersecurity Awareness)',
                'is_published' => false,
                'level' => 'Level 1',
                'thumbnail' => 'thumbnails/keamanan_siber.png',
                'contents' => [
                    ['title' => 'Apa itu Phishing?', 'type' => 'video', 'path' => 'course_content_files/apa-itu-phising.mp4 '],
                    ['title' => 'Deteksi Phishing', 'type' => 'quiz-pg', 'questions' => [
                        [
                            'text' => 'Anda menerima email dari "Bank Anda" yang meminta password. Apa yang Anda lakukan?',
                            'choices' => [
                                ['text' => 'Membalasnya dengan password', 'correct' => false],
                                ['text' => 'Mengklik link dan mengisi formulir', 'correct' => false],
                                ['text' => 'Menghapus email dan melapor ke IT', 'correct' => true],
                            ]
                        ]
                    ]],
                ]
            ],
            // Kursus 10
            [
                'title' => 'Mengenal Budaya Perusahaan (Company Values)',
                'is_published' => false,
                'level' => 'Level 1',
                'thumbnail' => 'thumbnails/mengenal_budaya_perusahaan.png',
                'contents' => [
                    ['title' => 'Visi, Misi, dan Nilai Perusahaan', 'type' => 'ebook', 'path' => 'course_content_files/visi-misi-value.pdf'],
                    ['title' => 'Budaya Perusahaan', 'type' => 'quiz-pg', 'questions' => [
                        [
                            'text' => '(Contoh) Apa nilai inti perusahaan kita?',
                            'choices' => [
                                ['text' => 'Integritas', 'correct' => true],
                                ['text' => 'Profit', 'correct' => false],
                                ['text' => 'Individualisme', 'correct' => false],
                            ]
                        ]
                    ]],
                ]
            ],
        ];
    }
}
