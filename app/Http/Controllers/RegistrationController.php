<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Models\Event;
use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;

class RegistrationController extends Controller
{
    private RegistrationService $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    /**
     * POST /api/events/:id/register
     */
    public function store(StoreRegistrationRequest $request, Event $event): JsonResponse
    {
        $registration = $this->registrationService->register($event, $request->validated());

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $registration->id,
                'eventId'      => $registration->event_id,
                'firstName'    => $registration->first_name,
                'lastName'     => $registration->last_name,
                'email'        => $registration->email,
                'registeredAt' => $registration->registered_at,
            ],
        ], 201);
    }

    /**
     * GET /api/events/:id/registrations
     */
    public function index(Event $event): JsonResponse
    {
        $registrations = $event->registrations;

        return response()->json([
            'success' => true,
            'data'    => $registrations,
        ], 200);
    }

    /**
     * DELETE /api/registrations/:id
     */
    public function destroy(Registration $registration): JsonResponse
    {
        $this->registrationService->cancel($registration);

        return response()->json([
            'success' => true,
            'data'    => null,
        ], 204);
    }
}
