<?php
namespace App\Enum;

enum UserStatus: string {
    case Active = 'active';
    case Blocked = 'blocked';
    case Unverified = 'unverified';
    case Deleted = 'deleted';
}
