<?php

namespace App\Domains\Contact\ManagePets\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\ContactFeedItem;
use App\Models\Pet;
use Carbon\Carbon;

class DestroyPet extends DeathGunContactService
{
    private Pet $pet;

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
            'pet_id' => 'required|integer|exists:pets,id',
        ];
    }


    /**
     * Destroy a pet.
     */
    public function execute(array $data): void
    {
        $this->validateRules($data);

        $this->pet = $this->contact->pets()
            ->findOrFail($data['pet_id']);

        $this->pet->delete();

        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();

        $this->createFeedItem();
    }

    private function createFeedItem(): void
    {
        ContactFeedItem::create([
            'author_id' => $this->author->id,
            'contact_id' => $this->contact->id,
            'action' => ContactFeedItem::ACTION_PET_DESTROYED,
            'description' => $this->pet->petCategory->name,
        ]);
    }
}
