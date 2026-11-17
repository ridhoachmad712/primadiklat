<?php

namespace Database\Seeders;

use App\Models\ScheduleDate;
use App\Models\Topic;
use App\Models\Training;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Data from "Jadwal Bimtek Januari – Desember 2027": 55 two-day slots and 13 cities / 14 hotels.
 * Safe to re-run (updateOrCreate). Topics are seeded from existing sample trainings until the
 * official topic list is entered in the admin panel.
 */
class Schedule2027Seeder extends Seeder
{
    /** Start dates; every slot lasts two consecutive days. */
    public const START_DATES = [
        '2027-01-07', '2027-01-14', '2027-01-21', '2027-01-28',
        '2027-02-04', '2027-02-11', '2027-02-18', '2027-02-25',
        '2027-03-04', '2027-03-11', '2027-03-18', '2027-03-25',
        '2027-04-01', '2027-04-08', '2027-04-15', '2027-04-22', '2027-04-29',
        '2027-05-06', '2027-05-13', '2027-05-20', '2027-05-27',
        '2027-06-03', '2027-06-10', '2027-06-17', '2027-06-24', '2027-06-29',
        '2027-07-01', '2027-07-08', '2027-07-15', '2027-07-22', '2027-07-29',
        '2027-08-05', '2027-08-12', '2027-08-19', '2027-08-26',
        '2027-09-02', '2027-09-09', '2027-09-16', '2027-09-23', '2027-09-29',
        '2027-10-07', '2027-10-14', '2027-10-21', '2027-10-25', '2027-10-28',
        '2027-11-04', '2027-11-11', '2027-11-18', '2027-11-25', '2027-11-29',
        '2027-12-02', '2027-12-09', '2027-12-16', '2027-12-20', '2027-12-27',
    ];

    public const VENUES = [
        ['Jakarta', 'Hotel Asyana Kemayoran', 'Jl. Bungur Besar Raya No. 1, Jakarta Pusat'],
        ['Jakarta', 'H! Hotel Senen', 'Jl. Kramat Raya No. 102, Senen, Kota Jakarta Pusat'],
        ['Bandung', 'Hotel Serela Cihampelas', 'Jl. Cihampelas, Bandung'],
        ['Yogyakarta', 'Fave Hotel Malioboro', 'Jl. I Dewa Nyoman Oka, Kotabaru, Yogyakarta'],
        ['Bali', 'Hotel Zia Kuta Bali', 'Jl. Ciung Wanara No. 17, Br. Tegal, Kuta 80361'],
        ['Surabaya', 'La Lisa Hotel Surabaya', 'Jl. Raya Nginden No. 82, Surabaya'],
        ['Malang', 'MaxOne Hotel Malang', 'Jl. Jaksa Agung Suprapto No. 75 A, Kota Malang'],
        ['Batam', 'Hotel Aston Gideon Inn Batam', 'Jl. Komp. Penuin Centre No. 1, Batu Selicin, Batam'],
        ['Makassar', 'Swiss-Belinn Panakkukang Makassar', 'Jl. Adyaksa Baru No. 55, Pandang, Kec. Panakkukang, Kota Makassar'],
        ['Lombok', 'Hotel Lombok Plaza', 'Jl. Pejanggik No. 8, Cakranegara 83231, Mataram'],
        ['Medan', 'Fave Hotel Medan', 'Jl. S. Parman No. 313A, Petisah Hulu, Medan'],
        ['Manado', 'Whizz Prime Hotel Manado', 'Kawasan Megamas, Jl. PT Boulevard, Manado'],
        ['Banjarmasin', 'Fave Hotel Banjarmasin', 'Jl. Ahmad Yani No. 35, Sungai Baru, Banjarmasin'],
        ['Balikpapan', "Hotel D'Prima Balikpapan", 'Jl. MT Haryono No. 78, Damai, Balikpapan'],
    ];

    public function run(): void
    {
        foreach (self::START_DATES as $start) {
            $s = Carbon::parse($start);
            ScheduleDate::updateOrCreate(['start_date' => $s->toDateString()], ['end_date' => $s->copy()->addDay()->toDateString()]);
        }

        foreach (self::VENUES as $i => [$city, $name, $address]) {
            Venue::updateOrCreate(['name' => $name], ['city' => $city, 'address' => $address, 'sort_order' => $i + 1]);
        }

        if (Topic::count() === 0) {
            Training::whereNull('topic_id')->whereNull('schedule_date_id')->orderBy('id')->get()
                ->unique('title')
                ->each(fn (Training $t, $i) => Topic::create([
                    'category_id' => $t->category_id,
                    'type' => $t->type,
                    'title' => $t->title,
                    'excerpt' => $t->excerpt,
                    'description' => $t->description,
                    'materials' => $t->materials,
                    'target_participants' => $t->target_participants,
                    'thumbnail' => $t->thumbnail,
                    'is_featured' => $t->is_featured,
                    'sort_order' => $i + 1,
                ]));
        }
    }
}
