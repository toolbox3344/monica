<?php

namespace App\Domains\Contact\ManageNotes\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\ContactFeedItem;
use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UpdateNote extends DeathGunContactService
{
    private Note $note;

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
            'note_id' => 'required|integer|exists:notes,id',
            'emotion_id' => 'nullable|integer|exists:emotions,id',
            'title' => 'nullable|string|max:255',
            'body' => 'required|string|max:65535',
        ];
    }

    /**
     * Update a note.
     */
    public function execute(array $data): Note
    {
        $this->validateRules($data);

        $this->note = $this->contact->notes()
            ->findOrFail($data['note_id']);

        if ($this->valueOrNull($data, 'emotion_id')) {
            $this->account()->emotions()
                ->findOrFail($data['emotion_id']);
        }

        $this->note->body = $data['body'];
        $this->note->title = $this->valueOrNull($data, 'title');
        $this->note->emotion_id = $this->valueOrNull($data, 'emotion_id');
        $this->note->save();

        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();

        $this->createFeedItem();

        return $this->note;
    }

    private function createFeedItem(): void
    {
        $feedItem = ContactFeedItem::create([
            'author_id' => $this->author->id,
            'contact_id' => $this->contact->id,
            'action' => ContactFeedItem::ACTION_NOTE_UPDATED,
            'description' => Str::words($this->note->body, 10, '…'),
        ]);
        $this->note->feedItem()->save($feedItem);
    }
}
