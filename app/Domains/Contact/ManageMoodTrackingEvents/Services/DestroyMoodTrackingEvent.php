<?php

namespace App\Domains\Contact\ManageMoodTrackingEvents\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Interfaces\ServiceInterface;
use App\Models\ContactFeedItem;
use App\Models\MoodTrackingEvent;
use App\Services\BaseService;
use Carbon\Carbon;

class DestroyMoodTrackingEvent extends DeathGunContactService
{
    private MoodTrackingEvent $moodTrackingEvent;

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
            'mood_tracking_event_id' => 'required|integer|exists:mood_tracking_events,id',
        ];
    }

    /**
     * Destroy a mood tracking event.
     */
    public function execute(array $data): void
    {
        $this->validateRules($data);

        $this->moodTrackingEvent = $this->contact->moodTrackingEvents()
            ->findOrFail($data['mood_tracking_event_id']);

        $this->moodTrackingEvent->delete();

        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();

        $this->createFeedItem();
    }

    private function createFeedItem(): void
    {
        ContactFeedItem::create([
            'author_id' => $this->author->id,
            'contact_id' => $this->contact->id,
            'action' => ContactFeedItem::ACTION_MOOD_TRACKING_EVENT_DESTROYED,
            'description' => $this->moodTrackingEvent->moodTrackingParameter->label,
        ]);
    }
}
