# 📚 Scripts SQL pour l'Application Loove

Ce dossier contient tous les scripts SQL nécessaires pour créer et gérer la base de données de l'application Loove.

## 🗂️ Structure des fichiers

### 1. `database_structure.sql` 
**Script principal de création de la base de données**
- Création de la base de données `loove_db`
- Toutes les tables principales (users, messages, likes, matches, etc.)
- Index et contraintes de clés étrangères
- Triggers automatiques
- Vues utiles
- Procédures stockées de base

### 2. `test_data.sql`
**Données de test pour la démonstration**
- 10 utilisateurs de test avec profils complets
- Interactions réalistes (likes, matches, messages)
- Conversations d'exemple
- Abonnements premium de test
- Statistiques complètes

### 3. `procedures_functions.sql`
**Fonctions et procédures avancées**
- Calcul d'âge automatique
- Calcul de distance entre villes
- Recommandations d'utilisateurs
- Gestion des conversations
- Statistiques utilisateurs
- Nettoyage automatique des données

## 🚀 Installation rapide

### Méthode 1 : Installation complète
```sql
-- 1. Exécuter dans l'ordre :
SOURCE database_structure.sql;
SOURCE test_data.sql;
SOURCE procedures_functions.sql;
```

### Méthode 2 : Import via phpMyAdmin
1. Aller sur `http://localhost/phpmyadmin`
2. Créer une nouvelle base de données `loove_db`
3. Importer les fichiers dans l'ordre :
   - `database_structure.sql`
   - `test_data.sql` 
   - `procedures_functions.sql`

## 📊 Structure de la base de données

### Tables principales :
- **users** : Utilisateurs de l'application
- **messages** : Système de messagerie
- **likes** : Likes/dislikes/superlikes
- **matches** : Matches confirmés
- **user_photos** : Photos supplémentaires
- **premium_subscriptions** : Abonnements premium
- **reports** : Signalements
- **blocked_users** : Utilisateurs bloqués

### Fonctionnalités avancées :
- **Triggers automatiques** pour créer les matches
- **Procédures stockées** pour les opérations complexes
- **Vues** pour les statistiques
- **Index optimisés** pour les performances

## 👥 Comptes de test disponibles

### Utilisateurs normaux :
- **Email:** `emma@test.com` | **Mot de passe:** `password123`
- **Email:** `lucas@test.com` | **Mot de passe:** `password123`
- **Email:** `sophie@test.com` | **Mot de passe:** `password123`
- **Email:** `thomas@test.com` | **Mot de passe:** `password123`

### Utilisateurs premium :
- **Email:** `camille@test.com` | **Mot de passe:** `password123` 
- **Email:** `clara@test.com` | **Mot de passe:** `password123`

### Administrateur :
- **Email:** `admin@loove.com` | **Mot de passe:** `password123`

## 🔧 Requêtes utiles

### Voir tous les matches d'un utilisateur :
```sql
CALL GetUserMatches(2); -- Pour Emma
```

### Obtenir les statistiques d'un utilisateur :
```sql
CALL GetUserStats(2); -- Pour Emma
```

### Voir les conversations :
```sql
CALL GetUserConversations(2); -- Pour Emma
```

### Obtenir des utilisateurs recommandés :
```sql
CALL GetRecommendedUsers(2, 10); -- 10 recommandations pour Emma
```

## 🧹 Maintenance

### Nettoyage automatique :
```sql
CALL CleanupOldData();
```

### Vérifier les statistiques générales :
```sql
SELECT * FROM active_users_stats;
```

## 📈 Performances

- **Index optimisés** sur toutes les colonnes fréquemment utilisées
- **Contraintes de clés étrangères** pour l'intégrité
- **Procédures stockées** pour réduire les requêtes
- **Vues materialisées** pour les statistiques

## 🚨 Important

- Tous les mots de passe sont hashés avec `password_hash()` PHP
- Les données de test sont réalistes mais fictives
- La base est configurée en UTF8MB4 pour supporter les emojis
- Les triggers créent automatiquement les matches lors des likes mutuels

## 🔒 Sécurité

- Contraintes de clés étrangères activées
- Validation des données au niveau base
- Prévention des doublons avec UNIQUE KEY
- Soft delete avec colonnes `is_active`

---

**🎯 Prêt pour la démonstration !**
Cette structure de base de données est complète et prête pour un oral de soutenance professionnel.