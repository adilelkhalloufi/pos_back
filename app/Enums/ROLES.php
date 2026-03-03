<?php

namespace App\Enums;

enum ROLES: string
{

    case OWNER = 'owner';
    case MANAGER = 'manager';
    case VENDOR = 'vendor';
    case VIEWER = 'viewer';
    case INVENTORY_MANAGER = 'inventory_manager';
    case ACCOUNTANT = 'accountant';
    case SUPER_ADMIN = 'super_admin';
}
