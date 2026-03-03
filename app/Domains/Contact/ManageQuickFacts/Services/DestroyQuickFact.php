<?php

namespace App\Domains\Contact\ManageQuickFacts\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Interfaces\ServiceInterface;
use App\Services\BaseService;

class DestroyQuickFact extends DeathGunContactService
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
            'quick_fact_id' => 'required|integer|exists:quick_facts,id',
        ];
    }

    /**
     * Destroy a quick fact.
     */
    public function execute(array $data): void
    {
        $this->validateRules($data);

        $quickFact = $this->contact->quickFacts()
            ->findOrFail($data['quick_fact_id']);

        $quickFact->delete();
    }
}
