# KORA MODE API

Backend Laravel 13 pour la démo e-commerce KORA MODE. Le frontend Next.js reste dans `../kora-mode`; ce projet expose une API REST versionnée et stocke le catalogue dans PostgreSQL.

## Prérequis

- PHP 8.3 avec `pdo_pgsql`
- Composer 2
- Docker Compose
- Une base PostgreSQL Neon et sa chaîne de connexion

## Démarrer avec Docker

1. Copier `.env.example` vers `.env`, renseigner `DB_URL` avec l’URL PostgreSQL Neon, puis générer une clé : `php artisan key:generate`.
2. Garder `DB_SSLMODE=require` pour chiffrer la connexion Neon.
3. Démarrer l’API : `docker compose up --build -d`. Le conteneur applique les migrations au démarrage.
4. Charger les données de démonstration : `docker compose exec app php artisan db:seed --force`.
5. Vérifier `/up`, puis ouvrir `http://localhost:8080/api/v1/products`.

Configure `DB_TEST_URL` avec une branche ou base Neon séparée avant les tests. Ne pointe jamais `DB_TEST_URL` vers les données de production : les tests réinitialisent leur schéma.

## API catalogue v1

- `GET /api/v1/categories`
- `GET /api/v1/categories/{slug}`
- `GET /api/v1/products`
- `GET /api/v1/products/{slug}`

Filtres produits : `q`, `category`, `gender`, `subcategory`, `min_price`, `max_price`, `promo`, `in_stock`, `sort`, `per_page`. Tri : `newest`, `price_asc`, `price_desc`, `popular`. Les listes sont paginées et les paramètres de filtre sont conservés dans les liens.

Exemple : `GET /api/v1/products?category=femme&promo=1&sort=price_asc`.

Les catégories et produits du seeder copient le catalogue de démonstration KORA MODE. Prix, notes et stocks sont explicitement marqués `is_demo_data`; ils ne doivent pas être interprétés comme les données d’une boutique réelle.

## Comptes et favoris (Sanctum)

- `GET /sanctum/csrf-cookie` initialise le cookie CSRF pour le frontend.
- `POST /api/v1/auth/register` crée un compte invité (nom, email, téléphone facultatif, mot de passe confirmé).
- `POST /api/v1/auth/login`, `GET /api/v1/auth/me`, `POST /api/v1/auth/logout` gèrent la session.
- `GET|POST /api/v1/favorites` liste ou ajoute un favori par `product_slug`.
- `DELETE /api/v1/favorites/{slug}` supprime un favori.

Le frontend Next.js doit envoyer les cookies et l’en-tête `Origin`, et conserver le cookie CSRF. En local, `SANCTUM_STATEFUL_DOMAINS` et `CORS_ALLOWED_ORIGINS` sont réglés pour `localhost:3000`. En production, configure les domaines réels et `SESSION_DOMAIN` sur un domaine partagé (par exemple `shop.exemple.com` et `api.exemple.com` avec `SESSION_DOMAIN=.exemple.com`). L’authentification SPA par session Sanctum exige que le frontend et l’API partagent le même domaine de premier niveau. Active `SESSION_SECURE_COOKIE=true` derrière HTTPS.

Les tests d’authentification et de favoris utilisent `DB_TEST_URL`. Configure-la sur une branche/base Neon distincte avant de les exécuter ; ils ne doivent pas viser la base de démonstration principale.

## Lots prévus

1. Catalogue, catégories et stock PostgreSQL.
2. Sanctum, comptes et favoris.
3. Panier serveur, commandes, codes promo et statuts.
4. Paiement FedaPay sandbox.
5. Webhooks signés et idempotents.
6. Documentation Scribe.
7. Conteneurisation, déploiement et compte de démo.
# kora_mode_backend
