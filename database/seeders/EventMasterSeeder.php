<?php

namespace Database\Seeders;

use App\Enums\ProgramCode;
use App\Models\EventMilestone;
use App\Models\EventSetting;
use App\Models\Program;
use Illuminate\Database\Seeder;

class EventMasterSeeder extends Seeder
{
    public function run(): void
    {
        // Source: original "TOR ICHES 6 UNISYA 2027.docx", sections D, E, F, H and L.
        // This seed is idempotent and intentionally NEVER overwrites Admin edits.
        if (! EventSetting::query()->whereKey(1)->exists()) {
            $event = new EventSetting([
                'name' => 'The 6th International Conference on Humanity, Education, and Society',
                'short_name' => 'ICHES 2027',
                'theme' => 'ISLAMIC VALUES IN A CHANGING WORLD: EDUCATION, ACCOUNTABILITY, AND SOCIAL RESILIENCE FOR INCLUSIVE DEVELOPMENT',
                'organizer' => 'Forum Pimpinan PTKIS Tapal Kuda / Universitas Islam Syarifuddin (UNISYA)',
                'main_venue' => 'Universitas Islam Syarifuddin (UNISYA), Lumajang',
                'starts_on' => '2027-01-05',
                'ends_on' => '2027-01-06',
                'start_time' => null, // The TOR does not establish one canonical event countdown hour.
                'timezone' => 'Asia/Jakarta',
            ]);

            $event->id = 1;
            $event->save();
        }

        $programs = [
            [ProgramCode::Conference, 'International Conference', 'Sesi pleno dan panel paralel; jalur presenter maupun peserta.', '2027-01-05', '2027-01-05', 'Universitas Islam Syarifuddin (UNISYA), Lumajang', 'hybrid'],
            [ProgramCode::ResearchPkm, 'Pembinaan Riset dan PKM', 'Pembinaan dan penguatan riset serta pengabdian bersama Subdit Penelitian dan PKM Kemenag RI.', '2027-01-05', '2027-01-05', 'Universitas Islam Syarifuddin (UNISYA), Lumajang', null],
            [ProgramCode::InternationalMou, 'Penandatanganan International MoU', 'Agenda kerja sama internasional dengan perguruan tinggi mitra.', '2027-01-05', '2027-01-05', 'Universitas Islam Syarifuddin (UNISYA), Lumajang', null],
            [ProgramCode::CommunityService, 'International Community Service', 'Pengabdian internasional kolaboratif di masyarakat; pembagian kelompok dan PIC akan diatur dalam modul operasional.', '2027-01-06', '2027-01-06', 'Desa Sidomulyo, Kabupaten Lumajang', 'onsite'],
            [ProgramCode::DestinationStudy, 'Sustainable Destination Study', 'Studi destinasi berkelanjutan di kawasan Teras Semeru dan Tumpak Sewu.', '2027-01-06', '2027-01-06', 'Teras Semeru dan Tumpak Sewu, Kabupaten Lumajang', 'onsite'],
        ];

        foreach ($programs as $index => [$code, $name, $description, $startsOn, $endsOn, $venue, $mode]) {
            if (Program::query()->where('code', $code->value)->exists()) {
                continue;
            }

            $program = new Program([
                'name' => $name,
                'description' => $description,
                'starts_on' => $startsOn,
                'ends_on' => $endsOn,
                'venue' => $venue,
                'delivery_mode' => $mode,
                'sort_order' => $index + 1,
            ]);

            $program->code = $code;
            $program->save();
        }

        // Official TOR dates are planning milestones, NOT automatic academic or payment gates.
        $milestones = [
            ['abstract_submission', 'Abstract Submission', '2026-12-01', '2026-12-20'],
            ['abstract_decision', 'Abstract Acceptance Announcement', '2026-12-22', '2026-12-22'],
            ['full_paper', 'Full Paper Submission (TOR target)', '2026-12-23', '2026-12-28'],
            ['registration_payment', 'Registration / Payment (TOR target)', '2026-12-24', '2026-12-28'],
            ['conference_day', 'International Conference', '2027-01-05', '2027-01-05'],
            ['field_day', 'Community Service & Destination Study', '2027-01-06', '2027-01-06'],
            ['proceedings', 'Proceedings Publication Target', '2027-01-28', '2027-01-28'],
        ];

        foreach ($milestones as $index => [$code, $name, $startsOn, $endsOn]) {
            if (EventMilestone::query()->where('code', $code)->exists()) {
                continue;
            }

            $milestone = new EventMilestone([
                'name' => $name,
                'starts_on' => $startsOn,
                'ends_on' => $endsOn,
            ]);

            $milestone->code = $code;
            $milestone->sort_order = $index + 1;
            $milestone->save();
        }
    }
}
