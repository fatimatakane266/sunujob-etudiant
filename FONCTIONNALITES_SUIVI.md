# ✅ SUIVI DES FONCTIONNALITÉS — SUNUJOB ÉTUDIANT
> Fichier de suivi du développement. Cocher chaque tâche au fur et à mesure de l'avancement.
> Classé par **ordre de priorité** : du plus critique au plus secondaire.
> Dernière mise à jour : état réel vérifié en conditions de test (serveur + base de données), pas seulement lu dans le code.

---

## LÉGENDE
- 🔴 **PRIORITÉ 1** — Fondations (sans ça, rien ne fonctionne)
- 🟠 **PRIORITÉ 2** — Cœur métier (fonctionnalités principales)
- 🟡 **PRIORITÉ 3** — Expérience utilisateur (confort et praticité)
- 🟢 **PRIORITÉ 4** — Améliorations (finitions et bonus)

---

## 🔴 PRIORITÉ 1 — FONDATIONS DU PROJET

### ⚙️ Étape 1 — Mise en place de l'environnement
- [x] Créer la base de données `sunujob_db` dans MySQL
- [x] Créer les tables (`utilisateurs`, `profils_etudiants`, `profils_recruteurs`, `categories`, `missions`, `candidatures`, `notifications`)
- [x] Insérer les données de test dans la table `categories`
- [x] Créer le fichier `includes/db.php` (connexion base de données)
- [x] Créer le fichier `includes/auth.php` (fonctions d'authentification)
- [x] Créer le fichier `assets/css/variables.css` (palette de couleurs)
- [x] Créer le fichier `assets/css/style.css` (styles globaux)
- [x] Placer le logo `logo.png` dans `assets/images/`
- [x] Créer le `includes/header.php` (navbar commune)
- [x] Créer le `includes/footer.php` (footer commun)

---

### 👤 Étape 2 — Système d'authentification

#### Inscription
- [x] Créer la page `register.php`
- [x] Afficher le formulaire d'inscription (nom, prénom, email, téléphone, mot de passe, rôle)
- [x] Valider les champs côté client (JavaScript)
- [x] Valider les champs côté serveur (PHP)
- [x] Vérifier que l'email n'existe pas déjà en base
- [x] Hasher le mot de passe avec `password_hash()`
- [x] Insérer l'utilisateur dans la table `utilisateurs`
- [x] Rediriger vers le tableau de bord après inscription
- [x] Afficher un message de succès ou d'erreur

#### Connexion
- [x] Créer la page `login.php`
- [x] Afficher le formulaire de connexion (email, mot de passe)
- [x] Vérifier les identifiants avec `password_verify()`
- [x] Créer la session utilisateur (`$_SESSION`)
- [x] Rediriger selon le rôle (`etudiant` → dashboard étudiant, `recruteur` → dashboard recruteur, `admin` → dashboard admin)
- [x] Afficher un message d'erreur si identifiants incorrects

#### Déconnexion
- [x] Créer la route de déconnexion `logout.php`
- [x] Détruire la session avec `session_destroy()`
- [x] Rediriger vers `login.php`

#### Récupération de mot de passe
- [x] Créer la page `forgot-password.php`
- [x] Afficher le formulaire de saisie d'email
- [x] Générer un token de réinitialisation
- [x] Stocker le token dans `utilisateurs.token_reset`
- [x] Créer la page `reset-password.php`
- [x] Vérifier le token et permettre la saisie d'un nouveau mot de passe
- [x] Mettre à jour le mot de passe et effacer le token

> ⚠️ En production, `forgot-password.php` doit envoyer un vrai email au lieu d'afficher le lien de réinitialisation directement à l'écran (mode démo actuel).

---

## 🟠 PRIORITÉ 2 — CŒUR MÉTIER

### 🏠 Étape 3 — Page d'accueil
- [x] Créer la page `index.php`
- [x] Section Hero (titre, slogan, boutons CTA étudiant / recruteur)
- [x] Section statistiques (nombre de missions, étudiants inscrits, recruteurs)
- [x] Section catégories de missions avec icônes
- [x] Section des dernières missions publiées
- [x] Section "Comment ça marche ?"
- [x] Footer avec liens et informations de contact

---

### 📋 Étape 4 — Gestion des missions (Recruteur)

#### Publication
- [x] Créer la page `pages/recruteur/ajouter-mission.php`
- [x] Formulaire de création (titre, description, catégorie, localisation, type, rémunération, dates, places)
- [x] Valider tous les champs (PHP)
- [x] Insérer la mission dans la table `missions`
- [x] Rediriger vers la liste des missions après création
- [x] Afficher un message de confirmation

#### Liste des missions du recruteur
- [x] Créer la page `pages/recruteur/mes-missions.php`
- [x] Afficher toutes les missions publiées par le recruteur connecté
- [x] Afficher le statut de chaque mission (active / fermée / expirée)
- [x] Afficher le nombre de candidatures reçues par mission

#### Modification
- [x] Créer la page `pages/recruteur/modifier-mission.php`
- [x] Pré-remplir le formulaire avec les données existantes
- [x] Valider et mettre à jour en base de données
- [x] Réactiver automatiquement une mission expirée si la nouvelle date de fin redevient valide

#### Suppression
- [x] Ajouter un bouton supprimer sur chaque mission
- [x] Afficher une confirmation avant suppression
- [x] Supprimer la mission et ses candidatures associées

#### Fermeture de mission
- [x] Ajouter un bouton "Fermer la mission"
- [x] Mettre à jour le statut à `fermee` en base
- [x] Permettre la réouverture si la date de fin n'est pas dépassée

---

### 🛠️ Étape 4bis — Administration (rôle admin)
> Non prévue dans la version initiale du cahier des charges, ajoutée en cours de développement — voir `cahier_des_charges_sunujob.md` §7.6.

- [x] Créer le rôle `admin` dans l'ENUM `utilisateurs.role`
- [x] Créer un compte admin par défaut au premier lancement (`admin@sunujob.sn`)
- [x] Créer `pages/admin/dashboard.php` (statistiques globales de la plateforme)
- [x] Créer `pages/admin/users.php` (activer / désactiver / supprimer un compte)
- [x] Créer `pages/admin/missions.php` (modérer / supprimer une mission)
- [x] Créer `pages/admin/categories.php` (créer / modifier une catégorie)
- [x] Protéger toutes les pages admin avec `verifierSession('admin')`
- [x] Protéger les actions de modération (suppression/statut) par un jeton CSRF

---

### 🔍 Étape 5 — Recherche et liste des missions (Étudiant)

#### Liste publique des missions
- [x] Créer la page `missions.php`
- [x] Afficher toutes les missions actives avec pagination
- [x] Afficher pour chaque mission : titre, catégorie, localisation, rémunération, date
- [x] Lien vers le détail de la mission

#### Recherche et filtres
- [x] Ajouter un champ de recherche par mot-clé
- [x] Filtre par catégorie (menu déroulant)
- [x] Filtre par localisation
- [x] Filtre par type de mission (ponctuelle / temps partiel / stage)
- [x] Filtre par rémunération (min / max)
- [x] Bouton "Réinitialiser les filtres"
- [x] Afficher le nombre de résultats trouvés

#### Détail d'une mission
- [x] Créer la page `mission-detail.php`
- [x] Afficher toutes les informations de la mission
- [x] Afficher le profil du recruteur
- [x] Bouton "Postuler" visible uniquement pour les étudiants connectés
- [x] Message "Connectez-vous pour postuler" si non connecté
- [x] Indicateur si l'étudiant a déjà postulé à cette mission

---

### 📨 Étape 6 — Gestion des candidatures

#### Côté Étudiant — Postuler
- [x] Afficher un formulaire de motivation (message)
- [x] Vérifier que l'étudiant n'a pas déjà postulé (contrainte UNIQUE)
- [x] Insérer la candidature dans la table `candidatures`
- [x] Afficher un message de confirmation après envoi

#### Côté Étudiant — Suivi
- [x] Créer la page `pages/etudiant/mes-candidatures.php`
- [x] Lister toutes les candidatures avec le statut (en attente / acceptée / refusée / en cours / terminée)
- [x] Afficher la date de candidature et le titre de la mission
- [x] Badge coloré selon le statut

#### Côté Recruteur — Gestion
- [x] Créer la page `pages/recruteur/candidatures.php`
- [x] Lister toutes les candidatures reçues par mission
- [x] Afficher le profil de l'étudiant candidat
- [x] Bouton "Accepter" → met le statut à `acceptee`
- [x] Bouton "Refuser" → met le statut à `refusee`
- [x] Suivi post-acceptation (`en_cours` → `terminee` à la date de fin de mission)

---

### 👤 Étape 7 — Gestion des profils

#### Profil Étudiant
- [x] Créer la page `pages/etudiant/profil.php`
- [x] Formulaire de mise à jour (université, niveau, filière, compétences, disponibilité, bio)
- [x] Upload de photo de profil
- [x] Upload de CV (PDF)
- [x] Sauvegarder les modifications en base
- [x] Afficher un message de succès

#### Profil Recruteur
- [x] Créer la page `pages/recruteur/profil.php`
- [x] Formulaire de mise à jour (nom structure, type, site web, description)
- [x] Upload de logo de structure
- [x] Sauvegarder les modifications en base

---

### 📊 Étape 8 — Tableaux de bord

#### Dashboard Étudiant
- [x] Créer la page `pages/etudiant/dashboard.php`
- [x] Afficher le nom et la photo de l'étudiant
- [x] Carte : nombre total de candidatures envoyées
- [x] Carte : nombre de candidatures acceptées
- [x] Carte : nombre de candidatures en attente
- [x] Liste des dernières candidatures
- [x] Lien rapide vers la recherche de missions

#### Dashboard Recruteur
- [x] Créer la page `pages/recruteur/dashboard.php`
- [x] Afficher le nom et le logo de la structure
- [x] Carte : nombre total de missions publiées
- [x] Carte : nombre total de candidatures reçues
- [x] Carte : nombre de missions actives
- [x] Liste des dernières missions avec leur nombre de candidatures
- [x] Lien rapide vers la publication d'une mission

---

## 🟡 PRIORITÉ 3 — EXPÉRIENCE UTILISATEUR

### 🛡️ Étape 9 — Sécurité et validation
- [x] Protéger toutes les pages privées avec vérification de session
- [x] Vérifier le rôle sur chaque page (un étudiant ne peut pas accéder aux pages recruteur/admin)
- [x] Utiliser `htmlspecialchars()` sur toutes les sorties HTML
- [x] Utiliser des requêtes préparées sur toutes les requêtes SQL
- [x] Limiter la taille des uploads (photo, CV, logo)
- [x] Vérifier les extensions autorisées pour les fichiers uploadés
- [x] Ajouter des tokens CSRF sur les formulaires sensibles

> ⚠️ Le mot de passe MySQL (`includes/db.php`) est en clair dans le code et commité dans git — à sortir en variable d'environnement avant toute mise en ligne publique.

### 📱 Étape 10 — Responsive Design
- [ ] Vérifier le rendu sur mobile (< 576px)
- [ ] Vérifier le rendu sur tablette (576px – 992px)
- [ ] Vérifier le rendu sur desktop (> 992px)
- [x] Navbar avec menu hamburger sur mobile
- [x] Grilles Bootstrap adaptées à chaque breakpoint
- [ ] Images et logos bien redimensionnés sur mobile

> Non vérifié visuellement dans un vrai navigateur à ce stade (tests faits en HTTP direct) — à valider avant la soutenance.

### 🎨 Étape 11 — Interface et cohérence visuelle
- [ ] Vérifier que toutes les pages respectent la charte graphique (`EXIGENCES_PROJET.md`)
- [ ] Aucune couleur hors palette utilisée
- [x] Police Poppins appliquée sur toutes les pages
- [x] Logo présent et bien affiché dans la navbar
- [x] Messages de succès en vert (`--color-accent-green`)
- [x] Messages d'erreur en rouge
- [x] Badges de statut avec les bonnes couleurs
- [x] Animations de transition sur les cartes au survol
- [x] Footer cohérent sur toutes les pages

### ⚠️ Étape 12 — Gestion des erreurs
- [x] Page 404 personnalisée
- [x] Messages d'erreur clairs sur les formulaires
- [x] Gestion des cas "aucun résultat" dans la recherche
- [x] Gestion des cas "aucune candidature" sur le dashboard
- [x] Gestion des cas "aucune mission" publiée

---

## 🟢 PRIORITÉ 4 — AMÉLIORATIONS ET FINITIONS

### 📄 Étape 13 — Pages statiques
- [x] Créer la page `a-propos.php` (présentation du projet)
- [x] Créer la page `contact.php` (formulaire de contact)
- [x] Créer la page `mentions-legales.php`

### 🔔 Étape 14 — Notifications et alertes
- [x] Afficher une alerte quand une candidature change de statut
- [x] Afficher une alerte quand une nouvelle candidature est reçue (recruteur)
- [x] Compteur de notifications dans la navbar

### 🧪 Étape 15 — Tests et déploiement
- [x] Tester toutes les fonctionnalités avec des données réelles
- [x] Tester les cas d'erreur (mauvais identifiants, doublons, champs vides)
- [ ] Tester le responsive sur différents appareils
- [ ] Vérifier la cohérence visuelle sur toutes les pages
- [ ] Corriger les bugs identifiés *(en continu — voir les derniers signalés)*
- [ ] Optimiser les requêtes SQL (index sur les clés étrangères)
- [ ] Déployer sur un hébergement (InfinityFree, 000webhost ou autre)
- [ ] Tester en ligne sur mobile et desktop

---

## 📊 TABLEAU DE PROGRESSION

| Priorité | Étapes | Total tâches | Complétées | Progression |
|---|---|---|---|---|
| 🔴 P1 — Fondations | Étapes 1 – 2 | 37 | 37 | 100% |
| 🟠 P2 — Cœur métier | Étapes 3 – 8 (+ 4bis Admin) | 79 | 79 | 100% |
| 🟡 P3 — Expérience | Étapes 9 – 12 | 28 | 21 | 75% |
| 🟢 P4 — Finitions | Étapes 13 – 15 | 18 | 8 | 44% |
| **TOTAL** | **15 étapes + admin** | **162** | **145** | **~90%** |

---

> 💡 **Ce qui reste réellement à faire** : validation visuelle responsive (mobile/tablette/desktop) dans un vrai navigateur, audit exhaustif de la charte graphique, optimisation SQL, et déploiement en ligne. Le cœur fonctionnel (comptes, missions, candidatures, dashboards, admin) est opérationnel et testé de bout en bout.

---

## Documents reliés

- [Cahier des charges](./cahier_des_charges_sunujob.md) — vision générale et périmètre du projet.
- [Exigences du projet](./EXIGENCES_PROJET.md) — directives techniques et charte graphique.
- [Base de données](./base_de_donnees_sunujob.md) — tables, relations et script de création.
- [README](./README.md) — index du projet et synthèse.
