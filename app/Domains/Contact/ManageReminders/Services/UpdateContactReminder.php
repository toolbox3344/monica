<?php

namespace App\Domains\Contact\ManageReminders\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\ContactReminder;
use Carbon\Carbon;

class UpdateContactReminder extends DeathGunContactService
{
    private ContactReminder $reminder;

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
            'contact_reminder_id' => 'required|integer|exists:contact_reminders,id',
            'label' => 'required|string|max:255',
            'day' => 'nullable|integer',
            'month' => 'nullable|integer',
            'year' => 'nullable|integer',
            'type' => 'required|string:255',
            'frequency_number' => 'nullable|integer',
        ];
    }

    /**
     * Update a reminder.
     */
    public function execute(array $data): ContactReminder
    {
        $this->data = $data;
        $this->validate();

        $this->update();
        $this->deleteOldScheduledReminders();
        $this->updateLastUpdatedDate();
        $this->scheduledReminderForAllUsersInVault();

        return $this->reminder;
    }

    private function validate(): void
    {
        $this->validateRules($this->data);

        $this->reminder = $this->contact->reminders()
            ->findOrFail($this->data['contact_reminder_id']);
    }

    private function update(): void
    {
        $this->reminder->label = $this->data['label'];
        $this->reminder->day = $this->data['day'];
        $this->reminder->month = $this->data['month'];
        $this->reminder->year = $this->data['year'];
        $this->reminder->type = $this->data['type'];
        $this->reminder->frequency_number = $this->valueOrNull($this->data, 'frequency_number');
        $this->reminder->save();
    }

    private function deleteOldScheduledReminders(): void
    {
        $this->reminder->userNotificationChannels()->detach();
    }

    private function scheduledReminderForAllUsersInVault(): void
    {
        foreach ($this->vault->users as $user) {
            (new ScheduleContactReminderForUser)->execute([
                'contact_reminder_id' => $this->reminder->id,
                'user_id' => $user->id,
            ]);
        }
    }

    private function updateLastUpdatedDate(): void
    {
        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();
    }
}
