<?php

namespace App\Enum;

enum EventStatus: string
{
    case PLANNED = 'planifié';
    case IN_PROGRESS = 'en_cours';
    case FINISHED = 'terminé';
    case CANCELLED = 'annulé';
}
