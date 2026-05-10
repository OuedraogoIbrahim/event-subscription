<?php

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Helper — créer un event avec capacité
function eventWithCapacity(int $capacity): Event
{
    return Event::create([
        'title'    => 'Event Test',
        'date'     => '2025-11-15T18:00:00Z',
        'location' => 'Ouagadougou',
        'capacity' => $capacity,
    ]);
}

// Helper — payload d'inscription
function registerPayload(string $email = 'test@example.com'): array
{
    return [
        'first_name' => 'Aminata',
        'last_name'  => 'Ouédraogo',
        'email'      => $email,
    ];
}

// -----------------------------------------------------------------------
// Inscription réussie
// -----------------------------------------------------------------------
it('inscrit un participant avec succès', function () {
    $event = eventWithCapacity(10);

    $this->postJson("/api/events/{$event->id}/register", registerPayload())
        ->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => [
            'id', 'eventId', 'firstName', 'lastName', 'email', 'registeredAt'
        ]]);
});

// -----------------------------------------------------------------------
// Règle métier : capacité
// -----------------------------------------------------------------------
it('retourne 422 quand l\'événement est complet — CAPACITY_REACHED', function () {
    $event = eventWithCapacity(1);

    // Première inscription — OK
    $this->postJson("/api/events/{$event->id}/register", registerPayload('first@example.com'))
        ->assertStatus(201);

    // Deuxième inscription — événement complet
    $this->postJson("/api/events/{$event->id}/register", registerPayload('second@example.com'))
        ->assertStatus(422)
        ->assertJsonPath('error', 'CAPACITY_REACHED');
});

// -----------------------------------------------------------------------
// Règle métier : unicité email
// -----------------------------------------------------------------------
it('retourne 409 pour un email déjà inscrit — DUPLICATE_EMAIL', function () {
    $event = eventWithCapacity(10);

    $this->postJson("/api/events/{$event->id}/register", registerPayload())
        ->assertStatus(201);

    $this->postJson("/api/events/{$event->id}/register", registerPayload())
        ->assertStatus(409)
        ->assertJsonPath('error', 'DUPLICATE_EMAIL');
});

// -----------------------------------------------------------------------
// Validation des champs
// -----------------------------------------------------------------------
it('retourne 400 si l\'email est invalide', function () {
    $event = eventWithCapacity(10);

    $this->postJson("/api/events/{$event->id}/register", [
        'first_name' => 'Aminata',
        'last_name'  => 'Ouédraogo',
        'email'      => 'pas-un-email',
    ])->assertStatus(400)
      ->assertJsonPath('error', 'VALIDATION_ERROR');
});

it('retourne 400 si des champs sont manquants', function () {
    $event = eventWithCapacity(10);

    $this->postJson("/api/events/{$event->id}/register", [])
        ->assertStatus(400)
        ->assertJsonPath('error', 'VALIDATION_ERROR');
});

// -----------------------------------------------------------------------
// Liste des inscriptions
// -----------------------------------------------------------------------
it('retourne la liste des inscriptions d\'un événement', function () {
    $event = eventWithCapacity(10);

    $this->postJson("/api/events/{$event->id}/register", registerPayload('first@example.com'));
    $this->postJson("/api/events/{$event->id}/register", registerPayload('second@example.com'));

    $this->getJson("/api/events/{$event->id}/registrations")
        ->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data');
});

// -----------------------------------------------------------------------
// Annulation
// -----------------------------------------------------------------------
it('annule une inscription', function () {
    $event = eventWithCapacity(10);

    $response = $this->postJson("/api/events/{$event->id}/register", registerPayload());
    $registrationId = $response->json('data.id');

    $this->deleteJson("/api/registrations/{$registrationId}")
        ->assertStatus(204);

    $this->assertDatabaseMissing('registrations', ['id' => $registrationId]);
});

// -----------------------------------------------------------------------
// Libération de place après annulation
// -----------------------------------------------------------------------
it('libère une place après annulation', function () {
    $event = eventWithCapacity(1);

    $response = $this->postJson("/api/events/{$event->id}/register", registerPayload('first@example.com'));
    $registrationId = $response->json('data.id');

    // Événement complet
    $this->postJson("/api/events/{$event->id}/register", registerPayload('second@example.com'))
        ->assertStatus(422);

    // Annulation
    $this->deleteJson("/api/registrations/{$registrationId}")
        ->assertStatus(204);

    // Nouvelle inscription possible
    $this->postJson("/api/events/{$event->id}/register", registerPayload('second@example.com'))
        ->assertStatus(201);
});