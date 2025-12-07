<?php

namespace App\Enums;

enum GuestRelationship: string
{
    case Mae = 'mae';
    case Pai = 'pai';
    case Amigos = 'amigos';
    case Familia = 'familia';
    case Trabalho = 'trabalho';
    case Outros = 'outros';
}
