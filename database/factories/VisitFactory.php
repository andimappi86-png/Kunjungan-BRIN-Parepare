<?php

namespace Database\Factories;

use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitFactory extends Factory
{
    protected $model = Visit::class;

    public function definition(): array
    {
        $facilities = [
            'Laboratorium Genomika Terpadu',
            'Fasilitas Riset Reactor Kartini',
            'Kawasan Sains dan Teknologi Sukamandi',
            'Laboratorium Nanoteknologi',
            'Pusat Riset Kecerdasan Artifisial & Siber'
        ];

        $statuses = [Visit::STATUS_MENUNGGU, Visit::STATUS_DISETUJUI, Visit::STATUS_DITOLAK];
        $waStatuses = [Visit::WA_BELUM, Visit::WA_TERKIRIM];

        $visitDate = $this->faker->dateTimeBetween('now', '+2 months');

        return [
            'registration_number' => 'BRIN-VISIT-' . $this->faker->unique()->numberBetween(10000, 99999),
            'applicant_name' => $this->faker->name(),
            'institution' => $this->faker->company() . ' University / Instansi',
            'phone' => '08' . $this->faker->numerify('##########'),
            'email' => $this->faker->safeEmail(),
            'participants_count' => $this->faker->numberBetween(5, 40),
            'purpose' => $this->faker->sentence(10),
            'facility_target' => $this->faker->randomElement($facilities),
            'visit_date' => $visitDate,
            'scheduled_date' => $visitDate,
            'visit_time' => '09:00:00',
            'status' => $this->faker->randomElement($statuses),
            'whatsapp_status' => $this->faker->randomElement($waStatuses),
        ];
    }
}
