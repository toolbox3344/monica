<?php

namespace App\Mcp\Tools;

use App\Domains\Contact\ManageContact\Services\UpdateContact;
use App\Domains\Contact\ManageCountry\Services\UpdateCountry;
use App\Enums\Country;
use App\Models\Contact;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Edit a contact.')]
class EditContactTool extends BaseTool
{

    /**
     * Handle the tool request.
     * @throws \Throwable
     */
    public function handle(Request $request): Response
    {

        DB::beginTransaction();

        $this->contact = $this->editContact($request);

        $this->editCountry($request);

        DB::commit();

        return Response::text('Contact edited successfully.');
    }

    private function editContact(Request $request): Contact
    {
        $contact = Contact::find($request->get('contact_id'));

        $gender = $request->get('gender');

        if ($gender) {
            $gender = $this->getGenderByName($gender)?->id;
        } else {
            $gender = $contact->gender_id;
        }

        return app(UpdateContact::class)
            ->execute($this->buildData([
                'contact_id' => $request->get('contact_id'),
                'first_name' => $request->get('first_name', $contact->first_name),
                'last_name' => $request->get('last_name', $contact->last_name),
                'gender_id' => $gender
            ]));
    }


    private function editCountry(Request $request): void
    {
        $country = $request->get('country');

        if ($country) {
            app(UpdateCountry::class)
                ->execute($this->buildData([
                    'country' => $country,
                ]));
        }
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'contact_id' => $schema->string()->required(),
            'first_name' => $schema->string(),
            'last_name' => $schema->string(),
            'gender' => $schema->string()->enum(['Male', 'Female']),
            'country' => $schema->string()->enum(Country::cases()),
        ];

    }
}
