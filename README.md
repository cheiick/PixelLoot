
# Connexion Google

- Dans la console Google Cloud, créez un identifiant OAuth client de type application Web et ajoutez `https://<votre-domaine>/auth/google/callback` aux URI de redirection autorisées (en local : `http://localhost/auth/google/callback`).
- Définissez `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` et `GOOGLE_REDIRECT_URI` dans l’environnement de l’application. Le bouton Google apparaît sur les pages de connexion et d’inscription lorsque les deux identifiants sont configurés.
- Exécutez `php artisan migrate --force` en production pour ajouter l’identifiant Google aux comptes. Les adresses Google doivent être vérifiées ; une adresse déjà présente est associée au compte correspondant.
- Le numéro de téléphone est obligatoire lors du paiement et est enregistré avec le compte et l’adresse de livraison de la commande.

# Lien de stockage

> ⚠️ N’oubliez pas de créer le lien vers le stockage public en exécutant `php artisan storage:link` après avoir migré et ajouté le champ image_path.

# E-mails de commande sur Render

1. Dans le panel admin, ouvrez **Paramètres du site** et activez l’envoi. Configurez Mailtrap Email Sending API (section ci-dessous) ou, sans jeton Mailtrap, renseignez les paramètres SMTP et une adresse expéditrice autorisée.
2. Enregistrez les paramètres puis utilisez **Envoyer un e-mail de test**. Le secret SMTP est chiffré avec `APP_KEY` et n’est pas réaffiché. Ne changez pas cette clé après avoir enregistré le secret, sinon Laravel ne pourra plus le déchiffrer.
3. Dans Render, ajoutez `STRIPE_WEBHOOK_SECRET` avec le secret de signature du webhook Stripe. Configurez dans Stripe un webhook vers `https://<votre-domaine>/stripe/webhook` pour les événements `checkout.session.completed` et `checkout.session.async_payment_succeeded`.
4. Appliquez les migrations de production avec `php artisan migrate --force`. Le démarrage du conteneur Render exécute aussi les migrations ; une migration resynchronise les séquences d’identifiants PostgreSQL avec les données existantes pour éviter les erreurs de clé primaire en double.

Le reçu e-mail est envoyé à l’adresse du compte après confirmation du paiement par Stripe ou FedaPay. Les changements de statut effectués par un administrateur déclenchent également un e-mail lorsqu’une adresse est disponible. L’URL de retour du client ne suffit pas à valider un paiement.

## Paiements FedaPay

- Dans l’environnement (par exemple Render), configurez `FEDAPAY_SECRET_KEY` avec la clé secrète de test ou live du tableau de bord FedaPay. Ne la publiez jamais.
- `FEDAPAY_BASE_URL` vaut par défaut `https://sandbox-api.fedapay.com/v1`. Pour les paiements réels, définissez-la sur `https://api.fedapay.com/v1` et utilisez la clé live correspondante.
- FedaPay est proposé au checkout uniquement lorsque la devise de la boutique est XOF et que la clé secrète est configurée. Configurez un webhook vers `https://<votre-domaine>/fedapay/webhook`, sélectionnez l’événement `transaction.approved` et ajoutez sa clé de signature dans `FEDAPAY_WEBHOOK_SECRET`.
- Le retour client et le webhook récupèrent la transaction auprès de l’API FedaPay et vérifient son statut, son montant et sa commande avant de marquer celle-ci comme payée.

# Envoi d’e-mails avec Mailtrap

- Pour envoyer réellement les confirmations newsletter et les e-mails de commande, utilisez **Mailtrap Email Sending API**, et non les identifiants de l’inbox Sandbox SMTP.
- Dans Render, ajoutez `MAILTRAP_API_TOKEN` avec le jeton API Email Sending. Ne le partagez pas et ne le commitez jamais. `MAILTRAP_API_URL` est facultatif (par défaut `https://send.api.mailtrap.io/api/send`) ; `MAILTRAP_CATEGORY` est facultatif (par défaut `Transactional`).
- Activez l’envoi dans les paramètres du site et configurez-y une adresse expéditrice autorisée par Mailtrap. Si `MAILTRAP_API_TOKEN` est défini, l’application utilise l’API ; sinon elle utilise le SMTP enregistré dans le panel.
- Mailtrap doit autoriser/vérifier le domaine expéditeur pour la livraison réelle. Consultez les journaux d’envoi sur [Mailtrap Email Logs](https://mailtrap.io/sending/email_logs).

# Images et déploiement Render

- Dans les variables Render, définissez `APP_URL` sur l’URL publique HTTPS exacte du service et `APP_DEBUG=false`. Le Dockerfile désactive aussi le debug par défaut. Laravel fait confiance aux en-têtes du proxy Render afin que les liens générés restent en HTTPS.
- Le conteneur configure PHP avec `upload_max_filesize=12M` et `post_max_size=64M`. L’application accepte au maximum 10 Mo par image produit, 2 Mo pour le logo et 10 Mo par image de bannière (jusqu’à cinq images, 50 Mo au total). Les très grandes images doivent être réduites sous la limite par fichier ; une requête multiple de plus de 64 Mo doit être divisée.
- Pour conserver durablement les images de produits, le logo et les bannières, configurez `IMAGE_STORAGE_DISK=cloudinary` dans Render et ajoutez `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_KEY` et `CLOUDINARY_SECRET` depuis la console Cloudinary. Le secret API doit rester privé : ne le commitez jamais. Les nouvelles images sont enregistrées sur Cloudinary et leur URL sécurisée est stockée en base. Les anciennes images locales ne sont pas transférées automatiquement : réimportez-les depuis le panel admin si leurs fichiers sont encore disponibles.
- Le stockage local du conteneur Render est éphémère. Pour le conserver, attachez un disque persistant Render monté sur `/var/data`, puis définissez `PUBLIC_STORAGE_DRIVER=local` et `PUBLIC_STORAGE_PATH=/var/data/public`. L’application crée le lien public au démarrage.
- Sans disque persistant, utilisez un stockage objet compatible S3 (le pilote est inclus) : définissez `PUBLIC_STORAGE_DRIVER=s3`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_URL` (URL publique/CDN du bucket) et éventuellement `AWS_ENDPOINT` ainsi que `AWS_USE_PATH_STYLE_ENDPOINT`. Configurez le bucket pour autoriser la lecture publique des objets, mais gardez les identifiants d’écriture privés. Pour un endpoint S3 compatible, `PUBLIC_STORAGE_URL` peut préciser l’URL publique exacte.
- Les fichiers image ne sont pas dans PostgreSQL. Après le changement de stockage ou un redéploiement ayant perdu le disque éphémère, les anciennes références d’images peuvent rester en base sans que leurs fichiers existent : recopiez les fichiers vers le stockage durable ou téléversez-les à nouveau.
- Les formulaires locaux génèrent des URL HTTPS si `APP_URL` est exact. Le script du panier vérifie maintenant que son URL reste sur la même origine et affiche une erreur exploitable si le serveur renvoie une page HTML au lieu du JSON attendu.