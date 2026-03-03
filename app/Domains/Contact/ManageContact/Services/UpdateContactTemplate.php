<?php

namespace App\Domains\Contact\ManageContact\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\Contact;
use Carbon\Carbon;

class UpdateContactTemplate extends DeathGunContactService
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
            'template_id' => 'required|integer|exists:templates,id',
        ];
    }

    /**
     * Update the contact's template.
     */
    public function execute(array $data): Contact
    {
        $this->data = $data;

        $this->validate();
        $this->update();
        $this->updateLastEditedDate();

        return $this->contact;
    }

    private function validate(): void
    {
        $this->validateRules($this->data);

        $this->account()->templates()
            ->findOrFail($this->data['template_id']);
    }

    private function update(): void
    {
        $this->contact->template_id = $this->data['template_id'];
        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();
    }

    private function updateLastEditedDate(): void
    {
        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();
    }
}
