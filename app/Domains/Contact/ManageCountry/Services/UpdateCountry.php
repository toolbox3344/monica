<?php

namespace App\Domains\Contact\ManageCountry\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Enums\Country;
use App\Interfaces\ServiceInterface;
use App\Models\Contact;
use App\Models\ContactFeedItem;
use App\Services\BaseService;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class UpdateCountry extends DeathGunContactService
{

    public function rules(): array
    {
        return [
            'account_id' => 'required|uuid|exists:accounts,id',
            'vault_id' => 'required|uuid|exists:vaults,id',
            'author_id' => 'required|uuid|exists:users,id',
            'contact_id' => 'required|uuid|exists:contacts,id',
            'country' => ['nullable', 'string', Rule::enum(Country::class)],
        ];
    }

    public function execute(array $data): Contact
    {
        $this->validateRules($data);

        $this->contact->country = $data['country'];
        $this->contact->save();

        $this->updateLastEditedDate();

        ContactFeedItem::create([
            'author_id' => $this->author->id,
            'contact_id' => $this->contact->id,
            'action' => ContactFeedItem::ACTION_COUNTRY_UPDATED,
        ]);

        return $this->contact;
    }

    private function updateLastEditedDate(): void
    {
        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();
    }


}
