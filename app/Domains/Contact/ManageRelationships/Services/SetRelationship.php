<?php

namespace App\Domains\Contact\ManageRelationships\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\Contact;
use App\Models\RelationshipType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SetRelationship extends DeathGunContactService
{
    /**
     * Get the validation rules that apply to the service.
     */
    public function rules(): array
    {
        return [
            'account_id' => 'required|uuid|exists:accounts,id',
            'vault_id' => 'required|uuid|exists:vaults,id',
            'author_id' => 'required|uuid|exists:users,id',
            'relationship_type_id' => 'required|integer|exists:relationship_types,id',
            'contact_id' => 'required|uuid|exists:contacts,id',
            'other_contact_id' => 'required|uuid|exists:contacts,id',
        ];
    }


    /**
     * Set a relationship between two contacts.
     * When a relationship is created (father -> son), we need to create
     * the inverse relationship (son -> father) as well.
     */
    public function execute(array $data): void
    {
        $this->validateRules($data);

        $otherContact = $this->vault->contacts()
            ->findOrFail($data['other_contact_id']);

        $relationshipType = RelationshipType::findOrFail($data['relationship_type_id']);
        if ($relationshipType->groupType->account_id !== $data['account_id']) {
            throw new ModelNotFoundException;
        }

        // create the relationships
        $this->setRelationship($this->contact, $otherContact, $relationshipType);

        $this->updateLastEditedDate();
    }

    private function setRelationship(Contact $contact, Contact $otherContact, RelationshipType $relationshipType): void
    {
        $contact->relationships()->syncWithoutDetaching([
            $otherContact->id => [
                'relationship_type_id' => $relationshipType->id,
            ],
        ]);
    }

    private function updateLastEditedDate(): void
    {
        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();
    }
}
