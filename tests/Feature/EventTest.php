<?php

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Helper — headers admin
function adminHeaders(): array
{
    return ['Authorization' => 'Bearer ' . config('app.admin_token')];
}

// Helper — créer un event de test
function makeEvent(array $overrides = []): Event
{
    return Event::create(array_merge([
        'title'    => 'Event Test',
        'date'     => '2025-11-15T18:00:00Z',
        'location' => 'Ouagadougou',
        'capacity' => 10,
    ], $overrides));
}

// -----------------------------------------------------------------------
// GET /api/events
// -----------------------------------------------------------------------
it('retourne la liste des événements', function () {
    makeEvent(['title' => 'Event A']);
    makeEvent(['title' => 'Event B']);

    $this->getJson('/api/events')
        ->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data.data');
});

it('filtre les événements par search', function () {
    makeEvent(['title' => 'Conférence Laravel']);
    makeEvent(['title' => 'Meetup Flutter']);

    $this->getJson('/api/events?search=Laravel')
        ->assertStatus(200)
        ->assertJsonCount(1, 'data.data')
        ->assertJsonPath('data.data.0.title', 'Conférence Laravel');
});

it('filtre les événements par date', function () {
    makeEvent(['date' => '2025-11-15T18:00:00Z']);
    makeEvent(['date' => '2026-01-10T09:00:00Z']);

    $this->getJson('/api/events?date=2025-11')
        ->assertStatus(200)
        ->assertJsonCount(1, 'data.data');
});

it('respecte le paramètre limit', function () {
    foreach (range(1, 15) as $i) {
        makeEvent(['title' => "Event {$i}"]);
    }

    $this->getJson('/api/events?limit=5')
        ->assertStatus(200)
        ->assertJsonCount(5, 'data.data');
});

// -----------------------------------------------------------------------
// POST /api/events
// -----------------------------------------------------------------------
it('crée un événement avec token valide', function () {
    $payload = [
        'title'    => 'Nouvel événement',
        'date'     => '2025-12-01T10:00:00Z',
        'location' => 'Ouagadougou',
        'capacity' => 50,
    ];

    $this->postJson('/api/events', $payload, adminHeaders())
        ->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.title', 'Nouvel événement');
});

it('refuse la création sans token', function () {
    $this->postJson('/api/events', ['title' => 'Hack'])
        ->assertStatus(401)
        ->assertJsonPath('error', 'UNAUTHORIZED');
});

it('retourne 400 si les champs obligatoires manquent', function () {
    $this->postJson('/api/events', [], adminHeaders())
        ->assertStatus(400)
        ->assertJsonPath('error', 'VALIDATION_ERROR');
});

it('retourne 400 si la date est dans un mauvais format', function () {
    $this->postJson('/api/events', [
        'title'    => 'Event',
        'date'     => '15-11-2025',
        'location' => 'Ouagadougou',
        'capacity' => 10,
    ], adminHeaders())
        ->assertStatus(400)
        ->assertJsonPath('error', 'VALIDATION_ERROR');
});

// -----------------------------------------------------------------------
// GET /api/events/:id
// -----------------------------------------------------------------------
it('retourne un événement par son id', function () {
    $event = makeEvent(['title' => 'Event précis']);

    $this->getJson("/api/events/{$event->id}")
        ->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.title', 'Event précis');
});

it('retourne 404 pour un événement inexistant', function () {
    $this->getJson('/api/events/99999')
        ->assertStatus(404)
        ->assertJsonPath('error', 'NOT_FOUND');
});

// -----------------------------------------------------------------------
// PUT /api/events/:id
// -----------------------------------------------------------------------
it('met à jour un événement', function () {
    $event = makeEvent(['title' => 'Ancien titre']);

    $this->putJson("/api/events/{$event->id}", [
        'title' => 'Nouveau titre',
    ], adminHeaders())
        ->assertStatus(200)
        ->assertJsonPath('data.title', 'Nouveau titre');
});

it('refuse la mise à jour sans token', function () {
    $event = makeEvent();

    $this->putJson("/api/events/{$event->id}", ['title' => 'Hack'])
        ->assertStatus(401);
});

// -----------------------------------------------------------------------
// DELETE /api/events/:id
// -----------------------------------------------------------------------
it('supprime un événement et ses inscriptions', function () {
    $event = makeEvent();
    $event->registrations()->create([
        'first_name'    => 'Aminata',
        'last_name'     => 'Ouédraogo',
        'email'         => 'aminata@example.com',
        'registered_at' => now(),
    ]);

    $this->deleteJson("/api/events/{$event->id}", [], adminHeaders())
        ->assertStatus(204);

    $this->assertDatabaseMissing('events', ['id' => $event->id]);
    $this->assertDatabaseMissing('registrations', ['event_id' => $event->id]);
});

it('refuse la suppression sans token', function () {
    $event = makeEvent();

    $this->deleteJson("/api/events/{$event->id}")
        ->assertStatus(401);
});