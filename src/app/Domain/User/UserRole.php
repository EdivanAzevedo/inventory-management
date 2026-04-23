<?php

namespace App\Domain\User;

enum UserRole: string
{
    case admin    = 'admin';
    case operator = 'operator';
    case viewer   = 'viewer';
}
