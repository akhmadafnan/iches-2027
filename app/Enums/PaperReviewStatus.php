<?php

namespace App\Enums;

enum PaperReviewStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
}
