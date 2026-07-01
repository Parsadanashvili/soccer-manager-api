# Soccer Manager API

A RESTful API where football fans create a fantasy team and buy/sell players on a
transfer market. Built with **Laravel 13** and **Laravel Sanctum** token
authentication.

When a user signs up they receive a ready-made squad of 20 players and a transfer
budget. Owners can edit their team and players, list players for sale at a chosen
price, browse the market, and buy other teams' players — with budgets and player
values updating on every transfer.

---

## Features

- Token-based auth (register / login / logout) via **Laravel Sanctum**
- One team per user, auto-generated on signup:
  **3 goalkeepers, 6 defenders, 6 midfielders, 5 attackers** ($1,000,000 each)
- Each team starts with a **$5,000,000** transfer budget
- Team value = the sum of its players' market values
- Editable team (name, country) and players (first name, last name, country)
- Transfer list: an owner lists a player at an asking price
- A market of listed players visible to every user
- Buying a player moves the asking price between budgets and **appreciates the
  player's value by a random 10–100%**
- Full test suite (**44 tests**)
- Localization: **English** and **Georgian** (`Accept-Language` negotiation)

---

## Requirements

- PHP **8.3+**
- Composer
- SQLite (default; the app also runs on MySQL/PostgreSQL by editing `.env`)

---

## Setup

```bash
# 1. Clone and install dependencies
git clone <repo-url> soccer-manager-api
cd soccer-manager-api
composer install

# 2. Create your environment file and app key
cp .env.example .env
php artisan key:generate

# 3. Create the SQLite database file
touch database/database.sqlite

# 4. Run migrations
php artisan migrate

# 5. Serve the API
php artisan serve
```

The API is now available at **`http://localhost:8000/api/v1`**.

> All requests should send `Accept: application/json`. Protected endpoints require
> an `Authorization: Bearer <token>` header (the token is returned by register/login).

---

## Running tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database (configured in `phpunit.xml`), so
they never touch your development data.

---

## Localization

The API returns validation, authentication, and domain messages in **English** or
**Georgian**. The language is chosen per request from the `Accept-Language` header,
matched against the app's supported locales (`ka`, `en`):

```bash
curl -H 'Accept-Language: ka' ...   # Georgian
curl -H 'Accept-Language: en' ...   # English
```

Relevant `.env` keys:

| Key | Purpose |
|-----|---------|
| `APP_LOCALE` | Default locale when the client expresses no preference |
| `APP_FALLBACK_LOCALE` | Locale used when a key is missing in the active locale |
| `APP_FAKER_LOCALE` | Locale used by the fake data generator on signup |

Translation files live under `lang/en` and `lang/ka`.

---

## API reference

Base URL: `http://localhost:8000/api/v1`

| Method | Endpoint | Auth | Description |
|--------|----------|:----:|-------------|
| `POST` | `/register` | — | Create an account; auto-generates the team + 20 players. Returns a token. |
| `POST` | `/login` | — | Log in. Returns a token. |
| `POST` | `/logout` | ✔ | Revoke the current token. |
| `GET`  | `/team` | ✔ | View your team (with players and computed value). |
| `PATCH`| `/team` | ✔ | Update your team's `name` and/or `country`. |
| `GET`  | `/players` | ✔ | List your team's players. |
| `PATCH`| `/players/{player}` | ✔ | Update a player you own (`first_name`, `last_name`, `country`). |
| `POST` | `/players/{player}/transfer-list` | ✔ | List a player you own at an `asking_price`. |
| `DELETE` | `/transfer-list/{transfer_list}` | ✔ | Remove one of your listings. |
| `GET`  | `/market` | ✔ | See every player currently listed for transfer. |
| `POST` | `/market/{player}/buy` | ✔ | Buy a listed player at its asking price. |

### Example flow

**1. Register** (returns token, user, and the generated team)

```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{
        "name": "Nika",
        "email": "nika@example.com",
        "password": "password",
        "password_confirmation": "password"
      }'
```

```json
{
  "token": "1|abcdef...",
  "user": { "id": 1, "name": "Nika", "email": "nika@example.com" },
  "team": { "id": 1, "name": "Tbilisi FC", "country": "Georgia", "budget": 5000000, "value": 20000000 }
}
```

**2. View your team** (use the token from step 1)

```bash
curl http://localhost:8000/api/v1/team \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer 1|abcdef...'
```

**3. List a player for transfer**

```bash
curl -X POST http://localhost:8000/api/v1/players/1/transfer-list \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -H 'Authorization: Bearer 1|abcdef...' \
  -d '{ "asking_price": 2000000 }'
```

**4. Browse the market and buy** (as another user)

```bash
curl http://localhost:8000/api/v1/market \
  -H 'Accept: application/json' -H 'Authorization: Bearer <another-user-token>'

curl -X POST http://localhost:8000/api/v1/market/1/buy \
  -H 'Accept: application/json' -H 'Authorization: Bearer <another-user-token>'
```

---

## How it works

### Registration & squad generation
On registration, `App\Actions\GenerateTeam` creates the user's team ($5,000,000
budget) and generates exactly 20 players — 3/6/6/5 across the positions defined in
`App\Enums\Position`. Each player starts at $1,000,000 with a random age (18–40).
The whole operation runs in a database transaction, so a user can never end up with
a half-built team.

### Team value
A team's value is a computed attribute: the sum of its players' market values. It is
never stored, so it stays correct automatically as players are bought and sold.

### Transfer list & market
An owner lists one of their players at an `asking_price`. A player can only be listed
once, and only by their owner (enforced by policies + request validation). The market
endpoint returns **all** active listings, so any user can shop across teams.

### Buying a player
`App\Actions\BuyPlayer` runs the purchase inside a database transaction and enforces
the rules:

- the player must be on the transfer list,
- you cannot buy your own player,
- your budget must cover the asking price.

On success it moves the **asking price** from the buyer's budget to the seller's,
transfers ownership, appreciates the player's market value by a random **10–100%**
(`round(value × (100 + rand(10,100)) / 100)`), removes the listing, and records the
deal in the `transfers` table.

---

## Project structure

```
app/
  Actions/            GenerateTeam, BuyPlayer — business logic
  Enums/              Position (with per-position squad counts)
  Http/
    Controllers/Api/V1  Auth, Team, Player, TransferList, Market
    Requests/           FormRequest validation + authorization
    Resources/          API response shapes (flat, unwrapped)
  Models/             User, Team, Player, TransferList, Transfer
  Policies/           Ownership rules for players and listings
database/
  factories/          Model factories used by the tests
  migrations/         Schema
lang/                 en/ and ka/ translation files
routes/api.php        Versioned (v1) API routes
tests/                Feature + Unit tests
```
