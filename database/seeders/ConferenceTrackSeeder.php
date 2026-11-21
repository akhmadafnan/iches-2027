<?php

namespace Database\Seeders;

use App\Enums\ProgramCode;
use App\Models\ConferenceTrack;
use App\Models\Program;
use Illuminate\Database\Seeder;
use RuntimeException;

class ConferenceTrackSeeder extends Seeder
{
    public function run(): void
    {
        // Source: original TOR ICHES 6 UNISYA 2027, Section E.2 (a–h).
        // Stable codes and official titles are retained. Reruns NEVER overwrite Admin edits.
        $conference = Program::query()->where('code', ProgramCode::Conference->value)->first();

        if ($conference === null) {
            throw new RuntimeException('International Conference program must be seeded before tracks.');
        }

        $officialTitles = [
            'Islamic Studies, Ethics, and Contemporary Civilization',
            'Education, Pedagogy, and Human Development',
            'Leadership, Governance, and Institutional Accountability',
            'Economics, Sustainable Business, and Inclusive Development',
            'Law, Human Rights, Social Justice, and Public Policy',
            'Social Sciences, Humanities, Culture, and Community Resilience',
            'Digital Transformation, Responsible Innovation, and Society',
            'Environmental Sustainability, and Human Well-Being',
        ];

        foreach ($officialTitles as $index => $title) {
            $code = sprintf('TRK-%02d', $index + 1);

            if (ConferenceTrack::query()->where('code', $code)->exists()) {
                continue;
            }

            $track = new ConferenceTrack([
                'name' => $title,
                'description' => null,
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
            $track->program_id = $conference->id;
            $track->code = $code;
            $track->official_title = $title;
            $track->save();
        }
    }
}
