<?php

namespace App\Domains\Contact\ManageAvatar\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\Contact;
use App\Models\ContactFeedItem;
use Carbon\Carbon;

class DestroyAvatar extends DeathGunContactService
{
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
        ];
    }

    /**
     * Remove the current file used as avatar and put the default avatar back.
     */
    public function execute(array $data): Contact
    {
        $this->data = $data;
        $this->validate();

        $this->deleteCurrentAvatar();
        $this->updateLastEditedDate();
        $this->createFeedItem();

        return $this->contact;
    }

    private function validate(): void
    {
        $this->validateRules($this->data);
    }

    private function deleteCurrentAvatar(): void
    {
        if ($this->contact->file) {
            $this->contact->file->delete();
        }

        $this->contact->file_id = null;
    }

    private function updateLastEditedDate(): void
    {
        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();
    }

    private function createFeedItem(): void
    {
        ContactFeedItem::create([
            'author_id' => $this->author->id,
            'contact_id' => $this->contact->id,
            'action' => ContactFeedItem::ACTION_CHANGE_AVATAR,
        ]);
    }
}
