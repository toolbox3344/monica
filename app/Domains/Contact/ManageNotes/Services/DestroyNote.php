<?php

namespace App\Domains\Contact\ManageNotes\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Interfaces\ServiceInterface;
use App\Models\ContactFeedItem;
use App\Models\Note;
use App\Services\BaseService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DestroyNote extends DeathGunContactService
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
        ];
    }


    /**
     * Destroy a note.
     */
    public function execute(array $data): void
    {
        $this->validateRules($data);

        $this->note = $this->contact->notes()
            ->findOrFail($data['note_id']);

        $this->createFeedItem();
        $this->note->delete();

        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();
    }

    private function createFeedItem(): void
    {
        ContactFeedItem::create([
            'author_id' => $this->author->id,
            'contact_id' => $this->contact->id,
            'action' => ContactFeedItem::ACTION_NOTE_DESTROYED,
            'description' => Str::words($this->note->body, 10, '…'),
        ]);
    }
}
