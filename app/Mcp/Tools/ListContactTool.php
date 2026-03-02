<?php

namespace App\Mcp\Tools;

use App\Enums\Country;
use App\Models\Contact;
use App\Models\Gender;
use App\Models\Vault;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Throwable;

#[Description('List contacts.')]
class ListContactTool extends Tool
{

    /**
     * Handle the tool request.
     * @throws Throwable
     */
    public function handle(Request $request): ResponseFactory
    {

        $firstName = $request->get('first_name');
        $lastName = $request->get('last_name');
        $gender = $request->get('gender');
        $country = $request->get('country');

        $contacts = Vault::find(config('mcp.vault_id'))
            ->contacts()
            ->when($firstName, fn($query) => $query->whereLike('first_name', $firstName))
            ->when($lastName, fn($query) => $query->whereLike('last_name', $lastName))
            ->when($gender, fn($query) => $query->where('gender_id', $gender))
            ->when($country, fn($query) => $query->where('country_id', $country))
            ->where('listed', true)
            ->cursorPaginate(perPage: 15, cursor: $request->get('cursor'));


        return Response::structured([
            'contacts' => collect($contacts->items())->map(fn(Contact $contact) => [
                'id' => $contact->id,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'gender' => $contact->gender,
                'country' => $contact->country,
            ]),
            'cursor' => $contacts->nextCursor()?->encode(),
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'first_name' => $schema->string(),
            'last_name' => $schema->string(),
            'gender' => $schema->string()->enum([Gender::MALE, Gender::FEMALE]),
            'country' => $schema->string()->enum(Country::cases()),
            'cursor' => $schema->string()
                ->nullable()
                ->description('Use the cursor returned by the previous response to load the next page of contacts.'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'contacts' => $schema->array()->items($schema->object([
                'id' => $schema->string()->required(),
                'first_name' => $schema->string()->required(),
                'last_name' => $schema->string(),
                'gender' => $schema->string(),
                'country' => $schema->string(),
            ])),
            'cursor' => $schema->string()
                ->description('Cursor to send in the next request to continue loading contacts, or null if there are no more results.'),
        ];
    }

}
