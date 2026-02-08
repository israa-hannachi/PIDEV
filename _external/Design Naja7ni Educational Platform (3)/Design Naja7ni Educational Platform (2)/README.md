# Naja7ni - Plateforme Éducative

Une plateforme éducative moderne et intuitive construite avec HTML, CSS, et JavaScript vanilla.

## 🎨 Design

- **Couleurs principales:**
  - Primary: #0FB5A9 (Light Sea Green)
  - Secondary: #04B6D5 (Turquoise Surf)
  - Accent: #B3D54C (Yellow Green)

- **Style:** Clean, moderne, avec des coins arrondis, ombres douces et une esthétique fraîche et professionnelle

## 📁 Structure du Projet

```
/
├── index.html                 # Page principale
├── styles/
│   └── main.css              # Styles personnalisés
├── scripts/
│   ├── main.js               # Logique principale et navigation
│   └── modules.js            # Contenu des modules
└── README.md                 # Documentation
```

## 🚀 Modules Disponibles

1. **Accueil** - Dashboard avec statistiques, cours récents, badges et classement
2. **Profil** - Gestion du profil utilisateur
3. **Catégories** - Navigation hiérarchique : Catégories → Modules → Cours
4. **Meet** - Réunions virtuelles et cours en direct
5. **Jeux** - Jeux éducatifs interactifs avec gamification
6. **Events** - Événements et ateliers éducatifs
7. **Forums** - Forums de discussion communautaires

## 🛠️ Technologies Utilisées

- **HTML5** - Structure sémantique
- **CSS3** - Styles personnalisés
- **Tailwind CSS** - Framework CSS (via CDN)
- **JavaScript (Vanilla)** - Logique et interactivité
- **Lucide Icons** - Bibliothèque d'icônes (via CDN)

## 📦 Installation & Utilisation

### Option 1: Serveur Local Simple

```bash
# Avec Python 3
python -m http.server 8000

# Avec Node.js (http-server)
npx http-server

# Avec PHP
php -S localhost:8000
```

Puis ouvrez `http://localhost:8000` dans votre navigateur.

### Option 2: Ouvrir directement

Vous pouvez aussi simplement ouvrir le fichier `index.html` dans votre navigateur, mais certaines fonctionnalités peuvent nécessiter un serveur local.

## 🎯 Fonctionnalités

### Navigation
- Menu latéral vertical avec icônes et labels
- Navigation fluide entre les modules
- Barre de recherche globale
- Breadcrumbs pour la navigation hiérarchique

### Dashboard
- Statistiques de l'utilisateur
- Cours récents avec progression
- Badges et achievements
- Classement des utilisateurs

### Catégories
- Structure à 3 niveaux : Catégories → Modules → Cours
- Navigation intuitive avec retour en arrière
- Affichage détaillé des cours avec niveau et durée

### Autres Modules
- Réunions virtuelles avec gestion des participants
- Jeux éducatifs avec système de points
- Calendrier d'événements
- Forums de discussion avec catégories

## 🎨 Personnalisation

### Modifier les couleurs

Éditez la configuration Tailwind dans `index.html`:

```javascript
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#0FB5A9',    // Votre couleur primaire
                secondary: '#04B6D5',  // Votre couleur secondaire
                accent: '#B3D54C',     // Votre couleur d'accent
                // ...
            }
        }
    }
}
```

### Ajouter du contenu

Les données sont stockées dans `/scripts/modules.js`. Modifiez les objets JavaScript pour ajouter ou modifier:
- Catégories de cours
- Événements
- Sujets de forum
- Jeux
- etc.

## 📱 Responsive Design

L'application est entièrement responsive et s'adapte à:
- Desktop (1024px+)
- Tablette (768px - 1023px)
- Mobile (< 768px)

## 🌟 Points Clés

- ✅ Pas de framework JavaScript lourd (React, Vue, etc.)
- ✅ Code simple et maintenable
- ✅ Performance optimale
- ✅ Compatible tous navigateurs modernes
- ✅ Facilement personnalisable

## 📄 License

Ce projet est libre d'utilisation pour des fins éducatives.

## 👥 Contribution

Les contributions sont les bienvenues! N'hésitez pas à:
1. Fork le projet
2. Créer une branche (`git checkout -b feature/amelioration`)
3. Commit vos changements (`git commit -m 'Ajout d'une fonctionnalité'`)
4. Push vers la branche (`git push origin feature/amelioration`)
5. Ouvrir une Pull Request

---

Développé avec ❤️ pour l'éducation
