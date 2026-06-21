<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContactResource;
use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class ContactMeController extends Controller
{
    public function index()
    {
        $contacts = Contact::latest()->paginate(10);
        return ContactResource::collection($contacts);
    }

    public function store(StoreContactRequest $request, NotificationService $notificationService)
    {
        $contact = Contact::create($request->validated());

        // 🔔 create notifications
        $notificationService->createContactNotification($contact);

        return new ContactResource($contact);
    }

    public function show(Contact $contact)
    {
        return new ContactResource($contact);
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->json([
            'message' => 'Contact Deleted Successfully !'
        ]);
    }
}
