<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * GET /api/events
     * Supporte ?search= et ?date= et ?page=
     */
    public function index(Request $request): JsonResponse
    {
        $events = $this->eventService->list($request->only(['search', 'date']));

        return response()->json([
            'success' => true,
            'data'    => $events,
        ], 200);
    }

    /**
     * POST /api/events  [protégé]
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = $this->eventService->create($request->validated());

        return response()->json([
            'success' => true,
            'data'    => $event,
        ], 201);
    }

    /**
     * GET /api/events/:id
     */
    public function show(Event $event): JsonResponse
    {
        $event->loadCount('registrations');

        return response()->json([
            'success' => true,
            'data'    => $event,
        ], 200);
    }

    /**
     * PUT /api/events/:id  [protégé]
     */
    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $event = $this->eventService->update($event, $request->validated());

        return response()->json([
            'success' => true,
            'data'    => $event,
        ], 200);
    }

    /**
     * DELETE /api/events/:id  [protégé]
     */
    public function destroy(Event $event): JsonResponse
    {
        $this->eventService->delete($event);

        return response()->json([
            'success' => true,
            'data'    => null,
        ], 204);
    }
}