<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'superadmin';
    case Admin = 'admin';
    case Guest = 'guest';
    case Couple = 'couple';
}
