# TodoList — Vue 3 Frontend

Aplikacja SPA (Single Page Application) do zarządzania zadaniami, zbudowana w Vue 3 z Composition API. Frontend komunikuje się z Laravel API przez Laravel Sanctum (autentykacja cookie-based).

---

## Wymagania

- Node.js >= 18
- npm >= 9
- Działający backend [laravel_api](../laravel_api) dostępny pod `http://localhost:8000`

---

## Technologie

- **Vue 3** — Composition API, `<script setup>`
- **Pinia** — zarządzanie stanem aplikacji
- **Vue Router 4** — routing z navigation guards
- **Axios** — komunikacja z API
- **v-calendar** — komponent DatePicker do wyboru daty
- **date-fns** — formatowanie i manipulacja datami
- **Bootstrap 5** — stylowanie UI

---

## Instalacja i uruchomienie

```bash
# 1. Sklonuj repozytorium i przejdź do folderu frontendu
cd todo-app

# 2. Zainstaluj zależności
npm install

# 3. Skopiuj plik środowiskowy i skonfiguruj zmienne
cp .env.example .env

# 4. Uruchom serwer deweloperski
npm run dev
```

Aplikacja będzie dostępna pod adresem: `http://localhost:5173`

---

## Zmienne środowiskowe

Plik `.env` (na podstawie `.env.example`):

```env
VITE_BASE_URL=http://localhost:8000
VITE_API_PATH=/api/v2/
```

`VITE_BASE_URL` — adres backendu Laravel  
`VITE_API_PATH` — prefiks ścieżki API (wersja v2)

---

## Struktura projektu

```
src/
├── assets/          # Globalne style CSS
├── components/
│   ├── dropdown/    # Generyczne komponenty dropdown (Dropdown, DropdownItem, DropdownTrigger)
│   ├── icons/       # Komponenty ikon SVG
│   ├── summaries/   # Komponenty strony Summary (Summaries, Summary, SummaryFilter)
│   └── tasks/       # Komponenty zarządzania taskami (Task, Tasks, NewTask, FilterTasks, SortTasks, SelectPriority, TaskActions)
├── composables/
│   ├── useDateFormatter.js  # Formatowanie dat (lokalny format i wyświetlanie)
│   └── useFocusInput.js     # Pomocnik do focusowania inputa po akcji
├── http/            # Warstwa komunikacji z API
│   ├── api.js           # Instancja Axios z baseURL
│   ├── auth-api.js      # Endpointy autoryzacji (login, register, logout, me)
│   ├── task-api.js      # Endpointy tasków (CRUD, complete)
│   ├── priority-api.js  # Endpoint priorytetów
│   └── summary-api.js   # Endpoint podsumowań
├── pages/
│   ├── HomePage.vue
│   ├── TasksPage.vue
│   ├── SummaryPage.vue
│   ├── LoginPage.vue
│   ├── RegisterPage.vue
│   └── errors/NotFoundErrorPage.vue
├── router/
│   ├── index.js     # Navigation guard z obsługą inicjalizacji sesji
│   └── routes.js    # Definicje tras z meta (auth / guest)
└── stores/
    ├── auth.js      # Stan użytkownika, login, logout, register
    ├── task.js      # Lista tasków, operacje CRUD
    ├── priority.js  # Lista priorytetów
    └── summary.js   # Dane podsumowań
```

---

## Funkcjonalności

**Autentykacja** — rejestracja i logowanie przez Laravel Sanctum (cookie-based). Navigation guard chroni trasy wymagające logowania i zapobiega wchodzeniu zalogowanego użytkownika na strony gościa. Przy odświeżeniu strony sesja jest weryfikowana przed podjęciem decyzji o nawigacji.

**Zarządzanie taskami** — dodawanie, edytowanie (dwuklik lub ikona ołówka), oznaczanie jako ukończone, usuwanie. Lista tasków dzieli się na aktywne i ukończone (z przyciskiem show/hide).

**Natural input** — podczas wpisywania nowego zadania można użyć specjalnej składni bezpośrednio w polu tekstowym. Parser po stronie backendu wyciąga z tekstu priorytety (`!high`, `!medium`, `!low`) oraz daty (`@today`, `@tomorrow`, `@nextweek`, `@YYYY-MM-DD`). Tagi są wizualnie podświetlane podczas pisania.

**Priorytety** — każdy task może mieć przypisany priorytet (High / Medium / Low / brak). Priorytet jest wizualnie oznaczony kolorem checkboxa. Można sortować taski według priorytetu.

**Daty** — każdy task może mieć przypisaną datę wykonania. Dostępne filtry: Inbox, Today, Next 3 days, Next week, Overdue.

**Sortowanie** — taski można sortować według czasu dodania, nazwy lub priorytetu.

**Summary** — strona z podsumowaniem tasków z filtrowaniem po okresie (Today, Yesterday, This week, Last week, This month, Last month).

---

## Routing i ochrona tras

Trasy z `meta: { auth: true }` wymagają zalogowania — nieautoryzowany użytkownik jest przekierowywany na `/login` z zapisaniem docelowego URL w query (`?redirect=...`). Po zalogowaniu użytkownik trafia na zapisany adres lub domyślnie na `/tasks`.

Trasy z `meta: { guest: true }` są dostępne tylko dla niezalogowanych — zalogowany użytkownik jest przekierowywany na `/tasks`.

---

## Budowanie produkcyjne

```bash
# Zbuduj aplikację do folderu dist/
npm run build

# Podgląd zbudowanej wersji lokalnie
npm run preview
```

---

## Powiązane projekty

Backend API: [laravel_api](../laravel_api) — Laravel 11 + Sanctum
