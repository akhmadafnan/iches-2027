<?php

namespace App\Enums;

enum PaperReviewRecommendation: string
{
    case Accept = 'accept';
    case Reject = 'reject';
}
