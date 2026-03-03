<?php

namespace App\Domains\Contact\ManageReminders\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\ContactReminder;
use Carbon\Carbon;

class DestroyReminder extends DeathGunContactService
{
    private ContactReminder $reminder;

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
            'contact_reminder_id' => 'required|integer|exists:contact_reminders,id',
        ];
    }


    /**
     * Destroy a reminder.
     */
    public function execute(array $data): void
    {
        $this->validateRules($data);

        $this->reminder = $this->contact->reminders()
            ->findOrFail($data['contact_reminder_id']);

        $this->reminder->delete();

        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();
    }
}
