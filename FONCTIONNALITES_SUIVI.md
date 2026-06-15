# ✅ SUIVI DES FONCTIONNALITÉS — SUNUJOB ÉTUDIANT
> Fichier de suivi du développement. Cocher chaque tâche au fur et à mesure de l'avancement.
> Classé par **ordre de priorité** : du plus critique au plus secondaire.

---

## LÉGENDE
- 🔴 **PRIORITÉ 1** — Fondations (sans ça, rien ne fonctionne)
- 🟠 **PRIORITÉ 2** — Cœur métier (fonctionnalités principales)
- 🟡 **PRIORITÉ 3** — Expérience utilisateur (confort et praticité)
- 🟢 **PRIORITÉ 4** — Améliorations (finitions et bonus)

---

## 🔴 PRIORITÉ 1 — FONDATIONS DU PROJET

### ⚙️ Étape 1 — Mise en place de l'environnement
- [ ] Créer la base de données `sunujob_db` dans MySQL
- [ ] Créer les 6 tables (`utilisateurs`, `profils_etudiants`, `profils_recruteurs`, `categories`, `missions`, `candidatures`)
- [ ] Insérer les données de test dans la table `categories`
- [ ] Créer le fichier `includes/db.php` (connexion base de données)
- [ ] Créer le fichier `includes/auth.php` (fonctions d'authentification)
- [ ] Créer le fichier `assets/css/variables.css` (palette de couleurs)
- [ ] Créer le fichier `assets/css/style.css` (styles globaux)
- [ ] Placer le logo `logo.png` dans `assets/images/`
- [ ] Créer le `includes/header.php` (navbar commune)
- [ ] Créer le `includes/footer.php` (footer commun)

---

### 👤 Étape 2 — Système d'authentification

#### Inscription
- [ ] Créer la page `register.php`
- [ ] Afficher le formulaire d'inscription (nom, prénom, email, téléphone, mot de passe, rôle)
- [ ] Valider les champs côté client (JavaScript)
- [ ] Valider les champs côté serveur (PHP)
- [ ] Vérifier que l'email n'existe pas déjà en base
- [ ] Hasher le mot de passe avec `password_hash()`
- [ ] Insérer l'utilisateur dans la table `utilisateurs`
- [ ] Rediriger vers le tableau de bord après inscription
- [ ] Afficher un message de succès ou d'erreur

#### Connexion
- [ ] Créer la page `login.php`
- [ ] Afficher le formulaire de connexion (email, mot de passe)
- [ ] Vérifier les identifiants avec `password_verify()`
- [ ] Créer la session utilisateur (`$_SESSION`)
- [ ] Rediriger selon le rôle (`etudiant` → dashboard étudiant, `recruteur` → dashboard recruteur)
- [ ] Afficher un message d'erreur si identifiants incorrects

#### Déconnexion
- [ ] Créer la route de déconnexion `logout.php`
- [ ] Détruire la session avec `session_destroy()`
- [ ] Rediriger vers `login.php`

#### Récupération de mot de passe
- [ ] Créer la page `forgot-password.php`
- [ ] Afficher le formulaire de saisie d'email
- [ ] Générer un token de réinitialisation
- [ ] Stocker le token dans `utilisateurs.token_reset`
- [ ] Créer la page `reset-password.php`
- [ ] Vérifier le token et permettre la saisie d'un nouveau mot de passe
- [ ] Mettre à jour le mot de passe et effacer le token

---

## 🟠 PRIORITÉ 2 — CŒUR MÉTIER

### 🏠 Étape 3 — Page d'accueil
- [ ] Créer la page `index.php`
- [ ] Section Hero (titre, slogan, boutons CTA étudiant / recruteur)
- [ ] Section statistiques (nombre de missions, étudiants inscrits, recruteurs)
- [ ] Section catégories de missions avec icônes
- [ ] Section des dernières missions publiées (6 missions en aperçu)
- [ ] Section "Comment ça marche ?" (3 étapes illustrées)
- [ ] Footer avec liens et informations de contact

---

### 📋 Étape 4 — Gestion des missions (Recruteur)

#### Publication
- [ ] Créer la page `pages/recruteur/ajouter-mission.php`
- [ ] Formulaire de création (titre, description, catégorie, localisation, type, rémunération, dates, places)
- [ ] Valider tous les champs (PHP)
- [ ] Insérer la mission dans la table `missions`
- [ ] Rediriger vers la liste des missions après création
- [ ] Afficher un message de confirmation

#### Liste des missions du recruteur
- [ ] Créer la page `pages/recruteur/mes-missions.php`
- [ ] Afficher toutes les missions publiées par le recruteur connecté
- [ ] Afficher le statut de chaque mission (active / fermée / expirée)
- [ ] Afficher le nombre de candidatures reçues par mission

#### Modification
- [ ] Créer la page `pages/recruteur/modifier-mission.php`
- [ ] Pré-remplir le formulaire avec les données existantes
- [ ] Valider et mettre à jour en base de données

#### Suppression
- [ ] Ajouter un bouton supprimer sur chaque mission
- [ ] Afficher une confirmation avant suppression
- [ ] Supprimer la mission et ses candidatures associées

#### Fermeture de mission
- [ ] Ajouter un bouton "Fermer la mission"
- [ ] Mettre à jour le statut à `fermee` en base

---

### 🔍 Étape 5 — Recherche et liste des missions (Étudiant)

#### Liste publique des missions
- [ ] Créer la page `missions.php`
- [ ] Afficher toutes les missions actives avec pagination
- [ ] Afficher pour chaque mission : titre, catégorie, localisation, rémunération, date
- [ ] Lien vers le détail de la mission

#### Recherche et filtres
- [ ] Ajouter un champ de recherche par mot-clé
- [ ] Filtre par catégorie (menu déroulant)
- [ ] Filtre par localisation
- [ ] Filtre par type de mission (ponctuelle / temps partiel / stage)
- [ ] Filtre par rémunération (min / max)
- [ ] Bouton "Réinitialiser les filtres"
- [ ] Afficher le nombre de résultats trouvés

#### Détail d'une mission
- [ ] Créer la page `mission-detail.php`
- [ ] Afficher toutes les informations de la mission
- [ ] Afficher le profil du recruteur
- [ ] Bouton "Postuler" visible uniquement pour les étudiants connectés
- [ ] Message "Connectez-vous pour postuler" si non connecté
- [ ] Indicateur si l'étudiant a déjà postulé à cette mission

---

### 📨 Étape 6 — Gestion des candidatures

#### Côté Étudiant — Postuler
- [ ] Afficher un formulaire de motivation (message)
- [ ] Vérifier que l'étudiant n'a pas déjà postulé (contrainte UNIQUE)
- [ ] Insérer la candidature dans la table `candidatures`
- [ ] Afficher un message de confirmation après envoi

#### Côté Étudiant — Suivi
- [ ] Créer la page `pages/etudiant/mes-candidatures.php`
- [ ] Lister toutes les candidatures avec le statut (en attente / acceptée / refusée)
- [ ] Afficher la date de candidature et le titre de la mission
- [ ] Badge coloré selon le statut

#### Côté Recruteur — Gestion
- [ ] Créer la page `pages/recruteur/candidatures.php`
- [ ] Lister toutes les candidatures reçues par mission
- [ ] Afficher le profil de l'étudiant candidat
- [ ] Bouton "Accepter" → met le statut à `acceptee`
- [ ] Bouton "Refuser" → met le statut à `refusee`

---

### 👤 Étape 7 — Gestion des profils

#### Profil Étudiant
- [ ] Créer la page `pages/etudiant/profil.php`
- [ ] Formulaire de mise à jour (université, niveau, filière, compétences, disponibilité, bio)
- [ ] Upload de photo de profil
- [ ] Upload de CV (PDF)
- [ ] Sauvegarder les modifications en base
- [ ] Afficher un message de succès

#### Profil Recruteur
- [ ] Créer la page `pages/recruteur/profil.php`
- [ ] Formulaire de mise à jour (nom structure, type, site web, description)
- [ ] Upload de logo de structure
- [ ] Sauvegarder les modifications en base

---

### 📊 Étape 8 — Tableaux de bord

#### Dashboard Étudiant
- [ ] Créer la page `pages/etudiant/dashboard.php`
- [ ] Afficher le nom et la photo de l'étudiant
- [ ] Carte : nombre total de candidatures envoyées
- [ ] Carte : nombre de candidatures acceptées
- [ ] Carte : nombre de candidatures en attente
- [ ] Liste des 5 dernières candidatures
- [ ] Lien rapide vers la recherche de missions

#### Dashboard Recruteur
- [ ] Créer la page `pages/recruteur/dashboard.php`
- [ ] Afficher le nom et le logo de la structure
- [ ] Carte : nombre total de missions publiées
- [ ] Carte : nombre total de candidatures reçues
- [ ] Carte : nombre de missions actives
- [ ] Liste des 5 dernières missions avec leur nombre de candidatures
- [ ] Lien rapide vers la publication d'une mission

---

## 🟡 PRIORITÉ 3 — EXPÉRIENCE UTILISATEUR

### 🛡️ Étape 9 — Sécurité et validation
- [ ] Protéger toutes les pages privées avec vérification de session
- [ ] Vérifier le rôle sur chaque page (un étudiant ne peut pas accéder aux pages recruteur)
- [ ] Utiliser `htmlspecialchars()` sur toutes les sorties HTML
- [ ] Utiliser des requêtes préparées sur toutes les requêtes SQL
- [ ] Limiter la taille des uploads (photo, CV, logo)
- [ ] Vérifier les extensions autorisées pour les fichiers uploadés
- [ ] Ajouter des tokens CSRF sur les formulaires sensibles

### 📱 Étape 10 — Responsive Design
- [ ] Vérifier le rendu sur mobile (< 576px)
- [ ] Vérifier le rendu sur tablette (576px – 992px)
- [ ] Vérifier le rendu sur desktop (> 992px)
- [ ] Navbar avec menu hamburger sur mobile
- [ ] Grilles Bootstrap adaptées à chaque breakpoint
- [ ] Images et logos bien redimensionnés sur mobile

### 🎨 Étape 11 — Interface et cohérence visuelle
- [ ] Vérifier que toutes les pages respectent la charte graphique (`EXIGENCES_PROJET.md`)
- [ ] Aucune couleur hors palette utilisée
- [ ] Police Poppins appliquée sur toutes les pages
- [ ] Logo présent et bien affiché dans la navbar
- [ ] Messages de succès en vert (`--color-accent-green`)
- [ ] Messages d'erreur en rouge
- [ ] Badges de statut avec les bonnes couleurs
- [ ] Animations de transition sur les cartes au survol
- [ ] Footer cohérent sur toutes les pages

### ⚠️ Étape 12 — Gestion des erreurs
- [ ] Page 404 personnalisée
- [ ] Messages d'erreur clairs sur les formulaires
- [ ] Gestion des cas "aucun résultat" dans la recherche
- [ ] Gestion des cas "aucune candidature" sur le dashboard
- [ ] Gestion des cas "aucune mission" publiée

---

## 🟢 PRIORITÉ 4 — AMÉLIORATIONS ET FINITIONS

### 📄 Étape 13 — Pages statiques
- [ ] Créer la page `a-propos.php` (présentation du projet)
- [ ] Créer la page `contact.php` (formulaire de contact)
- [ ] Créer la page `mentions-legales.php`

### 🔔 Étape 14 — Notifications et alertes
- [ ] Afficher une alerte quand une candidature change de statut
- [ ] Afficher une alerte quand une nouvelle candidature est reçue (recruteur)
- [ ] Compteur de notifications dans la navbar

### 🧪 Étape 15 — Tests et déploiement
- [ ] Tester toutes les fonctionnalités avec des données réelles
- [ ] Tester les cas d'erreur (mauvais identifiants, doublons, champs vides)
- [ ] Tester le responsive sur différents appareils
- [ ] Vérifier la cohérence visuelle sur toutes les pages
- [ ] Corriger les bugs identifiés
- [ ] Optimiser les requêtes SQL (index sur les clés étrangères)
- [ ] Déployer sur un hébergement (InfinityFree, 000webhost ou autre)
- [ ] Tester en ligne sur mobile et desktop

---

## 📊 TABLEAU DE PROGRESSION

| Priorité | Étapes | Total tâches | Complétées | Progression |
|---|---|---|---|---|
| 🔴 P1 — Fondations | Étapes 1 – 2 | 37 | 0 | 0% |
| 🟠 P2 — Cœur métier | Étapes 3 – 8 | 72 | 0 | 0% |
| 🟡 P3 — Expérience | Étapes 9 – 12 | 28 | 0 | 0% |
| 🟢 P4 — Finitions | Étapes 13 – 15 | 18 | 0 | 0% |
| **TOTAL** | **15 étapes** | **155** | **0** | **0%** |

---

> 💡 **Conseil** : Ne pas passer à la priorité suivante avant d'avoir terminé la priorité en cours.
> Commencer toujours par **l'Étape 1** et avancer dans l'ordre.

---

## Documents reliés

- [Cahier des charges](./cahier_des_charges_sunujob.md) — vision générale et périmètre du projet.
- [Exigences du projet](./EXIGENCES_PROJET.md) — directives techniques et charte graphique.
- [Base de données](./base_de_donnees_sunujob.md) — tables, relations et script de création.
- [README](./README.md) — index du projet et synthèse.
