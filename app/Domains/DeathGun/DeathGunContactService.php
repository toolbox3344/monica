<?php

namespace App\Domains\DeathGun;

use App\Interfaces\ServiceInterface;
use App\Services\BaseService;

abstract class DeathGunContactService extends BaseService implements ServiceInterface
{

    /**
     * Get the permissions that apply to the user calling the service.
     */
    public function permissions(): array
    {
        return [
            'author_must_belong_to_account',
            'vault_must_belong_to_account',
            'author_must_be_vault_editor',
            'contact_must_belong_to_vault',
        ];
    }

    //TODO: Cacher les rules

}
