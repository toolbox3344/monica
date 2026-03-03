<?php

namespace App\Domains\Contact\ManagePronouns\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Interfaces\ServiceInterface;
use App\Services\BaseService;
use Carbon\Carbon;

class RemovePronoun extends DeathGunContactService
{
    /**
     * Get the validation rules that apply to the service.
     */
    public function rules(): array
    {
        return [
            'account_id' => 'required|uuid|exists:accounts,id',
            'vault_id' => 'required|uuid|exists:vaults,id',
            'author_id' => 'required|uuid|exists:users,id',
            'contact_id' => 'required|uuid|exists:contacts,id',
        ];
    }

    /**
     * Unset a contact's pronoun.
     */
    public function execute(array $data): void
    {
        $this->validateRules($data);

        $this->contact->pronoun_id = null;
        $this->contact->save();
        $this->updateLastEditedDate();
    }

    private function updateLastEditedDate(): void
    {
        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();
    }
}
