# App-Loove - Application de Rencontre

## Contexte
App-Loove est une application de rencontre amoureuse qui permet aux utilisateurs de créer un profil, rechercher des partenaires compatibles, discuter en temps réel et organiser des rencontres. L'application inclut des fonctionnalités de géolocalisation, de chat privé, de filtres de recherche avancés et d'abonnement premium.

## Fonctionnalités Principales
- **Authentification** : Inscription et connexion des utilisateurs avec gestion des comptes par un administrateur.
- **Gestion des Profils** : Création, modification et suppression de profils utilisateurs.
- **Recherche et Filtres** : Recherche de profils compatibles selon divers critères.
- **Système de Match** : Fonctionnalité de "liker" et de messagerie en temps réel.
- **Abonnement Premium** : Accès à des fonctionnalités exclusives via un abonnement.
- **Notifications** : Alertes pour nouveaux messages, matchs et profils correspondants.
- **Modération** : Signalement de comportements inappropriés et gestion par l'administrateur.

## Architecture
L'application est construite selon le modèle MVC (Model-View-Controller) avec les technologies suivantes :
- **Frontend** : HTML, CSS, JavaScript
- **Backend** : PHP
- **Base de données** : MySQL

## Installation
1. Clonez le dépôt depuis GitLab.
2. Configurez la base de données en exécutant le script `database.sql`.
3. Modifiez le fichier `config/database.php` pour définir les paramètres de connexion à la base de données.
4. Lancez le serveur web et accédez à `public/index.php`.

## Responsabilité des Fichiers
- **config/** : Contient les fichiers de configuration de l'application.
- **controllers/** : Gère la logique de l'application.
- **models/** : Représente les données et les interactions avec la base de données.
- **public/** : Contient les fichiers accessibles au public, y compris les assets et le point d'entrée de l'application.
- **utils/** : Fournit des classes utilitaires pour la journalisation et la validation.
- **views/** : Contient les fichiers de présentation pour l'interface utilisateur.

## Documentation
Pour plus de détails sur l'utilisation de l'application, veuillez consulter la documentation utilisateur fournie dans le dépôt.

## Auteurs
Développé par [Votre Nom] - [Votre Email] - [Votre GitLab] 

## License
Ce projet est sous licence MIT.