# Event Management API

API REST de gestion d'événements et d'inscriptions en ligne avec gestion des capacités.

## Stack technique

- **Backend** : Laravel 13 (API mode)
- **Base de données** : SQLite
- **Conteneurisation** : Docker + Docker Compose
- **Tests** : Pest

---

## Lancement du projet

### Prérequis

- Docker
- Docker Compose

### Démarrage en une commande

```bash
git clone <url-du-repo>
cd back
docker compose up --build
```

L'API est disponible sur **http://localhost:8000/api**

> Le `.env` est généré automatiquement depuis `.env.example` au premier lancement.  
> La base de données SQLite est créée et migrée automatiquement.  
> Les données de test sont insérées au premier démarrage uniquement (seeder idempotent).

### Lancer les tests

Le projet doit être démarré avant de lancer les tests.

```bash
docker compose exec backend-event-registration ./vendor/bin/pest
```

---

## Collection Postman

Toutes les requêtes sont préconfigurées dans la collection Postman fournie.

### Importer la collection

1. Ouvrir **Postman**
2. Cliquer sur **Import** en haut à gauche
3. Sélectionner le fichier **`postman_collection.json`** à la racine du projet
4. Cliquer sur **Import**

### Variables préconfigurées

| Variable | Valeur par défaut |
|----------|------------------|
| `base_url` | `http://localhost:8000/api` |
| `admin_token` | `sk-evt-admin-2025-dev-12345abcdef67890` |

> Si vous modifiez `ADMIN_TOKEN` dans le `.env`, pensez à mettre à jour la variable `admin_token` dans Postman.  
> Pour modifier une variable : cliquer sur la collection → onglet **Variables**.

### Requêtes disponibles dans la collection

**Dossier Événements**
- `GET` Lister les événements
- `GET` Lister avec recherche (`?search=`)
- `GET` Lister avec filtre date (`?date=`)
- `GET` Lister avec pagination (`?limit=` `?page=`)
- `GET` Détail d'un événement
- `GET` Détail — événement inexistant → **404**
- `POST` Créer un événement ✅
- `POST` Créer — sans token → **401**
- `POST` Créer — champs manquants → **400**
- `POST` Créer — mauvais format date → **400**
- `PUT` Mettre à jour un événement ✅
- `PUT` Mettre à jour — sans token → **401**
- `DELETE` Supprimer un événement ✅
- `DELETE` Supprimer — sans token → **401**

**Dossier Inscriptions**
- `POST` S'inscrire à un événement → **201**
- `POST` S'inscrire — événement complet → **422**
- `POST` S'inscrire — email déjà inscrit → **409**
- `POST` S'inscrire — email invalide → **400**
- `POST` S'inscrire — champs manquants → **400**
- `GET` Lister les inscriptions d'un événement
- `DELETE` Annuler une inscription

---

## Authentification

Les routes de création, modification et suppression d'événements sont protégées par un Bearer token statique défini dans le `.env`.

```http
Authorization: Bearer sk-evt-admin-2025-dev-12345abcdef67890
```

> Ce token est hardcodé car le sujet ne prévoit pas d'endpoint de login.  
> En production, ce mécanisme serait remplacé par un vrai système d'authentification.

---

## Endpoints

### Événements

| Méthode | Route | Auth | Description |
|---------|-------|------|-------------|
| GET | `/api/events` | Non | Liste. Supporte `?search=`, `?date=` et `?limit=` |
| POST | `/api/events` | ✅ | Crée un événement |
| GET | `/api/events/:id` | Non | Détail d'un événement |
| PUT | `/api/events/:id` | ✅ | Met à jour un événement |
| DELETE | `/api/events/:id` | ✅ | Supprime l'événement et ses inscriptions |

### Inscriptions

| Méthode | Route | Auth | Description |
|---------|-------|------|-------------|
| POST | `/api/events/:id/register` | Non | Inscrit un participant |
| GET | `/api/events/:id/registrations` | Non | Liste les participants |
| DELETE | `/api/registrations/:id` | Non | Annule une inscription |

---

## Codes HTTP

| Code | Situation |
|------|-----------|
| 200 | Succès |
| 201 | Création réussie |
| 204 | Suppression réussie |
| 400 | Champ manquant ou invalide |
| 401 | Token manquant ou invalide |
| 404 | Ressource introuvable |
| 409 | Email déjà inscrit (`DUPLICATE_EMAIL`) |
| 422 | Événement complet (`CAPACITY_REACHED`) |

---

## Données de test

Le seeder insère automatiquement des données au premier lancement :

| Event ID | Titre | Capacité | Inscrits | État |
|----------|-------|----------|----------|------|
| 1 | Workshop Laravel Avancé | 2 | 2 | **Complet** — teste `CAPACITY_REACHED` |
| 2 | Conférence Tech Ouaga 2025 | 50 | 1 | **Places disponibles** — teste une inscription réussie |
| 3 à 12 | Divers événements | - | 0 | Pour tester la pagination |

> **Astuce** : pour tester `DUPLICATE_EMAIL`, inscrivez-vous à l'event 2 avec `fatima@example.com` — cet email est déjà enregistré dans le seeder.

---

## Architecture

```bash
app/
├── Exceptions/         → Exceptions métier (CapacityReached, DuplicateEmail)
├── Http/
│   ├── Controllers/    → Controllers fins qui délèguent aux services
│   ├── Middleware/     → AdminTokenMiddleware
│   └── Requests/       → Validation des entrées (FormRequests)
├── Models/             → Event, Registration
└── Services/           → Logique métier (EventService, RegistrationService)
```

---

## Choix techniques

- **Transactions + `lockForUpdate()`** — protection contre les race conditions lors des inscriptions simultanées.
- **Suppression en cascade** — les inscriptions sont supprimées automatiquement via contrainte SQL `ON DELETE CASCADE`.
- **Seeder idempotent** — les données ne sont insérées qu'une seule fois, même si le conteneur redémarre.
- **SQLite** — choisi pour sa simplicité, zéro configuration. Passage à MySQL possible en modifiant `DB_CONNECTION` dans `.env`.
- **Token statique** — protection des routes d'écriture sans endpoint de login, conformément au cahier des charges.