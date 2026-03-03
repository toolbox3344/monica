<?php

namespace App\Domains\Contact\ManageGoals\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\ContactFeedItem;
use App\Models\Goal;
use Carbon\Carbon;

class UpdateGoal extends DeathGunContactService
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
            'name' => 'nullable|string|max:255',
        ];
    }

    /**
     * Update a goal.
     */
    public function execute(array $data): Goal
    {
        $this->data = $data;
        $this->validateRules($data);

        $this->goal = $this->contact->goals()
            ->findOrFail($data['goal_id']);

        $this->goal->name = $data['name'];
        $this->goal->save();

        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();

        $this->createFeedItem();

        return $this->goal;
    }

    private function createFeedItem(): void
    {
        $feedItem = ContactFeedItem::create([
            'author_id' => $this->author->id,
            'contact_id' => $this->contact->id,
            'action' => ContactFeedItem::ACTION_GOAL_UPDATED,
            'description' => $this->data['name'],
        ]);
        $this->goal->feedItem()->save($feedItem);
    }
}
