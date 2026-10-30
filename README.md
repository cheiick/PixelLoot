
# Lien de stockage

> ⚠️ N’oubliez pas de créer le lien vers le stockage public en exécutant `php artisan storage:link` après avoir migré et ajouté le champ image_path.

# E-mails de commande sur Render

1. Dans le panel admin, ouvrez **Paramètres du site** et renseignez le serveur SMTP, le port, le chiffrement, les identifiants ainsi qu’une adresse expéditrice autorisée. Le SMTP de Brevo peut être utilisé avec son offre gratuite, selon ses limites et conditions en vigueur ; tout fournisseur SMTP compatible convient également.
2. Enregistrez les paramètres puis utilisez **Envoyer un e-mail de test**. Le secret SMTP est chiffré avec `APP_KEY` et n’est pas réaffiché. Ne changez pas cette clé après avoir enregistré le secret, sinon Laravel ne pourra plus le déchiffrer.
3. Dans Render, ajoutez `STRIPE_WEBHOOK_SECRET` avec le secret de signature du webhook Stripe. Configurez dans Stripe un webhook vers `https://<votre-domaine>/stripe/webhook` pour les événements `checkout.session.completed` et `checkout.session.async_payment_succeeded`.
4. Appliquez les migrations de production avec `php artisan migrate --force`.

Le reçu e-mail est envoyé à l’adresse du compte après confirmation du paiement par Stripe. Les changements de statut effectués par un administrateur déclenchent également un e-mail lorsqu’une adresse est disponible. L’URL de retour du client ne suffit pas à valider un paiement.

# Images et déploiement Render

- Dans les variables Render, définissez `APP_URL` sur l’URL publique HTTPS exacte du service et `APP_DEBUG=false`. Le Dockerfile désactive aussi le debug par défaut. Laravel fait confiance aux en-têtes du proxy Render afin que les liens générés restent en HTTPS.
- Le conteneur configure PHP avec `upload_max_filesize=12M` et `post_max_size=64M`. L’application accepte au maximum 10 Mo par image produit, 2 Mo pour le logo et 10 Mo par image de bannière (jusqu’à cinq images, 50 Mo au total). Les très grandes images doivent être réduites sous la limite par fichier ; une requête multiple de plus de 64 Mo doit être divisée.
- Le stockage local du conteneur Render est éphémère. Pour le conserver, attachez un disque persistant Render monté sur `/var/data`, puis définissez `PUBLIC_STORAGE_DRIVER=local` et `PUBLIC_STORAGE_PATH=/var/data/public`. L’application crée le lien public au démarrage.
- Sans disque persistant, utilisez un stockage objet compatible S3 (le pilote est inclus) : définissez `PUBLIC_STORAGE_DRIVER=s3`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_URL` (URL publique/CDN du bucket) et éventuellement `AWS_ENDPOINT` ainsi que `AWS_USE_PATH_STYLE_ENDPOINT`. Configurez le bucket pour autoriser la lecture publique des objets, mais gardez les identifiants d’écriture privés. Pour un endpoint S3 compatible, `PUBLIC_STORAGE_URL` peut préciser l’URL publique exacte.
- Les fichiers image ne sont pas dans PostgreSQL. Après le changement de stockage ou un redéploiement ayant perdu le disque éphémère, les anciennes références d’images peuvent rester en base sans que leurs fichiers existent : recopiez les fichiers vers le stockage durable ou téléversez-les à nouveau.
- Les formulaires locaux génèrent des URL HTTPS si `APP_URL` est exact. Le script du panier vérifie maintenant que son URL reste sur la même origine et affiche une erreur exploitable si le serveur renvoie une page HTML au lieu du JSON attendu.