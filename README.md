
# Lien de stockage

> ⚠️ N’oubliez pas de créer le lien vers le stockage public en exécutant `php artisan storage:link` après avoir migré et ajouté le champ image_path.

# E-mails de commande sur Render

1. Dans le panel admin, ouvrez **Paramètres du site** et renseignez le serveur SMTP, le port, le chiffrement, les identifiants ainsi qu’une adresse expéditrice autorisée. Le SMTP de Brevo peut être utilisé avec son offre gratuite, selon ses limites et conditions en vigueur ; tout fournisseur SMTP compatible convient également.
2. Enregistrez les paramètres puis utilisez **Envoyer un e-mail de test**. Le secret SMTP est chiffré avec `APP_KEY` et n’est pas réaffiché. Ne changez pas cette clé après avoir enregistré le secret, sinon Laravel ne pourra plus le déchiffrer.
3. Dans Render, ajoutez `STRIPE_WEBHOOK_SECRET` avec le secret de signature du webhook Stripe. Configurez dans Stripe un webhook vers `https://<votre-domaine>/stripe/webhook` pour les événements `checkout.session.completed` et `checkout.session.async_payment_succeeded`.
4. Appliquez les migrations de production avec `php artisan migrate --force`.

Le reçu e-mail est envoyé à l’adresse du compte après confirmation du paiement par Stripe. Les changements de statut effectués par un administrateur déclenchent également un e-mail lorsqu’une adresse est disponible. L’URL de retour du client ne suffit pas à valider un paiement.