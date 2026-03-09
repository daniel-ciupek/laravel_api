# TodoList — Laravel API

Backend REST API dla aplikacji TodoList, zbudowany w Laravel 11 z autentykacją opartą na Laravel Sanctum (cookie-based session authentication). API obsługuje zarządzanie zadaniami z priorytetami, datami wykonania, natural input parsing oraz podsumowaniami.

---

## Wymagania

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Laravel 11

---

## Technologie

- **Laravel 11** — framework PHP
- **Laravel Sanctum** — autentykacja przez cookies (SPA authentication)
- **Eloquent ORM** — komunikacja z bazą danych, scope'y, relacje
- **Laravel Policies** — autoryzacja operacji na taskach
- **FormRequest** — walidacja danych wejściowych
- **API Resources** — transformacja modeli do spójnych odpowiedzi JSON
- **Carbon** — manipulacja datami

---

## Instalacja i uruchomienie

```bash
# 1. Sklonuj repozytorium i przejdź do folderu backendu
cd laravel_api

# 2. Zainstaluj zależności PHP
composer install

# 3. Skopiuj plik środowiskowy
cp .env.example .env

# 4. Wygeneruj klucz aplikacji
php artisan key:generate

# 5. Skonfiguruj połączenie z bazą danych w pliku .env
# (patrz sekcja "Zmienne środowiskowe" poniżej)

# 6. Uruchom migracje i seeduj bazę danych
php artisan migrate --seed

# 7. Uruchom serwer deweloperski
php artisan serve
```

API będzie dostępne pod adresem: `http://localhost:8000`

---

## Zmienne środowiskowe

Najważniejsze zmienne w pliku `.env`:

```env
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_api
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:5173
```

`SANCTUM_STATEFUL_DOMAINS` — adresy frontendów które mogą używać autentykacji przez cookies. Musi zawierać adres aplikacji Vue (domyślnie `localhost:5173`).

---

## Struktura projektu

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── Auth/          # Kontrolery autentykacji (Login, Register, Logout, Me)
│   │   │   ├── V1/            # API v1 — podstawowe operacje na taskach
│   │   │   └── V2/            # API v2 — pełne API z priorytetami, datami, summary
│   │   │       ├── TaskController.php
│   │   │       ├── CompleteTaskController.php
│   │   │       ├── PriorityController.php
│   │   │       └── SummaryController.php
│   ├── Requests/              # Walidacja danych wejściowych
│   │   ├── StoreTaskRequest.php
│   │   ├── UpdateTaskRequest.php
│   │   ├── CompleteTaskRequest.php
│   │   ├── LoginRequest.php
│   │   └── RegisterRequest.php
│   └── Resources/             # Transformacja modeli do JSON
│       ├── TaskResource.php
│       ├── PriorityResource.php
│       └── TaskSummaryResource.php
├── Models/
│   ├── Task.php               # Model z scope'ami handleSort i handleFilter
│   ├── User.php               # Model z metodą tasksSummary()
│   └── Priority.php
├── Policies/
│   └── TaskPolicy.php         # Autoryzacja — użytkownik widzi tylko swoje taski
└── Services/
    └── TaskInputParser.php    # Parser natural input (@today, !high itp.)

routes/
├── api.php                    # Główny plik routingu — auth + v1 + v2
├── api/
│   ├── v1.php                 # Trasy API v1
│   └── v2.php                 # Trasy API v2 (chronione przez auth:sanctum)

database/
├── migrations/                # Historia schematu bazy danych
├── seeders/
│   └── DatabaseSeeder.php     # Seeder tworzący użytkowników testowych z taskami
└── factories/
    ├── UserFactory.php
    └── TaskFactory.php        # Factory z metodami withPriority, withDueDate itp.

tests/
├── Unit/
│   └── TaskInputParserTest.php
└── Feature/
    └── Api/
        ├── Auth/AuthTest.php
        └── V2/                # Testy endpointów: Task, Priority, DueDate, Summary, Authorization, NaturalInput
```

---

## Endpointy API

### Autentykacja (`/api/auth/`)

Przed pierwszym logowaniem aplikacja Vue pobiera CSRF cookie przez `GET /sanctum/csrf-cookie` — jest to wymagane przez Sanctum do autentykacji SPA.

`POST /api/auth/login` — logowanie, zwraca dane użytkownika i token. Przyjmuje `email` i `password`.

`POST /api/auth/register` — rejestracja nowego użytkownika. Przyjmuje `name`, `email`, `password` i `password_confirmation`.

`POST /api/auth/logout` — wylogowanie, usuwa aktualny token. Wymaga autentykacji.

`GET /api/auth/me` — zwraca dane aktualnie zalogowanego użytkownika. Wymaga autentykacji.

### Taski (`/api/v2/tasks/`) — wymagają autentykacji

`GET /api/v2/tasks` — lista tasków zalogowanego użytkownika. Obsługuje parametry query: `sort_by` (time, name, priority) oraz `due_date` (today, next3d, nextweek, overdue).

`POST /api/v2/tasks` — tworzenie nowego taska. Obsługuje natural input w polu `name` (np. `Kup mleko @tomorrow !high`). Przyjmuje `name`, `priority_id` (opcjonalne), `due_date` (opcjonalne).

`GET /api/v2/tasks/{id}` — pojedynczy task.

`PUT /api/v2/tasks/{id}` — aktualizacja taska. Przyjmuje `name`, `priority_id`, `due_date`.

`DELETE /api/v2/tasks/{id}` — usunięcie taska.

`PATCH /api/v2/tasks/{id}/complete` — oznaczenie taska jako ukończony/nieukończony. Przyjmuje `is_completed` (boolean).

### Priorytety i Summary

`GET /api/v2/priorities` — lista dostępnych priorytetów (high, medium, low).

`GET /api/v2/summaries` — podsumowanie tasków z filtrowaniem po okresie. Obsługuje parametr `period`: today, yesterday, thisweek, lastweek, thismonth, lastmonth.

---

## Natural Input

Parser `TaskInputParser` pozwala użytkownikowi wpisać nazwę taska wraz z priorytetem i datą w jednym polu tekstowym. Na przykład `Spotkanie z klientem @tomorrow !high` zostanie rozłożone na trzy osobne pola: name `Spotkanie z klientem`, due_date jutrzejsza data, priority_id identyfikator priorytetu high.

Obsługiwane tagi daty to `@today`, `@tomorrow`, `@next2d`, `@next3d`, `@nextweek` oraz dowolna data w formacie `@YYYY-MM-DD`. Obsługiwane tagi priorytetu to `!high`, `!medium`, `!low` — wielkość liter nie ma znaczenia.

Jeśli jawnie podasz `priority_id` lub `due_date` w body żądania, mają one pierwszeństwo nad tagami z natural input. Jeśli po usunięciu tagów nie pozostanie żadna nazwa taska, API zwraca błąd walidacji 422.

---

## Autoryzacja

Każdy użytkownik widzi i może modyfikować tylko swoje własne taski. Jest to egzekwowane przez `TaskPolicy` w każdej metodzie kontrolera przez `Gate::authorize()`. Próba dostępu do cudzego taska zwraca błąd 403 Forbidden.

---

## Uruchamianie testów

```bash
# Wszystkie testy
php artisan test

# Tylko testy jednostkowe parsera
php artisan test --filter TaskInputParserTest

# Tylko testy natural input
php artisan test --filter TaskNaturalInputTest

# Tylko testy autoryzacji
php artisan test --filter TaskAuthorizationTest
```

Testy używają osobnej bazy danych SQLite w pamięci (`:memory:`) skonfigurowanej w `phpunit.xml`, dzięki czemu nie dotykają deweloperskiej bazy MySQL.

---

## Baza danych

Schemat bazy jest budowany przez migracje w kolejności chronologicznej. Tabela `priorities` jest wypełniana danymi (high, medium, low) bezpośrednio przez migrację `create_priorities_table` — są to dane strukturalne wymagane do działania aplikacji, nie dane testowe.

Seeder `DatabaseSeeder` tworzy 5 testowych użytkowników, każdy z 10 losowymi taskami oraz z taskami rozłożonymi na każdy dzień od początku poprzedniego miesiąca do dziś — co pozwala od razu testować filtry dat i widok Summary.

Aby sprawdzić email pierwszego użytkownika po świeżym seedowaniu:

```bash
php artisan tinker
> App\Models\User::first()->email
```

Hasło wszystkich użytkowników testowych to zawsze `password`.

---

## Powiązane projekty

Frontend SPA: [todo-app](../todo-app) — Vue 3 + Pinia + Vue Router
