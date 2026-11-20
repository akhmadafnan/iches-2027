<?php

namespace App\Enums;

enum RegistrationIntention: string
{
    case SubmitPaper = 'submit_paper';
    case JoinConference = 'join_conference';
    case ExplorePrograms = 'explore_programs';

    public function label(): string
    {
        return match ($this) {
            self::SubmitPaper => 'Submit a Paper',
            self::JoinConference => 'Join as Conference Participant',
            self::ExplorePrograms => 'Explore ICHES Programs',
        };
    }
}
