<?php

namespace App\Enums;

enum LeaveGenderEnum: string
{
    case All = 'all';
    case Female = 'female';
    case Male = 'male';
    case Others = 'others';
}