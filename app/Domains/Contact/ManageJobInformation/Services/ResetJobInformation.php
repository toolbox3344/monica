<?php

namespace App\Domains\Contact\ManageJobInformation\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\Contact;
use App\Models\ContactFeedItem;
use Carbon\Carbon;

class ResetJobInformation extends DeathGunContactService
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
     * Reset job information for the given contact.
     */
    public function execute(array $data): Contact
    {
        $this->validateRules($data);

        $this->contact->company_id = null;
        $this->contact->job_position = null;
        $this->contact->save();

        $this->updateLastEditedDate();

        ContactFeedItem::create([
            'author_id' => $this->author->id,
            'contact_id' => $this->contact->id,
            'action' => ContactFeedItem::ACTION_JOB_INFORMATION_UPDATED,
        ]);

        return $this->contact;
    }

    private function updateLastEditedDate(): void
    {
        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();
    }
}
