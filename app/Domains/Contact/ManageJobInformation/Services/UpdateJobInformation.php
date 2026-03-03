<?php

namespace App\Domains\Contact\ManageJobInformation\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\Company;
use App\Models\Contact;
use App\Models\ContactFeedItem;
use Carbon\Carbon;

class UpdateJobInformation extends DeathGunContactService
{
    private Company $company;

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
            'company_id' => 'nullable|integer',
            'job_position' => 'nullable|string|max:255',
        ];
    }


    /**
     * Update job information for the given contact.
     */
    public function execute(array $data): Contact
    {
        $this->validateRules($data);

        if (! is_null($this->valueOrNull($data, 'company_id'))) {
            $this->company = $this->vault->companies()
                ->findOrFail($data['company_id']);
        }

        $this->contact->company_id = $data['company_id'] ? $this->company->id : null;
        $this->contact->job_position = $this->valueOrNull($data, 'job_position');
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
