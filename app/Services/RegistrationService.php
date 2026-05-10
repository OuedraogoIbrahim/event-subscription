<?php

namespace App\Services;

use App\Exceptions\CapacityReachedException;
use App\Exceptions\DuplicateEmailException;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class RegistrationService
{

    /**
     * Liste les inscriptions d'un événement.
     */

    public function list(Event $event)
    {
        return $event->registrations()->get();
    }
    /**
     * Inscrit un participant avec vérification atomique.
     *
     * La transaction + lockForUpdate() garantit qu'aucune race condition
     * ne peut faire dépasser la capacité sous charge concurrente.
     * Sans ce mécanisme, deux requêtes simultanées pourraient toutes les deux
     * passer la vérification de capacité et créer une inscription en trop.
     */
    public function register(Event $event, array $data): Registration
    {
        return DB::transaction(function () use ($event, $data) {

            // Verrou exclusif sur la ligne — les requêtes concurrentes attendent ici
            $event = Event::lockForUpdate()->findOrFail($event->id);

            // Vérification capacité
            $count = $event->registrations()->count();
            if ($count >= $event->capacity) {
                throw new CapacityReachedException();
            }

            // Vérification unicité email
            $alreadyRegistered = $event->registrations()
                ->where('email', $data['email'])
                ->exists();

            if ($alreadyRegistered) {
                throw new DuplicateEmailException();
            }

            return $event->registrations()->create([
                'first_name'    => $data['first_name'],
                'last_name'     => $data['last_name'],
                'email'         => $data['email'],
                'registered_at' => now(),
            ]);
        });
    }

    /**
     * Annule une inscription.
     */
    public function cancel(Registration $registration): void
    {
        $registration->delete();
    }
}
