<?php

namespace App\Enum;

enum RegistrationStatus: string
{
    case PENDING = 'en_attente';
    case CONFIRMED = 'confirmé';
    case CANCELLED = 'annulé';
    case REFUSED = 'refusé';
    case REGISTERED = 'inscrit';
}
