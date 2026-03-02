<?php

namespace App\Mcp\Tools;

use App\Domains\Contact\ManageContact\Services\CreateContact;
use App\Domains\Contact\ManageCountry\Services\UpdateCountry;
use App\Enums\Country;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Gender;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Store a new contact.')]
class StoreContactTool extends Tool
{

    private ?Contact $contact = null;

    /**
     * Handle the tool request.
     * @throws \Throwable
     */
    public function handle(Request $request): Response
    {

        DB::beginTransaction();

        $this->contact = $this->saveContact($request);

        $this->saveCountry($request);

        DB::commit();

        return Response::text('Contact created successfully.');
    }

    private function saveContact(Request $request): Contact
    {
        return app(CreateContact::class)
            ->execute($this->buildData([
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'gender_id' => $this->getGenderByName($request->get('gender'))?->id,
                'listed' => true
            ]));
    }

    private function getGenderByName(string $name): ?Gender
    {
        return Account::find(Auth::user()->account_id)->genders
            ->firstWhere('name', $name);
    }

    private function saveCountry(Request $request): void
    {
        $country = $request->get('country');

        if ($country) {
            app(UpdateCountry::class)
                ->execute($this->buildData([
                    'country' => $country,
                ]));
        }
    }


    private function buildData(array $data): array
    {

        $data = array_merge([
            'account_id' => Auth::user()->account_id,
            'author_id' => Auth::id(),
            'vault_id' => config('mcp.vault_id'),
        ], $data);

        if ($this->contact) {
            $data['contact_id'] = $this->contact->id;
        }

        return $data;
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'first_name' => $schema->string()->required(),
            'last_name' => $schema->string(),
            'gender' => $schema->string()->enum(['Male', 'Female']),
            'country' => $schema->string()->enum(Country::cases()),
        ];

    }
}
