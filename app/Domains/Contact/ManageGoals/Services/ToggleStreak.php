<?php

namespace App\Domains\Contact\ManageGoals\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\Goal;
use App\Models\Streak;
use Carbon\Carbon;

class ToggleStreak extends DeathGunContactService
{
    private Goal $goal;

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
            'goal_id' => 'nullable|integer|exists:goals,id',
            'happened_at' => 'required|date_format:Y-m-d',
        ];
    }

    /**
     * Log a streak for a given goal.
     */
    public function execute(array $data): void
    {
        $this->data = $data;

        $this->validate();

        $entry = $this->goal->streaks()
            ->whereDate('happened_at', $this->data['happened_at'])
            ->first();

        if ($entry) {
            $this->destroyStreak($entry);
        } else {
            $this->createStreak();
        }

        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();
    }

    private function validate(): void
    {
        $this->validateRules($this->data);

        $this->goal = $this->contact->Goals()
            ->findOrFail($this->data['goal_id']);
    }

    private function destroyStreak(Streak $streak): void
    {
        $streak->delete();
    }

    private function createStreak(): void
    {
        Streak::create([
            'goal_id' => $this->goal->id,
            'happened_at' => $this->data['happened_at'],
        ]);
    }
}
