<?php

namespace App\Domains\Contact\ManageLabels\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Interfaces\ServiceInterface;
use App\Models\ContactFeedItem;
use App\Models\Label;
use App\Services\BaseService;
use Carbon\Carbon;

class RemoveLabel extends DeathGunContactService
{
    private Label $label;

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
            'label_id' => 'required|integer|exists:labels,id',
        ];
    }


    /**
     * Remove a label from the contact.
     */
    public function execute(array $data): Label
    {
        $this->validateRules($data);

        $this->label = $this->vault->labels()
            ->findOrFail($data['label_id']);

        $this->contact->labels()->detach($this->label);

        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();

        $this->createFeedItem();

        return $this->label;
    }

    private function createFeedItem(): void
    {
        $feedItem = ContactFeedItem::create([
            'author_id' => $this->author->id,
            'contact_id' => $this->contact->id,
            'action' => ContactFeedItem::ACTION_LABEL_REMOVED,
            'description' => $this->label->name,
        ]);
        $this->label->feedItem()->save($feedItem);
    }
}
