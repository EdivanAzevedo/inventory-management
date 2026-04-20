<?php

namespace App\Domain\Stock;

enum MovementType: string
{
    case ENTRY    = 'ENTRY';
    case EXIT     = 'EXIT';
    case REVERSAL = 'REVERSAL';
}
