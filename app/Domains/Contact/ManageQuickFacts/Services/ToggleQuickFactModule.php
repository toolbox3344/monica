<?php

namespace App\Domains\Contact\ManageQuickFacts\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Interfaces\ServiceInterface;
use App\Services\BaseService;

class ToggleQuickFactModule extends DeathGunContactService
{
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
        ];
    }

    /**
     * Toggle the quick facts window for the given contact.
     */
    public function execute(array $data): void
    {
        $this->data = $data;
        $this->validate();
        $this->update();
    }

    private function validate(): void
    {
        $this->validateRules($this->data);
    }

    private function update(): void
    {
        $this->contact->show_quick_facts = ! $this->contact->show_quick_facts;
        $this->contact->save();
    }
}
