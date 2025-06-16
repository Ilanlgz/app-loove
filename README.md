# 💕 Loove - Application de Rencontre

Une application de rencontre moderne avec notifications push en temps réel.

## 🚀 Fonctionnalités

- ✅ Inscription et connexion sécurisées
- 💬 Système de messages en temps réel
- 📱 Notifications push pour messages et likes
- ❤️ Système de likes et matchs
- 👤 Profils utilisateurs avec photos
- 🔍 Découverte d'utilisateurs
- 💎 Système Premium
- 📱 Interface responsive

## 🛠️ Technologies

- **Backend**: PHP 8+ avec PDO
- **Frontend**: HTML5, CSS3, JavaScript
- **Base de données**: MySQL
- **Notifications**: Pusher Beams
- **Serveur**: Apache (XAMPP)

## 📦 Installation

1. Cloner le projet
    ```bash
    git clone https://github.com/TON_USERNAME/loove.git
    cd loove
    ```

2. Configurer la base de données
    - Créer une base de données MySQL `loove`
    - Importer le fichier `database.sql`
    - Configurer `config/database.php`

3. Installer les dépendances
    ```bash
    composer install
    ```

4. Configurer les notifications push
    - Créer un compte Pusher Beams
    - Mettre à jour les clés dans le code

## 🔧 Configuration

Copier `config/database.example.php` vers `config/database.php` et configurer :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'loove');
define('DB_USER', 'root');
define('DB_PASS', '');
```

## 🚀 Utilisation

1. Démarrer XAMPP
2. Aller sur `http://localhost/loove`
3. Créer un compte ou se connecter
4. Profiter de l'expérience Loove ! 💕

## 📱 Notifications Push

Les notifications push fonctionnent avec Pusher Beams :
- Messages en temps réel
- Notifications de likes
- Notifications de matchs

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou un pull request.

## 📄 Licence

Ce projet est sous licence MIT.

## 👨‍💻 Auteur

Créé avec ❤️ par [TON_NOM]
