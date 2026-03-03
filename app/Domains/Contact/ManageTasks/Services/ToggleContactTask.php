<?php

namespace App\Domains\Contact\ManageTasks\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\ContactTask;
use Carbon\Carbon;

class ToggleContactTask extends DeathGunContactService
{
    private ContactTask $task;

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
            'contact_task_id' => 'required|integer|exists:contact_tasks,id',
        ];
    }


    /**
     * Toggle a contact task.
     */
    public function execute(array $data): ContactTask
    {
        $this->validateRules($data);

        $this->task = $this->contact->tasks()
            ->findOrFail($data['contact_task_id']);

        $this->task->completed = ! $this->task->completed;
        $this->task->completed_at = Carbon::now();
        $this->task->save();

        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();

        return $this->task;
    }
}
