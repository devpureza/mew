<?php

namespace App\Enums;

enum GuestStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
}
