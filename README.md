# Loove - Application de rencontres

## Configuration de l'environnement de démo

Suivez ces étapes pour configurer rapidement l'environnement de démo avec des images de qualité :

1. Clonez ce dépôt dans votre dossier htdocs de XAMPP.

2. Téléchargez les images de démo en exécutant :
   ```bash
   php app/utils/download_demo_images.php
   ```
   
   Ce script va télécharger automatiquement des images libres de droit depuis Unsplash pour alimenter votre démo.

3. Assurez-vous que les dossiers suivants existent et sont accessibles en écriture :
   - `public/assets/images`
   - `public/assets/images/demo`

4. Configurez votre base de données dans `app/config/database.php`.

5. Accédez à l'application via votre navigateur : http://localhost/loove/public/

## Fonctionnalités

- Page d'accueil avec stories et publications
- Système de messagerie
- Profils utilisateurs
- Recherche de profils
- Fonctionnalités de rencontres

## Crédits

Les images utilisées dans cette démo proviennent d'Unsplash, une source d'images libres de droit.
