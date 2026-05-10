<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventService
{
    /**
     * Liste les événements avec filtres optionnels et pagination.
     */
    public function list(array $filters): LengthAwarePaginator
    {
        $query = Event::withCount('registrations');

        $query
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(!empty($filters['date']), function ($query) use ($filters) {
                $query->where('date', 'like', $filters['date'] . '%');
            });

        return $query->orderBy('date')->paginate(10);
    }

    /**
     * Crée un événement.
     */
    public function create(array $data): Event
    {
        return Event::create($data);
    }

    /**
     * Met à jour un événement.
     */
    public function update(Event $event, array $data): Event
    {
        $event->update($data);
        return $event->fresh();
    }

    /**
     * Supprime un événement.
     * Les inscriptions sont supprimées en cascade via la contrainte BDD.
     */
    public function delete(Event $event): void
    {
        $event->delete();
    }
}
