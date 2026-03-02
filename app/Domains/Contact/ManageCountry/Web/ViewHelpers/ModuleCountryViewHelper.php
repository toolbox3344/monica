<?php

namespace App\Domains\Contact\ManageCountry\Web\ViewHelpers;

use App\Enums\Country;
use App\Models\Contact;
use Illuminate\Support\Collection;

class ModuleCountryViewHelper
{

    public static function data(Contact $contact): array
    {
        return [
            'country' => $contact->country,
            'countries' => self::list($contact),
            'url' => [
                'update' => route('contact.country.update', [
                    'vault' => $contact->vault_id,
                    'contact' => $contact->id,
                ]),
            ],
        ];
    }

    private static function list(Contact $contact): Collection
    {
        return collect(Country::cases())->map(fn (Country $country) => self::dto($country, $contact));
    }

    public static function dto(Country $country, Contact $contact): array
    {
        return [
            'name' => $country->value,
            'selected' => $country->value === $contact->country,
        ];
    }

}
