# EXIGENCES PROJET — SUNUJOB ÉTUDIANT
> Document de référence obligatoire pour toute conception ou développement sur ce projet. 
> Tout développeur ou designer intervenant sur ce projet **doit** respecter ces règles sans exception.

---

## 1. CHARTE GRAPHIQUE

### 🎨 Palette de couleurs officielle
> Extraite du logo officiel `logo.png`. **Aucune couleur extérieure à cette palette n'est autorisée.**

| Nom | Code HEX | Code RGB | Usage |
|---|---|---|---|
| **Bleu Marine** | `#1B3F72` | rgb(27, 63, 114) | Couleur principale — textes, boutons primaires, navbar |
| **Bleu Moyen** | `#2E6DB4` | rgb(46, 109, 180) | Liens, icônes, éléments secondaires |
| **Vert Émeraude** | `#2D9B4E` | rgb(45, 155, 78) | Accents, badges succès, flèche du logo |
| **Orange Vif** | `#F5A623` | rgb(245, 166, 35) | Call-to-action, highlights, texte "ÉTUDIANT" |
| **Blanc Cassé** | `#F5F4F0` | rgb(245, 244, 240) | Fond principal des pages |
| **Blanc Pur** | `#FFFFFF` | rgb(255, 255, 255) | Fond des cartes, modals, formulaires |
| **Gris Clair** | `#E8E8E6` | rgb(232, 232, 230) | Bordures, séparateurs, fond inputs |
| **Gris Texte** | `#6B7280` | rgb(107, 114, 128) | Textes secondaires, placeholders |

### 📐 Variables CSS obligatoires
> À déclarer dans chaque fichier CSS du projet (`style.css` ou `variables.css`).

```css
:root {
  /* Couleurs principales */
  --color-primary:       #1B3F72; /* Bleu Marine */
  --color-primary-light: #2E6DB4; /* Bleu Moyen */
  --color-accent-green:  #2D9B4E; /* Vert Émeraude */
  --color-accent-orange: #F5A623; /* Orange Vif */

  /* Fonds */
  --color-bg:            #F5F4F0; /* Fond principal */
  --color-white:         #FFFFFF; /* Blanc pur */
  --color-border:        #E8E8E6; /* Bordures */

  /* Textes */
  --color-text-main:     #1B3F72; /* Texte principal */
  --color-text-muted:    #6B7280; /* Texte secondaire */

  /* Dégradés */
  --gradient-primary: linear-gradient(135deg, #1B3F72, #2E6DB4);
  --gradient-accent:  linear-gradient(135deg, #2D9B4E, #F5A623);
}
```

---

## 2. TYPOGRAPHIE

### Polices autorisées
| Usage | Police | Source |
|---|---|---|
| Titres / Headings | **Poppins Bold / SemiBold** | Google Fonts |
| Corps de texte | **Poppins Regular / Medium** | Google Fonts |
| Boutons / Labels | **Poppins SemiBold** | Google Fonts |

```html
<!-- Intégration dans chaque page HTML -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
```

```css
body {
  font-family: 'Poppins', sans-serif;
  color: var(--color-text-main);
  background-color: var(--color-bg);
}
```

### Hiérarchie typographique
| Élément | Taille | Poids | Couleur |
|---|---|---|---|
| `h1` | 2rem | 700 | `--color-primary` |
| `h2` | 1.5rem | 600 | `--color-primary` |
| `h3` | 1.25rem | 600 | `--color-primary` |
| Paragraphe | 1rem | 400 | `--color-text-main` |
| Petit texte | 0.875rem | 400 | `--color-text-muted` |

---

## 3. COMPOSANTS UI — RÈGLES BOOTSTRAP

### Boutons
```html
<!-- Bouton principal -->
<button class="btn btn-primary-custom">Postuler</button>

<!-- Bouton secondaire -->
<button class="btn btn-outline-custom">Voir plus</button>

<!-- Bouton CTA (appel à l'action) -->
<button class="btn btn-cta">Publier une mission</button>
```

```css
.btn-primary-custom {
  background-color: var(--color-primary);
  border-color: var(--color-primary);
  color: var(--color-white);
  border-radius: 8px;
  font-weight: 600;
  padding: 10px 24px;
  transition: background 0.3s ease;
}
.btn-primary-custom:hover {
  background-color: var(--color-primary-light);
}

.btn-cta {
  background-color: var(--color-accent-orange);
  border-color: var(--color-accent-orange);
  color: var(--color-white);
  border-radius: 8px;
  font-weight: 700;
}
.btn-cta:hover {
  background-color: #d4911e;
}

.btn-outline-custom {
  border: 2px solid var(--color-primary);
  color: var(--color-primary);
  background: transparent;
  border-radius: 8px;
  font-weight: 600;
}
.btn-outline-custom:hover {
  background-color: var(--color-primary);
  color: var(--color-white);
}
```

### Navbar
```html
<nav class="navbar navbar-expand-lg navbar-sunujob">
  <a class="navbar-brand" href="#">
    <img src="assets/images/logo.png" alt="SunuJob Étudiant" height="45">
  </a>
  <!-- ... -->
</nav>
```
```css
.navbar-sunujob {
  background: var(--gradient-primary);
  box-shadow: 0 2px 12px rgba(27, 63, 114, 0.3);
}
.navbar-sunujob .nav-link {
  color: var(--color-white) !important;
  font-weight: 500;
}
.navbar-sunujob .nav-link:hover {
  color: var(--color-accent-orange) !important;
}
```

### Cards (cartes de missions)
```css
.card-mission {
  border: 1px solid var(--color-border);
  border-radius: 12px;
  background: var(--color-white);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card-mission:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(27, 63, 114, 0.15);
}
.card-mission .badge-categorie {
  background-color: var(--color-primary-light);
  color: var(--color-white);
  border-radius: 20px;
  font-size: 0.75rem;
  padding: 4px 12px;
}
.card-mission .remuneration {
  color: var(--color-accent-green);
  font-weight: 700;
}
```

### Badges de statut
```css
.badge-active    { background-color: var(--color-accent-green);  color: #fff; }
.badge-attente   { background-color: var(--color-accent-orange); color: #fff; }
.badge-fermee    { background-color: var(--color-text-muted);    color: #fff; }
.badge-acceptee  { background-color: var(--color-accent-green);  color: #fff; }
.badge-refusee   { background-color: #DC3545;                    color: #fff; }
```

### Formulaires
```css
.form-control:focus {
  border-color: var(--color-primary-light);
  box-shadow: 0 0 0 0.2rem rgba(46, 109, 180, 0.25);
}
.form-label {
  color: var(--color-primary);
  font-weight: 600;
}
```

---

## 4. STRUCTURE DU PROJET

```
sunujob-etudiant/
│
├── assets/
│   ├── images/
│   │   └── logo.png              ← Logo officiel (NE PAS MODIFIER)
│   ├── css/
│   │   ├── variables.css         ← Variables CSS (couleurs, typo)
│   │   └── style.css             ← Styles globaux du projet
│   └── js/
│       └── main.js
│
├── includes/
│   ├── db.php                    ← Connexion base de données
│   ├── header.php                ← Navbar commune
│   ├── footer.php                ← Footer commun
│   └── auth.php                  ← Fonctions d'authentification
│
├── pages/
│   ├── etudiant/
│   │   ├── profil.php
│   │   ├── candidatures.php
│   │   └── dashboard.php
│   └── recruteur/
│       ├── profil.php
│       ├── missions.php
│       └── dashboard.php
│
├── index.php                     ← Page d'accueil
├── login.php                     ← Connexion
├── register.php                  ← Inscription
├── missions.php                  ← Liste des missions
├── mission-detail.php            ← Détail d'une mission
└── EXIGENCES_PROJET.md           ← Ce fichier (racine du projet)
```

---

## 5. RÈGLES DE DÉVELOPPEMENT PHP

### Connexion base de données (`includes/db.php`)
```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sunujob_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}
?>
```

### Sécurité obligatoire
- **Toujours** utiliser les requêtes préparées (`prepare` / `bind_param`) — jamais de concaténation directe dans les requêtes SQL.
- **Toujours** hasher les mots de passe avec `password_hash($mdp, PASSWORD_BCRYPT)`.
- **Toujours** vérifier les sessions avant d'accéder aux pages protégées.
- **Toujours** utiliser `htmlspecialchars()` pour afficher des données utilisateurs.
- **Toujours** valider et filtrer les données avec `filter_input()` ou `htmlspecialchars()`.

```php
// ✅ Correct
$stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE email = ?");
$stmt->bind_param("s", $email);

// ❌ Interdit
$query = "SELECT * FROM utilisateurs WHERE email = '$email'";
```

### Structure d'une page type
```php
<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
// Vérification session si page protégée
verifier_session('etudiant');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Page — SunuJob Étudiant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/variables.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <main class="container py-4">
    <!-- Contenu de la page -->
  </main>

  <?php include 'includes/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>
```

---

## 6. RÈGLES DE DESIGN UI/UX

### À faire ✅
- Utiliser **uniquement** les couleurs de la palette officielle.
- Garder le logo visible et bien proportionné dans la navbar.
- Utiliser `--gradient-primary` pour les headers de sections importantes.
- Mettre l'orange (`--color-accent-orange`) sur les boutons d'action principale (CTA).
- Mettre le vert (`--color-accent-green`) pour les éléments positifs (succès, rémunération, statut actif).
- Conserver des `border-radius` harmonieux (8px pour les boutons, 12px pour les cartes).
- Responsive first : toutes les pages doivent être fonctionnelles sur mobile.

### À ne pas faire ❌
- ❌ Utiliser une couleur non présente dans la palette du logo.
- ❌ Modifier ou remplacer le logo officiel.
- ❌ Utiliser d'autres polices que Poppins.
- ❌ Utiliser Bootstrap tel quel sans personnalisation (surcharger avec le CSS custom).
- ❌ Utiliser du rouge pour autre chose que les erreurs / refus.
- ❌ Créer des pages non responsives.

---

## 7. VERSIONS DES DÉPENDANCES

| Outil / Librairie | Version | CDN |
|---|---|---|
| Bootstrap | 5.3.x | `https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/` |
| Font Awesome | 6.x | `https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css` |
| Google Fonts (Poppins) | — | `https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700` |
| PHP | 8.x | — |
| MySQL | 8.x | — |

---

> **Ce fichier fait foi.** En cas de doute sur un choix de couleur, de police ou de composant, se référer à ce document en priorité.

---

## Documents connectés

- [Cahier des charges](./cahier_des_charges_sunujob.md) — contexte, objectifs et vision produit.
- [Suivi des fonctionnalités](./FONCTIONNALITES_SUIVI.md) — road map de développement et priorisation.
- [Base de données](./base_de_donnees_sunujob.md) — schéma SQL et relations des tables.
- [README](./README.md) — point d'entrée unique pour toute la documentation.
