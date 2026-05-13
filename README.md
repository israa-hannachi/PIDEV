# 🎓 PIDEV — Plateforme Éducative Web

**Une plateforme éducative complète développée avec Symfony, couvrant la gestion des cours,des utilisateurs, des événements, des forums, des réunions et des quiz.**


---

## 📋 Table des matières

- [Description du projet](#-description-du-projet)
- [Modules fonctionnels](#-modules-fonctionnels)
- [Technologies utilisées](#-technologies-utilisées)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Structure du projet](#-structure-du-projet)
- [Équipe](#-équipe)

---

## 📖 Description du projet

**PIDEV** est une application web éducative complète développée dans le cadre d'un projet intégré (PIDEV) à l'école **Esprit**. Elle permet à des enseignants, étudiants et administrateurs d'interagir autour de contenus pédagogiques structurés.

La plateforme couvre **6 modules métiers** principaux :

| Module | Rôle principal |
|--------|---------------|
| 👤 Gestion des utilisateurs | Authentification, profils, 2FA, Face ID |
| 📚 Gestion des cours | Catégories, modules, cours, commentaires, paiement |
| 📅 Gestion des événements | Création, réservation, sponsors, paiement en ligne |
| 🏋️ Gestion des formations | Inscriptions, acceptation/refus, filtres |
| 🎮 Gestion des quiz/jeux | Quiz interactifs, gamification, statistiques |
| 🗓️ Gestion des réunions | Planification, invitations, visioconférence Jitsi |
| 💬 Gestion des forums | Discussions, messages, catégories, chatbot IA |
| 🚨 Gestion des réclamations | Soumission, suivi, réponses admin, chatbot |

---

## 🧩 Modules fonctionnels

### 👤 Authentification & Gestion des utilisateurs
- Connexion classique (email/mot de passe)
- Authentification OAuth2 Google
- Reconnaissance faciale (modèle YuNet ONNX)
- Authentification à deux facteurs (2FA)
- Réinitialisation de mot de passe par email (code à 6 chiffres)
- Gestion de profil (avatar, email, mot de passe)
- Blocage/déblocage d'utilisateurs (admin)
- Tableau de bord administrateur avec statistiques

### 📚 Gestion des cours
- **Catégories** : CRUD complet des catégories de cours
- **Modules** : gestion des modules avec niveaux (Débutant / Intermédiaire / Avancé)
- **Cours** : ajout avec upload PDF (Cloudinary) ou éditeur TinyMCE, gestion de visibilité
- **Commentaires & notes (étoiles)** : par les étudiants
- **Paiement** : intégration Paymee (Stripe)
- **Chatbot** : résumé automatique du contenu d'un cours (GPT)
- **Email automatique** envoyé au formateur lors de la sélection d'un cours

### 📅 Gestion des événements
- Création, modification, suppression d'événements
- Réservation de places avec validation de capacité en temps réel
- Paiement en ligne des billets
- Affichage en carte (API Maps : latitude/longitude)
- Affichage en vue calendrier
- Notation des événements (1-5 étoiles)
- Gestion des sponsors
- Suggestions personnalisées via IA
- Export de confirmation PDF
- Chatbot événements (questions en langage naturel)

### 🎮 Gestion des quiz / jeux
- Création et gestion de quiz par les enseignants
- Suggestions de quiz par IA (API externe)
- Gamification : badges, scores, chronomètre, 3 tentatives max
- Calculatrice intégrée pour les étudiants
- Statistiques de progression par étudiant
- Export des listes (PDF / Excel / CSV)
- Bloc-notes pour sauvegarder les suggestions

### 🗓️ Gestion des réunions
- Planification de réunions (titre, dates, lien Meet)
- Gestion des participants
- Envoi d'emails avec lien de réunion
- Intégration **Jitsi Meet** pour la visioconférence
- Accès contrôlé par email et période de validité
- Statistiques des réunions (admin)

### 💬 Gestion des forums
- Création de forums par l'administrateur (titre, description, catégorie)
- Publication, modification, suppression de messages
- Réactions (Like / Dislike)
- Recherche et filtrage par catégorie
- **Chatbot IA** (LLaMA3) avec support multilingue (FR / EN)
- Speech-to-Text (Whisper)
- Export de contenu (WhiteBoard : PNG, SVG, ZIP)
- Statistiques dynamiques (PieChart, BarChart)

---

## 🛠 Technologies utilisées

| Technologie | Utilisation |
|-------------|-------------|
| **PHP 8** | Langage backend principal |
| **Symfony 6** | Framework MVC |
| **Twig** | Moteur de templates |
| **MySQL** | Base de données relationnelle |
| **Doctrine ORM** | Mapping objet-relationnel |
| **Stripe / Paymee** | Paiement en ligne |
| **Cloudinary** | Stockage de fichiers PDF/images |
| **TinyMCE** | Éditeur de contenu riche |
| **Jitsi Meet** | Visioconférence |
| **OpenAI / GPT** | Résumé de cours, chatbot réclamations |
| **LLaMA3** | Chatbot forum multilingue |
| **Whisper** | Reconnaissance vocale (Speech-to-Text) |
| **YuNet (ONNX)** | Reconnaissance faciale |
| **Google OAuth2** | Authentification sociale |
| **JavaFX (partie desktop)** | Interface desktop (partie 2 du projet) |
| **Git & GitHub** | Versioning et collaboration |

---

## ✅ Prérequis

- PHP >= 8.1
- Composer
- Symfony CLI
- MySQL >= 8.0
- Node.js & npm (pour les assets frontend)

---

## 🚀 Installation

```bash
# 1. Cloner le dépôt
git clone https://github.com/israa-hannachi/PIDEV.git
cd PIDEV

# 2. Installer les dépendances PHP
composer install

# 3. Configurer l'environnement
cp .env .env.local
# Modifier DATABASE_URL et les clés API dans .env.local

# 4. Créer la base de données et appliquer les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 5. Installer les assets frontend
npm install
npm run build

# 6. Lancer le serveur de développement
symfony server:start
```

---

## 🗂 Structure du projet

```
PIDEV/
├── src/
│   ├── Controller/          # Contrôleurs Symfony
│   ├── Entity/              # Entités Doctrine (User, Cours, Categorie, Module...)
│   ├── Repository/          # Repositories
│   ├── Form/                # Formulaires Symfony
│   └── Service/             # Services métier
├── templates/               # Templates Twig
├── public/                  # Assets publics
├── migrations/              # Migrations Doctrine
└── config/                  # Configuration Symfony
```

---

## 👥 Équipe

Projet réalisé dans le cadre du **PIDEV** à l'école **Esprit** — groupe de 6 membres.

| Module | Responsable |
|--------|------------|
| Gestion des utilisateurs | chahine mezni |
| Gestion des cours | israa hannachi |
| Gestion des événements | isra dabbebi |
| Gestion des forums | farah jemmali |
| Gestion des quiz/jeux | miriam kouki |
| Gestion des réunions |imen ghamlouli |

> **Partie 2 du projet** → Application Desktop JavaFX : [PiJava](https://github.com/israa-hannachi/PiJava)

---

