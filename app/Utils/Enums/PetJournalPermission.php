<?php

namespace App\Utils\Enums;

enum PetJournalPermission:int
{
    case PermissionsUsersView = 900;
    case PermissionsUsersCreate = 901;
    case PermissionsUsersEdit = 902;
    case PermissionsUsersDelete = 903;
    case PermissionsUsersExport = 904;
    case PermissionsUsersSearch = 905;
}
