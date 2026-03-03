<?php

namespace App\Domains\Contact\ManageQuickFacts\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\QuickFact;

class UpdateQuickFact extends DeathGunContactService
{
    private QuickFact $quickFact;

    private array $data;

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
            'content' => 'required|string|max:255',
        ];
    }


    /**
     * Update a quick fact.
     */
    public function execute(array $data): QuickFact
    {
        $this->data = $data;
        $this->validate();

        $this->quickFact->content = $this->data['content'];
        $this->quickFact->save();

        return $this->quickFact;
    }

    private function validate(): void
    {
        $this->validateRules($this->data);

        $this->quickFact = $this->contact->quickFacts()
            ->findOrFail($this->data['quick_fact_id']);
    }
}
