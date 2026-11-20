<?php

namespace App\Enums;

enum ProgramCode: string
{
    case Conference = 'conference';
    case ResearchPkm = 'research_pkm';
    case InternationalMou = 'international_mou';
    case CommunityService = 'community_service';
    case DestinationStudy = 'destination_study';
}
