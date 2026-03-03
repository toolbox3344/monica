<?php

namespace App\Domains\Contact\ManageQuickFacts\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Interfaces\ServiceInterface;
use App\Models\QuickFact;
use App\Services\BaseService;

class CreateQuickFact extends DeathGunContactService
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
            'author_id' => 'required|uuid|exists:users,id',
            'contact_id' => 'required|uuid|exists:contacts,id',
            'vault_id' => 'required|uuid|exists:vaults,id',
            'vault_quick_facts_template_id' => 'required|integer|exists:vault_quick_facts_templates,id',
            'content' => 'required|string|max:255',
        ];
    }


    /**
     * Create a quick fact.
     */
    public function execute(array $data): QuickFact
    {
        $this->data = $data;
        $this->validate();

        $this->quickFact = QuickFact::create([
            'vault_quick_facts_template_id' => $data['vault_quick_facts_template_id'],
            'contact_id' => $data['contact_id'],
            'content' => $data['content'],
        ]);

        return $this->quickFact;
    }

    private function validate(): void
    {
        $this->validateRules($this->data);

        $this->vault->quickFactsTemplateEntries()
            ->findOrFail($this->data['vault_quick_facts_template_id']);
    }
}
