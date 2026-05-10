<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Idempotence du seeder
         *
         * Ce seeder est conçu pour être idempotent 
         */
        if (Event::count() > 0) {
            return;
        }

        // -------------------------------------------------------
        // EVENTS
        // -------------------------------------------------------
        $events = [
            // Event 1 — COMPLET (capacité 2, 2 inscrits)
            // Pour tester CAPACITY_REACHED → HTTP 422
            [
                'id'          => 1,
                'title'       => 'Workshop Laravel Avancé',
                'description' => 'Architecture, tests et bonnes pratiques Laravel 11.',
                'date'        => '2025-11-22T14:00:00Z',
                'location'    => 'Ouagadougou, Campus Numérique',
                'capacity'    => 2,
            ],
            // Event 2 — PLACES DISPONIBLES (capacité 50, 1 inscrit)
            // Pour tester une inscription réussie → HTTP 201
            [
                'id'          => 2,
                'title'       => 'Conférence Tech Ouaga 2025',
                'description' => 'Les dernières tendances en développement web et mobile.',
                'date'        => '2025-11-15T09:00:00Z',
                'location'    => 'Ouagadougou, Salle des fêtes de Ouaga 2000',
                'capacity'    => 50,
            ],
            // Events 3 à 12 — pour tester la pagination
            [
                'id'          => 3,
                'title'       => 'Meetup Flutter Burkina',
                'description' => 'Découverte de Flutter et Dart pour le mobile.',
                'date'        => '2025-12-05T18:00:00Z',
                'location'    => 'Ouagadougou, Espace Numérique Ouvert',
                'capacity'    => 30,
            ],
            [
                'id'          => 4,
                'title'       => 'Hackathon FinTech Africa',
                'description' => 'Construire des solutions de paiement mobile pour l\'Afrique.',
                'date'        => '2025-12-20T08:00:00Z',
                'location'    => 'Ouagadougou, Hôtel Azalaï',
                'capacity'    => 80,
            ],
            [
                'id'          => 5,
                'title'       => 'Formation React & Next.js',
                'description' => 'De zéro à la mise en production avec Next.js 14.',
                'date'        => '2026-01-10T09:00:00Z',
                'location'    => 'Ouagadougou, Institut du Numérique',
                'capacity'    => 25,
            ],
            [
                'id'          => 6,
                'title'       => 'DevFest Burkina 2026',
                'description' => 'La grande conférence annuelle des développeurs.',
                'date'        => '2026-01-25T08:00:00Z',
                'location'    => 'Ouagadougou, Palais des Sports',
                'capacity'    => 500,
            ],
            [
                'id'          => 7,
                'title'       => 'Atelier Docker & DevOps',
                'description' => 'Conteneurisation et déploiement continu en pratique.',
                'date'        => '2026-02-05T10:00:00Z',
                'location'    => 'Ouagadougou, Campus Numérique',
                'capacity'    => 20,
            ],
            [
                'id'          => 8,
                'title'       => 'Séminaire Cybersécurité',
                'description' => 'Les bonnes pratiques de sécurité pour les développeurs.',
                'date'        => '2026-02-14T09:00:00Z',
                'location'    => 'Ouagadougou, Hôtel Laïco',
                'capacity'    => 60,
            ],
            [
                'id'          => 9,
                'title'       => 'Bootcamp Python & Data Science',
                'description' => 'Introduction à la data science avec Python.',
                'date'        => '2026-03-01T08:00:00Z',
                'location'    => 'Ouagadougou, Université Ouaga II',
                'capacity'    => 40,
            ],
            [
                'id'          => 10,
                'title'       => 'Forum Startups Tech Afrique',
                'description' => 'Rencontre entre startups tech et investisseurs africains.',
                'date'        => '2026-03-15T09:00:00Z',
                'location'    => 'Ouagadougou, Sofitel',
                'capacity'    => 150,
            ],
            [
                'id'          => 11,
                'title'       => 'Conférence Intelligence Artificielle',
                'description' => 'IA générative et impacts sur les métiers du numérique.',
                'date'        => '2026-04-10T09:00:00Z',
                'location'    => 'Ouagadougou, Centre Culturel Français',
                'capacity'    => 100,
            ],
            [
                'id'          => 12,
                'title'       => 'Atelier Git & Bonnes Pratiques',
                'description' => 'Workflow Git en équipe et gestion de versions.',
                'date'        => '2026-04-20T14:00:00Z',
                'location'    => 'Ouagadougou, Campus Numérique',
                'capacity'    => 20,
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }

        // -------------------------------------------------------
        // REGISTRATIONS
        // -------------------------------------------------------

        // Event 1 — 2 inscrits sur capacité 2 → COMPLET
        Registration::insert([
            [
                'event_id'      => 1,
                'first_name'    => 'Aminata',
                'last_name'     => 'Ouédraogo',
                'email'         => 'aminata@example.com',
                'registered_at' => now(),
            ],
            [
                'event_id'      => 1,
                'first_name'    => 'Moussa',
                'last_name'     => 'Kaboré',
                'email'         => 'moussa@example.com',
                'registered_at' => now(),
            ],
        ]);

        // Event 2 — 1 inscrit sur capacité 50 → PLACES DISPONIBLES
        Registration::insert([
            [
                'event_id'      => 2,
                'first_name'    => 'Fatima',
                'last_name'     => 'Sawadogo',
                'email'         => 'fatima@example.com',
                'registered_at' => now(),
            ],
        ]);
    }
}