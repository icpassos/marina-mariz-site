<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadDownload;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<LeadDownload> */
class LeadDownloadFactory extends Factory
{
    protected $model = LeadDownload::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'submission_id' => (string) Str::uuid(),
            'origem' => 'Guia do pré-natal',
        ];
    }
}
