<?php

namespace App\Domains\Contact\ManagePronouns\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\Pronoun;
use Carbon\Carbon;

class SetPronoun extends DeathGunContactService
{
    private Pronoun $pronoun;

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
            'pronoun_id' => 'required|integer|exists:pronouns,id',
        ];
    }


    /**
     * Set a contact's pronoun.
     */
    public function execute(array $data): void
    {
        $this->validateRules($data);

        $this->pronoun = $this->account()->pronouns()
            ->findOrFail($data['pronoun_id']);

        $this->contact->pronoun_id = $this->pronoun->id;
        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();
    }
}
