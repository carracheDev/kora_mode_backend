# KORA MODE API

Backend Laravel 13 pour la démo e-commerce KORA MODE. Le frontend Next.js est dans `../kora-mode`. L’API REST v1 stocke le catalogue, les commandes et les comptes dans PostgreSQL (Neon en environnement partagé).

## Démarrage local

1. Copier `.env.example` vers `.env`, renseigner `DB_URL` avec la chaîne Neon et générer `APP_KEY` (`php artisan key:generate`). Garder `DB_SSLMODE=require`.
2. Laisser `PAYMENT_DRIVER=simulation` pour tester sans argent réel. Pour FedaPay, passer à `fedapay` et renseigner les clés **sandbox** `FEDAPAY_SECRET_KEY`, `FEDAPAY_WEBHOOK_SECRET`, plus l’URL publique de retour.
3. Depuis ce dossier, démarrer l’API avec `docker compose up --build -d`.
4. Charger une fois le catalogue et les codes de démonstration avec `docker compose exec app php artisan db:seed --force`.
5. Vérifier `http://localhost:8080/up` et `http://localhost:8080/api/v1/products`.

La commande de seed remet à niveau le catalogue de démonstration. Ne la relance pas sur un catalogue dont le stock a été modifié. Les migrations de commandes sont additives et se lancent au démarrage du conteneur.

## Contrat API

La spec Swagger/OpenAPI 3.1 est dans [`docs/openapi.yaml`](docs/openapi.yaml). Elle peut être importée dans Swagger UI, Postman ou Insomnia.

### Catalogue

- `GET /api/v1/categories`
- `GET /api/v1/categories/{slug}`
- `GET /api/v1/products`
- `GET /api/v1/products/{slug}`

Filtres : `q`, `category`, `gender`, `subcategory`, `min_price`, `max_price`, `promo`, `in_stock`, `sort`, `per_page`, `page`. Tri : `newest`, `price_asc`, `price_desc`, `popular`.

### Compte client et favoris

- `POST /api/v1/auth/register`, `POST /api/v1/auth/login`
- `GET /api/v1/auth/me`, `POST /api/v1/auth/logout`
- `GET|POST /api/v1/favorites`, `DELETE /api/v1/favorites/{slug}`
- `GET /api/v1/orders` (historique du compte)

Sanctum accepte la session SPA si frontend et API partagent un domaine de premier niveau. Pour un frontend Vercel et une API sur un autre domaine, les appels login/register renvoient un jeton Bearer Sanctum à conserver côté navigateur, puis à révoquer à la déconnexion. Les jetons expirent après 30 jours. Configure `CORS_ALLOWED_ORIGINS` sur l’origine exacte du frontend.

### Commandes, stock et promotions

- `POST /api/v1/orders` crée une commande invitée ou client.
- `GET /api/v1/orders/{orderNumber}` lit son reçu via un identifiant UUID non devinable.
- `POST /api/v1/orders/{orderNumber}/payments` démarre ou reprend le paiement.
- `POST /api/v1/orders/{orderNumber}/payment-simulation` simule `succeeded`, `failed` ou `pending` seulement si `PAYMENT_DRIVER=simulation`.

Le serveur recalcule prix, remise, livraison et total depuis PostgreSQL, vérifie tailles/couleurs et stock sous transaction, puis réserve le stock. Le code look `complete-look-10` n’est accepté qu’avec ses trois pièces. Les codes promo sont `BF40`, `NOEL15`, `2027`; leurs dates et produits éligibles sont dans le seeder. Les données de catalogue sont marquées comme démo.

Les moyens `mtn`, `moov` et `celtiis` utilisent la page de paiement FedaPay lorsqu’on sélectionne `PAYMENT_DRIVER=fedapay`. En mode simulation, le client choisit un résultat de démo et aucun paiement réel n’est déclenché. Le paiement à la livraison est confirmé comme commande, mais reste non payé.

### FedaPay et webhooks

Le backend crée une transaction XOF et un lien hébergé avec la clé API secrète côté serveur. Le webhook public `POST /api/v1/webhooks/fedapay` vérifie `X-FEDAPAY-SIGNATURE` en HMAC-SHA256 avec une tolérance de cinq minutes, stocke l’identifiant d’événement unique et ignore les répétitions. Un paiement échoué annule la commande et restitue le stock une seule fois.

Les clés FedaPay sandbox sont nécessaires pour un essai de bout en bout. N’utilise aucune clé live pour la démo.

## Tests

Les tests utilisent `DB_CONNECTION=pgsql_testing` et réinitialisent les tables. Renseigne `DB_TEST_URL` avec une branche/base de test dédiée. **Ne pointe jamais `DB_TEST_URL` vers la base Neon qui contient les données de démonstration.** La suite complète : `php artisan test --compact`.

## Déploiement

Le conteneur PHP-FPM/Nginx et PostgreSQL Neon sont prêts à être déployés sur un hôte compatible Docker (par exemple Render, Railway ou Laravel Cloud). Sur l’hôte, configurer les variables de `.env.example`, appliquer les migrations, définir `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, `SESSION_SECURE_COOKIE=true`, `CORS_ALLOWED_ORIGINS`, `FRONTEND_URL`, les paramètres Sanctum et le driver de paiement voulu. Déployer le frontend Next.js séparément sur Vercel avec `NEXT_PUBLIC_KORA_API_URL=https://<api>/api/v1`.

Pour utiliser les cookies Sanctum en production, configure un domaine commun (par exemple `shop.example.com` et `api.example.com`). Avec des domaines distincts `*.vercel.app` et `*.onrender.com`, utilise le jeton Bearer documenté ci-dessus.
