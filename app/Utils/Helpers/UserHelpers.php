<?php

namespace App\Utils\Helpers;

use App\Utils\Enums\PetJournalPermission;

class UserHelpers
{
    public static function getUserPermissions(){
        $permissionValues = PetJournalPermission::getValues();
    }
}
