-- ============================================
-- DONNÉES DE TEST — SUNUJOB ÉTUDIANT
-- Mot de passe pour tous les comptes : Test123!
-- ============================================

USE sunujob_db;

SET @mdp = '$2y$10$vfc6kC0ErTX4opNR9/Pf6Oo60w30bT1E8Hmypm/NBCTaM7ABasSpm';

-- ============================================
-- RECRUTEURS
-- ============================================
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, localisation, statut) VALUES
('Ndiaye', 'Fatou', 'contact@eventpro.sn', @mdp, '77 234 56 78', 'recruteur', 'Dakar', 'actif'),
('Kane', 'Moussa', 'rh@techsenegal.sn', @mdp, '76 345 67 89', 'recruteur', 'Dakar', 'actif'),
('Sy', 'Awa', 'awa.sy@cafeteranga.sn', @mdp, '78 456 78 90', 'recruteur', 'Saint-Louis', 'actif'),
('Fall', 'Ibrahima', 'ibrahima.fall@gmail.com', @mdp, '70 567 89 01', 'recruteur', 'Rufisque', 'actif'),
('Ba', 'Mariama', 'mariama@digitalwave.sn', @mdp, '75 678 90 12', 'recruteur', 'Thiès', 'actif'),
('Sarr', 'Omar', 'omar.sarr@agencemedia.sn', @mdp, '77 890 12 34', 'recruteur', 'Dakar', 'actif');

INSERT INTO profils_recruteurs (utilisateur_id, nom_structure, type_recruteur, site_web, description) VALUES
((SELECT id FROM utilisateurs WHERE email = 'contact@eventpro.sn'), 'EventPro Sénégal', 'agence', 'https://eventpro.sn', 'Agence événementielle spécialisée dans l''organisation de mariages, conférences et festivals à Dakar et en région.'),
((SELECT id FROM utilisateurs WHERE email = 'rh@techsenegal.sn'), 'TechSénégal', 'startup', 'https://techsenegal.sn', 'Startup tech dakaroise développant des solutions numériques pour les PME sénégalaises.'),
((SELECT id FROM utilisateurs WHERE email = 'awa.sy@cafeteranga.sn'), 'Café Teranga', 'commercant', NULL, 'Café-restaurant au cœur de Saint-Louis, nous recrutons régulièrement des étudiants pour le service et l''accueil.'),
((SELECT id FROM utilisateurs WHERE email = 'ibrahima.fall@gmail.com'), 'Cours Ibra', 'particulier', NULL, 'Professeur particulier en mathématiques et physique, je recherche des étudiants pour donner des cours à domicile.'),
((SELECT id FROM utilisateurs WHERE email = 'mariama@digitalwave.sn'), 'DigitalWave', 'entreprise', 'https://digitalwave.sn', 'Agence de communication digitale : community management, création de contenu et gestion des réseaux sociaux.'),
((SELECT id FROM utilisateurs WHERE email = 'omar.sarr@agencemedia.sn'), 'Agence Média Plus', 'agence', 'https://mediaplus.sn', 'Agence de production audiovisuelle et couverture médiatique d''événements sportifs et culturels.');

-- ============================================
-- ÉTUDIANTS (emails scolaires)
-- ============================================
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone, role, localisation, statut) VALUES
('Diop', 'Cheikh', 'cheikh.diop@etu.ucad.sn', @mdp, '77 111 22 33', 'etudiant', 'Dakar', 'actif'),
('Sow', 'Aminata', 'aminata.sow@ugb.sn', @mdp, '76 222 33 44', 'etudiant', 'Saint-Louis', 'actif'),
('Gueye', 'Ousmane', 'ousmane.gueye@esp.sn', @mdp, '78 333 44 55', 'etudiant', 'Dakar', 'actif'),
('Mbaye', 'Khady', 'khady.mbaye@etu.ucad.sn', @mdp, '70 444 55 66', 'etudiant', 'Dakar', 'actif'),
('Faye', 'Modou', 'modou.faye@uam.sn', @mdp, '75 555 66 77', 'etudiant', 'Ziguinchor', 'actif'),
('Niang', 'Rokhaya', 'rokha.niang@univ-thies.sn', @mdp, '77 666 77 88', 'etudiant', 'Thiès', 'actif'),
('Cissé', 'Mamadou', 'mamadou.cisse@etu.ucad.sn', @mdp, '76 777 88 99', 'etudiant', 'Dakar', 'actif');

INSERT INTO profils_etudiants (utilisateur_id, universite, niveau_etude, filiere, competences, disponibilite, bio) VALUES
((SELECT id FROM utilisateurs WHERE email = 'cheikh.diop@etu.ucad.sn'), 'UCAD', 'Licence 3', 'Informatique', 'PHP, HTML/CSS, JavaScript, MySQL, WordPress', 'Week-ends et mercredis après-midi', 'Étudiant passionné par le développement web, je cherche des missions en informatique pour gagner de l''expérience.'),
((SELECT id FROM utilisateurs WHERE email = 'aminata.sow@ugb.sn'), 'UGB Saint-Louis', 'Master 1', 'Marketing & Communication', 'Réseaux sociaux, Canva, rédaction, organisation d''événements', 'Tous les jours après 14h', 'Créative et organisée, j''adore le community management et l''événementiel.'),
((SELECT id FROM utilisateurs WHERE email = 'ousmane.gueye@esp.sn'), 'ESP Dakar', 'Licence 2', 'Génie Civil', 'AutoCAD, gestion de chantier, dessin technique', 'Vacances et samedis', 'Étudiant sérieux en génie civil, disponible pour des missions ponctuelles.'),
((SELECT id FROM utilisateurs WHERE email = 'khady.mbaye@etu.ucad.sn'), 'UCAD', 'Licence 2', 'Sciences Économiques', 'Excel, comptabilité de base, accueil client', 'Lundi, mercredi, vendredi', 'Motivée et ponctuelle, je cherche des missions en accueil ou administration.'),
((SELECT id FROM utilisateurs WHERE email = 'modou.faye@uam.sn'), 'UAM Ziguinchor', 'Licence 3', 'Anglais', 'Anglais courant, français, traduction, cours particuliers', 'Flexible selon emploi du temps', 'Étudiant bilingue, je propose des cours d''anglais et de soutien scolaire.'),
((SELECT id FROM utilisateurs WHERE email = 'rokha.niang@univ-thies.sn'), 'Université de Thiès', 'Licence 1', 'Commerce', 'Vente, accueil, livraison, relation client', 'Week-ends uniquement', 'Dynamique et souriante, je suis disponible pour des missions de livraison et d''accueil.'),
((SELECT id FROM utilisateurs WHERE email = 'mamadou.cisse@etu.ucad.sn'), 'UCAD', 'Master 2', 'Journalisme', 'Rédaction, interview, montage vidéo, réseaux sociaux', 'Après 16h en semaine', 'Futur journaliste, je cherche des missions en rédaction et couverture d''événements.');

-- ============================================
-- MISSIONS
-- ============================================
INSERT INTO missions (recruteur_id, categorie_id, titre, description, localisation, type_mission, remuneration, date_debut, date_fin, places_disponibles, nb_vues, statut) VALUES
-- EventPro Sénégal
((SELECT id FROM utilisateurs WHERE email = 'contact@eventpro.sn'), 2, 'Assistant événementiel — Mariage à Almadies',
 'Nous recherchons 4 étudiants dynamiques pour assister lors d''un mariage de 300 personnes à Almadies. Missions : accueil des invités, coordination avec le traiteur, gestion du parking. Tenue correcte exigée, briefing la veille à 18h.',
 'Dakar — Almadies', 'ponctuelle', 25000, '2026-06-28', '2026-06-28', 4, 47, 'active'),

((SELECT id FROM utilisateurs WHERE email = 'contact@eventpro.sn'), 2, 'Hôtesse/Hôte — Salon de l''Étudiant UCAD',
 'EventPro recrute des hôtesses et hôtes pour le Salon de l''Emploi Étudiant à l''UCAD. Accueil des visiteurs, distribution de flyers, orientation vers les stands. Bonne présentation et aisance relationnelle requises.',
 'Dakar — UCAD', 'ponctuelle', 20000, '2026-07-05', '2026-07-06', 6, 89, 'active'),

-- TechSénégal
((SELECT id FROM utilisateurs WHERE email = 'rh@techsenegal.sn'), 4, 'Développeur Web Junior — Site vitrine PME',
 'TechSénégal cherche un étudiant en informatique pour développer un site vitrine responsive pour une PME sénégalaise. Technologies : HTML, CSS, PHP, MySQL. Durée estimée : 3 semaines. Travail en télétravail possible.',
 'Dakar — Plateau', 'temps_partiel', 150000, '2026-06-20', '2026-07-15', 1, 124, 'active'),

((SELECT id FROM utilisateurs WHERE email = 'rh@techsenegal.sn'), 4, 'Technicien support informatique',
 'Assistance technique pour configuration de postes informatiques et installation de logiciels dans nos bureaux à Sicap Liberté. Connaissances Windows et réseaux souhaitées.',
 'Dakar — Sicap Liberté', 'ponctuelle', 30000, '2026-06-18', '2026-06-20', 2, 56, 'active'),

-- Café Teranga
((SELECT id FROM utilisateurs WHERE email = 'awa.sy@cafeteranga.sn'), 6, 'Serveur/Serveuse — Week-end Saint-Louis',
 'Notre café recherche un(e) étudiant(e) pour le service en salle les samedis et dimanches. Prise de commandes, service à table, encaissement. Expérience appréciée mais formation assurée.',
 'Saint-Louis', 'temps_partiel', 35000, '2026-06-15', '2026-08-31', 2, 38, 'active'),

-- Cours Ibra (particulier)
((SELECT id FROM utilisateurs WHERE email = 'ibrahima.fall@gmail.com'), 1, 'Cours particuliers Maths — Niveau BFEM',
 'Recherche un étudiant en maths/sciences pour donner des cours de mathématiques à un élève en 3ème (préparation BFEM). 2 séances par semaine de 2h, à domicile à Rufisque.',
 'Rufisque', 'temps_partiel', 40000, '2026-06-16', '2026-07-31', 1, 72, 'active'),

((SELECT id FROM utilisateurs WHERE email = 'ibrahima.fall@gmail.com'), 1, 'Soutien scolaire Physique-Chimie — Terminale',
 'Cours de physique-chimie pour une élève en Terminale S. Objectif : préparation au bac. 3h par semaine, flexible sur les horaires.',
 'Dakar — Parcelles Assainies', 'temps_partiel', 50000, '2026-06-10', '2026-06-30', 1, 45, 'active'),

-- DigitalWave
((SELECT id FROM utilisateurs WHERE email = 'mariama@digitalwave.sn'), 5, 'Community Manager — Boutique mode en ligne',
 'Gestion des réseaux sociaux (Instagram, TikTok, Facebook) d''une boutique de mode sénégalaise. Création de 3 posts/semaine, réponse aux commentaires, reporting mensuel.',
 'Dakar — télétravail', 'temps_partiel', 80000, '2026-06-15', '2026-09-15', 1, 156, 'active'),

((SELECT id FROM utilisateurs WHERE email = 'mariama@digitalwave.sn'), 5, 'Créateur de contenu vidéo — Réseaux sociaux',
 'Tournage et montage de vidéos courtes (Reels/TikTok) pour promouvoir des produits locaux. Smartphone récent avec bonne caméra suffisant.',
 'Thiès', 'temps_partiel', 60000, '2026-07-01', '2026-08-31', 2, 93, 'active'),

-- Agence Média Plus
((SELECT id FROM utilisateurs WHERE email = 'omar.sarr@agencemedia.sn'), 2, 'Photographe événementiel — Match de foot',
 'Couverture photo d''un match de football en League 1. Livraison de 50 photos retouchées sous 48h. Appareil photo personnel requis.',
 'Dakar — Stade Demba Diop', 'ponctuelle', 45000, '2026-06-22', '2026-06-22', 1, 67, 'active'),

-- Recruteur existant (id 2)
(2, 3, 'Livreur à vélo — Plateau Dakar', 'Livraison de repas et colis dans le quartier du Plateau. Vélo personnel obligatoire. Horaires flexibles entre 11h et 20h.', 'Dakar — Plateau', 'temps_partiel', 25000, '2026-06-15', '2026-12-31', 3, 201, 'active'),
(2, 6, 'Agent d''accueil — Bureau administratif', 'Accueil physique et téléphonique, gestion du courrier et orientation des visiteurs dans un bureau administratif au centre-ville.', 'Dakar — Médina', 'temps_partiel', 40000, '2026-07-01', '2026-09-30', 1, 34, 'active'),
(2, 4, 'Stage développement application mobile', 'Stage de 2 mois pour participer au développement d''une application mobile de livraison. Flutter ou React Native souhaité.', 'Dakar', 'stage', 100000, '2026-07-01', '2026-08-31', 2, 178, 'active'),

-- Mission expirée (test fermeture auto)
((SELECT id FROM utilisateurs WHERE email = 'contact@eventpro.sn'), 2, 'Hôte d''accueil — Conférence passée', 'Mission terminée — conférence sur l''entrepreneuriat étudiant.', 'Dakar', 'ponctuelle', 15000, '2026-03-01', '2026-03-01', 2, 42, 'expiree'),

-- Mission fermée manuellement
((SELECT id FROM utilisateurs WHERE email = 'mariama@digitalwave.sn'), 5, 'Rédacteur web — Articles blog (pourvu)', 'Rédaction d''articles pour le blog d''une startup. Mission pourvue.', 'Dakar', 'temps_partiel', 70000, '2026-05-01', '2026-06-30', 1, 88, 'fermee');

-- ============================================
-- CANDIDATURES (pour démo des statuts)
-- ============================================
INSERT INTO candidatures (etudiant_id, mission_id, message_motivation, statut) VALUES
-- Cheikh → Dev Web Junior
((SELECT id FROM utilisateurs WHERE email = 'cheikh.diop@etu.ucad.sn'),
 (SELECT id FROM missions WHERE titre LIKE 'Développeur Web Junior%'),
 'Bonjour, je suis en Licence 3 Informatique à l''UCAD et maîtrise PHP/MySQL. J''ai déjà réalisé 2 projets web similaires. Je suis très motivé pour cette mission !', 'acceptee'),

-- Aminata → Community Manager
((SELECT id FROM utilisateurs WHERE email = 'aminata.sow@ugb.sn'),
 (SELECT id FROM missions WHERE titre LIKE 'Community Manager%'),
 'Passionnée par les réseaux sociaux, je gère déjà le compte Instagram de mon association étudiante avec 2000 abonnés. Je serais ravie de contribuer à votre projet.', 'en_cours'),

-- Ousmane → Assistant événementiel
((SELECT id FROM utilisateurs WHERE email = 'ousmane.gueye@esp.sn'),
 (SELECT id FROM missions WHERE titre LIKE 'Assistant événementiel — Mariage%'),
 'Disponible le week-end du 28 juin. J''ai déjà assisté à 3 mariages en tant qu''hôte. Ponctuel et présentable.', 'en_attente'),

-- Khady → Agent accueil Médina
((SELECT id FROM utilisateurs WHERE email = 'khady.mbaye@etu.ucad.sn'),
 (SELECT id FROM missions WHERE titre LIKE 'Agent d''accueil — Bureau%'),
 'J''ai de l''expérience en accueil lors de salons étudiants. Sérieuse et à l''écoute.', 'en_attente'),

-- Modou → Cours anglais (via soutien scolaire)
((SELECT id FROM utilisateurs WHERE email = 'modou.faye@uam.sn'),
 (SELECT id FROM missions WHERE titre LIKE 'Soutien scolaire Physique%'),
 'Étudiant bilingue avec expérience en tutorat. Je peux aussi aider en anglais si besoin.', 'refusee'),

-- Rokhaya → Livreur vélo
((SELECT id FROM utilisateurs WHERE email = 'rokha.niang@univ-thies.sn'),
 (SELECT id FROM missions WHERE titre LIKE 'Livreur à vélo%'),
 'Je possède un vélo et connais bien le Plateau. Disponible tous les après-midis.', 'terminee'),

-- Mamadou → Photographe match
((SELECT id FROM utilisateurs WHERE email = 'mamadou.cisse@etu.ucad.sn'),
 (SELECT id FROM missions WHERE titre LIKE 'Photographe événementiel%'),
 'Étudiant en journalisme avec appareil Canon EOS. Portfolio disponible sur demande.', 'en_attente'),

-- Houleye existante → Salon étudiant
(1,
 (SELECT id FROM missions WHERE titre LIKE 'Hôtesse/Hôte — Salon%'),
 'Bonjour, je suis disponible pour le salon. Motivée et souriante !', 'en_attente');
