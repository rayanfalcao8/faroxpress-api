<?php

namespace App;

enum UserRole: string
{
    case User = 'USER';
    case Operator = 'OPERATOR';
    case Admin = 'ADMIN';
}
