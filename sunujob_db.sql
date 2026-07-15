-- MySQL dump 10.13  Distrib 8.0.37, for Linux (x86_64)
--
-- Host: localhost    Database: sunujob_db
-- ------------------------------------------------------
-- Server version	8.0.37-0ubuntu0.23.10.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `candidatures`
--

DROP TABLE IF EXISTS `candidatures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidatures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `etudiant_id` int NOT NULL,
  `mission_id` int NOT NULL,
  `message_motivation` text,
  `statut` enum('en_attente','acceptee','refusee','en_cours','terminee') DEFAULT 'en_attente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_candidature` (`etudiant_id`,`mission_id`),
  KEY `idx_candidatures_etudiant` (`etudiant_id`),
  KEY `idx_candidatures_mission` (`mission_id`),
  CONSTRAINT `candidatures_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidatures_ibfk_2` FOREIGN KEY (`mission_id`) REFERENCES `missions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidatures`
--

LOCK TABLES `candidatures` WRITE;
/*!40000 ALTER TABLE `candidatures` DISABLE KEYS */;
INSERT INTO `candidatures` VALUES (1,1,2,'j souhaite vous donner des cours car sws motivee,engage','terminee','2026-06-12 23:54:38','2026-06-29 09:53:47'),(2,1,3,'suis interesse','terminee','2026-06-14 21:23:52','2026-06-29 09:54:16'),(3,10,8,'Bonjour, je suis en Licence 3 Informatique à l\'UCAD et maîtrise PHP/MySQL. J\'ai déjà réalisé 2 projets web similaires. Je suis très motivé pour cette mission !','acceptee','2026-06-15 23:37:32','2026-06-15 23:37:32'),(5,12,6,'Disponible le week-end du 28 juin. J\'ai déjà assisté à 3 mariages en tant qu\'hôte. Ponctuel et présentable.','en_attente','2026-06-15 23:37:32','2026-06-15 23:37:32'),(6,13,17,'J\'ai de l\'expérience en accueil lors de salons étudiants. Sérieuse et à l\'écoute.','refusee','2026-06-15 23:37:32','2026-07-02 20:23:56'),(7,14,12,'Étudiant bilingue avec expérience en tutorat. Je peux aussi aider en anglais si besoin.','refusee','2026-06-15 23:37:32','2026-06-15 23:37:32'),(8,15,16,'Je possède un vélo et connais bien le Plateau. Disponible tous les après-midis.','terminee','2026-06-15 23:37:32','2026-06-15 23:37:32'),(10,1,7,'Bonjour, je suis disponible pour le salon. Motivée et souriante !','en_attente','2026-06-15 23:37:32','2026-06-15 23:37:32'),(11,1,8,'tyuhj','en_attente','2026-07-01 10:39:10','2026-07-01 10:39:10'),(12,1,21,'cours complet en informatique','acceptee','2026-07-01 11:03:22','2026-07-01 14:34:58'),(21,1,32,'j sws motiveee','en_attente','2026-07-11 22:12:13','2026-07-11 22:12:13');
/*!40000 ALTER TABLE `candidatures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `icone` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Cours particuliers','fa-book'),(2,'Événementiel','fa-calendar'),(3,'Livraison','fa-motorcycle'),(4,'Informatique','fa-laptop'),(5,'Community Management','fa-hashtag'),(6,'Agent d\'accueil','fa-handshake'),(7,'Autre','fa-ellipsis-h');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `missions`
--

DROP TABLE IF EXISTS `missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `missions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `recruteur_id` int NOT NULL,
  `categorie_id` int NOT NULL,
  `titre` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `localisation` varchar(100) NOT NULL,
  `type_mission` enum('ponctuelle','temps_partiel','stage') NOT NULL,
  `remuneration` decimal(10,2) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `jours_travail` varchar(100) DEFAULT NULL,
  `heures_travail` varchar(100) DEFAULT NULL,
  `places_disponibles` int DEFAULT '1',
  `nb_vues` int NOT NULL DEFAULT '0',
  `statut` enum('active','fermee','expiree') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_missions_recruteur` (`recruteur_id`),
  KEY `idx_missions_categorie` (`categorie_id`),
  KEY `idx_missions_statut` (`statut`),
  KEY `idx_missions_localisation` (`localisation`),
  CONSTRAINT `missions_ibfk_1` FOREIGN KEY (`recruteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `missions_ibfk_2` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `missions`
--

LOCK TABLES `missions` WRITE;
/*!40000 ALTER TABLE `missions` DISABLE KEYS */;
INSERT INTO `missions` VALUES (2,2,2,'assistant evenementiel','gerer les taches','dakar, ouakam','ponctuelle',20000.00,'2026-06-20','2026-06-30',NULL,NULL,2,2,'expiree','2026-06-12 23:49:47','2026-07-01 10:13:40'),(3,2,3,'livrer','livrer des commandes','hml,dakar','temps_partiel',4000.00,'2026-06-16','2026-06-17',NULL,NULL,1,0,'fermee','2026-06-12 23:51:50','2026-06-29 09:54:09'),(6,4,2,'Assistant événementiel — Mariage à Almadies','Nous recherchons 4 étudiants dynamiques pour assister lors d\'un mariage de 300 personnes à Almadies. Missions : accueil des invités, coordination avec le traiteur, gestion du parking. Tenue correcte exigée, briefing la veille à 18h.','Dakar — Almadies','ponctuelle',25000.00,'2026-06-28','2026-06-28',NULL,NULL,4,49,'expiree','2026-06-15 23:37:32','2026-07-10 20:51:57'),(7,4,2,'Hôtesse/Hôte — Salon de l\'Étudiant UCAD','EventPro recrute des hôtesses et hôtes pour le Salon de l\'Emploi Étudiant à l\'UCAD. Accueil des visiteurs, distribution de flyers, orientation vers les stands. Bonne présentation et aisance relationnelle requises.','Dakar — UCAD','ponctuelle',20000.00,'2026-07-05','2026-07-06',NULL,NULL,6,91,'expiree','2026-06-15 23:37:32','2026-07-08 20:29:45'),(8,5,4,'Développeur Web Junior — Site vitrine PME','TechSénégal cherche un étudiant en informatique pour développer un site vitrine responsive pour une PME sénégalaise. Technologies : HTML, CSS, PHP, MySQL. Durée estimée : 3 semaines. Travail en télétravail possible.','Dakar — Plateau','temps_partiel',150000.00,'2026-06-20','2026-07-15',NULL,NULL,1,129,'active','2026-06-15 23:37:32','2026-07-01 10:39:10'),(9,5,4,'Technicien support informatique','Assistance technique pour configuration de postes informatiques et installation de logiciels dans nos bureaux à Sicap Liberté. Connaissances Windows et réseaux souhaitées.','Dakar — Sicap Liberté','ponctuelle',30000.00,'2026-06-18','2026-06-20',NULL,NULL,2,56,'expiree','2026-06-15 23:37:32','2026-06-24 20:20:55'),(10,6,6,'Serveur/Serveuse — Week-end Saint-Louis','Notre café recherche un(e) étudiant(e) pour le service en salle les samedis et dimanches. Prise de commandes, service à table, encaissement. Expérience appréciée mais formation assurée.','Saint-Louis','temps_partiel',35000.00,'2026-06-15','2026-08-31',NULL,NULL,2,39,'active','2026-06-15 23:37:32','2026-07-10 12:46:36'),(11,7,1,'Cours particuliers Maths — Niveau BFEM','Recherche un étudiant en maths/sciences pour donner des cours de mathématiques à un élève en 3ème (préparation BFEM). 2 séances par semaine de 2h, à domicile à Rufisque.','Rufisque','temps_partiel',40000.00,'2026-06-16','2026-07-31',NULL,NULL,1,72,'active','2026-06-15 23:37:32','2026-06-15 23:37:32'),(12,7,1,'Soutien scolaire Physique-Chimie — Terminale','Cours de physique-chimie pour une élève en Terminale S. Objectif : préparation au bac. 3h par semaine, flexible sur les horaires.','Dakar — Parcelles Assainies','temps_partiel',50000.00,'2026-06-10','2026-06-30',NULL,NULL,1,46,'expiree','2026-06-15 23:37:32','2026-07-01 10:13:40'),(16,2,3,'Livreur à vélo — Gueultape','Livraison de repas et colis dans le quartier du Plateau. Vélo personnel obligatoire. Horaires flexibles entre 11h et 20h.','Dakar — Gueultape','temps_partiel',25000.00,'2026-06-15','2026-12-31',NULL,NULL,3,202,'active','2026-06-15 23:37:32','2026-07-02 21:25:18'),(17,2,6,'Agent d\'accueil — Bureau administratif','Accueil physique et téléphonique, gestion du courrier et orientation des visiteurs dans un bureau administratif au centre-ville.','Dakar — Médina','temps_partiel',40000.00,'2026-07-01','2026-09-30',NULL,NULL,1,34,'active','2026-06-15 23:37:32','2026-06-15 23:37:32'),(18,2,4,'Stage développement application mobile','Stage de 2 mois pour participer au développement d\'une application mobile de livraison. Flutter ou React Native souhaité.','Dakar','stage',100000.00,'2026-07-01','2026-08-31',NULL,NULL,2,178,'active','2026-06-15 23:37:32','2026-06-15 23:37:32'),(19,4,2,'Hôte d\'accueil — Conférence passée','Mission terminée — conférence sur l\'entrepreneuriat étudiant.','Dakar','ponctuelle',15000.00,'2026-03-01','2026-03-01',NULL,NULL,2,42,'expiree','2026-06-15 23:37:32','2026-06-15 23:37:32'),(21,2,1,'cours informatique','fgbhjnmkl','keur massar','temps_partiel',20000.00,'2026-07-06','2026-08-06',NULL,NULL,1,3,'fermee','2026-07-01 11:02:28','2026-07-02 21:26:50'),(32,2,1,'Cours particuliers de mathématiques - Niveau lycée','Nous recherchons un étudiant pédagogue pour donner des cours de soutien en mathématiques à un élève de Terminale S. Séances à domicile, préparation aux évaluations et au baccalauréat. Bon relationnel et patience exigés. Une première séance d\'essai sera organisée.','Parcelle, Dakar','temps_partiel',6000.00,'2026-07-25','2026-08-28','Lundi, Mercredi, Vendredi','17h00 - 19h00',3,8,'active','2026-07-10 22:36:39','2026-07-13 13:39:51'),(33,2,2,'Hôte/Hôtesse d\'accueil - Salon professionnel','Recherche étudiants dynamiques et souriants pour l\'accueil des visiteurs lors d\'un salon professionnel de 3 jours au CICAD. Missions : orientation du public, remise de badges, assistance aux exposants. Tenue correcte exigée, formation assurée sur place.','Diamniadio, Dakar','ponctuelle',15000.00,'2026-08-05','2026-08-07','Mercredi, Jeudi, Vendredi','08h00 - 18h00',4,3,'active','2026-07-10 22:36:39','2026-07-10 22:38:08'),(34,2,3,'Livreur à moto - Zone Plateau / Médina','Structure de restauration rapide recherche un livreur disponible pour les commandes du midi et du soir. Moto personnelle exigée (essence remboursée). Bonne connaissance de Dakar et des quartiers environnants indispensable. Poste évolutif selon disponibilité.','Plateau, Dakar','temps_partiel',3000.00,'2026-07-25','2026-10-25','Tous les jours','11h30 - 14h30 et 19h00 - 22h00',2,4,'active','2026-07-10 22:36:39','2026-07-10 23:12:54'),(35,2,4,'Assistant technique - Maintenance informatique','PME recherche un étudiant en informatique pour assister le service technique : installation de postes, résolution de pannes simples, assistance aux utilisateurs. Connaissances de base Windows/réseau requises. Bonne occasion de première expérience professionnelle.','Almadies, Dakar','stage',75000.00,'2026-08-01','2026-10-31','Lundi au Vendredi','09h00 - 15h00',1,3,'active','2026-07-10 22:36:39','2026-07-10 22:38:08'),(36,2,5,'Community Manager - Page Facebook et Instagram','Boutique en ligne recherche un(e) étudiant(e) créatif(ve) pour gérer ses réseaux sociaux : création de visuels, rédaction de publications, réponses aux messages clients, suivi des statistiques. Expérience Canva ou outils similaires appréciée. Travail à distance possible.','Ouakam, Dakar','temps_partiel',50000.00,'2026-07-22','2026-10-22','Lundi, Mardi, Jeudi','14h00 - 17h00',1,3,'active','2026-07-10 22:36:39','2026-07-10 22:38:09'),(37,2,6,'Agent d\'accueil - Standard et réception','Cabinet médical recherche un(e) étudiant(e) pour assurer l\'accueil physique et téléphonique des patients, la prise de rendez-vous et l\'orientation. Présentation soignée et sens du contact indispensables. Une formation courte sera assurée en début de mission.','Point E, Dakar','temps_partiel',45000.00,'2026-07-28','2026-12-28','Lundi au Vendredi','08h30 - 13h00',1,3,'active','2026-07-10 22:36:39','2026-07-10 22:38:09'),(38,2,7,'Enquêteur terrain - Étude de marché','Cabinet d\'études recherche des étudiants motivés pour réaliser des enquêtes terrain auprès des commerçants et particuliers dans plusieurs quartiers de Dakar. Formation à l\'outil de collecte fournie. Idéal pour étudiants en marketing, sociologie ou gestion.','Grand Yoff, Dakar','ponctuelle',20000.00,'2026-07-24','2026-07-31','Lundi, Mardi, Mercredi','09h00 - 16h00',5,3,'active','2026-07-10 22:36:39','2026-07-10 22:38:09');
/*!40000 ALTER TABLE `missions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `type` enum('info','alerte','success','erreur') DEFAULT 'info',
  `titre` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `lien` varchar(255) DEFAULT NULL,
  `lu` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_utilisateur` (`utilisateur_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,2,'info','Nouvelle candidature','Un étudiant a postulé à votre mission : assistant evenementiel','/pages/recruteur/candidatures.php?mission=2',0,'2026-06-12 23:54:38'),(2,1,'success','Candidature acceptée !','Votre candidature a été acceptée.','/pages/etudiant/mes-candidatures.php',0,'2026-06-12 23:56:29'),(3,2,'info','Nouvelle candidature','Un étudiant a postulé à votre mission : livrer','/pages/recruteur/candidatures.php?mission=3',0,'2026-06-14 21:23:52'),(4,1,'success','Mission terminée','Votre mission est marquée comme terminée.','/pages/etudiant/mes-candidatures.php',0,'2026-06-29 09:53:47'),(5,1,'success','Candidature acceptée !','Votre candidature a été acceptée.','/pages/etudiant/mes-candidatures.php',0,'2026-06-29 09:54:09'),(6,1,'success','Mission terminée','Votre mission est marquée comme terminée.','/pages/etudiant/mes-candidatures.php',0,'2026-06-29 09:54:17'),(7,5,'info','Nouvelle candidature','Un étudiant a postulé à votre mission : Développeur Web Junior — Site vitrine PME','/pages/recruteur/candidatures.php?mission=8',0,'2026-07-01 10:39:10'),(8,2,'info','Nouvelle candidature','Un étudiant a postulé à votre mission : cours informatique','/pages/recruteur/candidatures.php?mission=21',0,'2026-07-01 11:03:22'),(9,1,'success','Candidature acceptée !','Votre candidature a été acceptée.','/pages/etudiant/mes-candidatures.php',0,'2026-07-01 14:34:58'),(10,2,'info','Nouvelle candidature','Un étudiant a postulé à votre mission : sdfghjl','/pages/recruteur/candidatures.php?mission=23',0,'2026-07-01 15:35:52'),(11,1,'success','Mission acceptée','Félicitations ! Votre candidature pour \"sdfghjl\" a été acceptée. Veuillez vous présenter à xcgj Jours : lundi,mercredi | Horaires : 09-13h. Apportez votre pièce d\'identité, votre carte d\'étudiant et votre CV pour finaliser votre mission.','/pages/etudiant/mes-candidatures.php',0,'2026-07-01 15:36:33'),(12,13,'info','Candidature refusée','Votre candidature a été refusée.','/pages/etudiant/mes-candidatures.php',0,'2026-07-02 20:23:56'),(14,2,'info','Nouvelle candidature','Un étudiant a postulé à votre mission : livrer','/pages/recruteur/candidatures.php?mission=24',0,'2026-07-03 09:29:41'),(15,2,'info','Nouvelle candidature','Un étudiant a postulé à votre mission : jduiklknkl','/pages/recruteur/candidatures.php?mission=25',0,'2026-07-03 22:16:00'),(16,2,'info','Nouvelle candidature','Un étudiant a postulé à votre mission : xdcfgvbhjnk','/pages/recruteur/candidatures.php?mission=22',0,'2026-07-03 22:16:35'),(17,2,'info','Nouvelle candidature','Un étudiant a postulé à votre mission : ugkckl;','/pages/recruteur/candidatures.php?mission=26',0,'2026-07-03 22:24:38'),(18,1,'info','Candidature refusée','Votre candidature a été refusée.','/pages/etudiant/mes-candidatures.php',0,'2026-07-08 20:36:21'),(22,2,'info','Nouvelle candidature','Un étudiant a postulé à votre mission : Cours particuliers de mathématiques - Niveau lycée','/pages/recruteur/candidatures.php?mission=32',0,'2026-07-11 22:12:13');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profils_etudiants`
--

DROP TABLE IF EXISTS `profils_etudiants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profils_etudiants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `universite` varchar(150) DEFAULT NULL,
  `niveau_etude` varchar(50) DEFAULT NULL,
  `filiere` varchar(100) DEFAULT NULL,
  `competences` text,
  `cv` varchar(255) DEFAULT NULL,
  `disponibilite` varchar(100) DEFAULT NULL,
  `bio` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `profils_etudiants_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profils_etudiants`
--

LOCK TABLES `profils_etudiants` WRITE;
/*!40000 ALTER TABLE `profils_etudiants` DISABLE KEYS */;
INSERT INTO `profils_etudiants` VALUES (1,1,'isep diamniadio','BTS','informatique','gestion de projet',NULL,'24h/24h','j suis serieuse, motive'),(2,10,'UCAD','Licence 3','Informatique','PHP, HTML/CSS, JavaScript, MySQL, WordPress',NULL,'Week-ends et mercredis après-midi','Étudiant passionné par le développement web, je cherche des missions en informatique pour gagner de l\'expérience.'),(3,11,'UGB Saint-Louis','Master 1','Marketing & Communication','Réseaux sociaux, Canva, rédaction, organisation d\'événements',NULL,'Tous les jours après 14h','Créative et organisée, j\'adore le community management et l\'événementiel.'),(4,12,'ESP Dakar','Licence 2','Génie Civil','AutoCAD, gestion de chantier, dessin technique',NULL,'Vacances et samedis','Étudiant sérieux en génie civil, disponible pour des missions ponctuelles.'),(5,13,'UCAD','Licence 2','Sciences Économiques','Excel, comptabilité de base, accueil client',NULL,'Lundi, mercredi, vendredi','Motivée et ponctuelle, je cherche des missions en accueil ou administration.'),(6,14,'UAM Ziguinchor','Licence 3','Anglais','Anglais courant, français, traduction, cours particuliers',NULL,'Flexible selon emploi du temps','Étudiant bilingue, je propose des cours d\'anglais et de soutien scolaire.'),(7,15,'Université de Thiès','Licence 1','Commerce','Vente, accueil, livraison, relation client',NULL,'Week-ends uniquement','Dynamique et souriante, je suis disponible pour des missions de livraison et d\'accueil.'),(8,16,'UCAD','Master 2','Journalisme','Rédaction, interview, montage vidéo, réseaux sociaux',NULL,'Après 16h en semaine','Futur journaliste, je cherche des missions en rédaction et couverture d\'événements.');
/*!40000 ALTER TABLE `profils_etudiants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profils_recruteurs`
--

DROP TABLE IF EXISTS `profils_recruteurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profils_recruteurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `nom_structure` varchar(150) DEFAULT NULL,
  `type_recruteur` enum('entreprise','startup','agence','commercant','particulier') DEFAULT NULL,
  `site_web` varchar(255) DEFAULT NULL,
  `description` text,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `profils_recruteurs_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profils_recruteurs`
--

LOCK TABLES `profils_recruteurs` WRITE;
/*!40000 ALTER TABLE `profils_recruteurs` DISABLE KEYS */;
INSERT INTO `profils_recruteurs` VALUES (1,2,NULL,'particulier','','',NULL),(2,4,'EventPro Sénégal','agence','https://eventpro.sn','Agence événementielle spécialisée dans l\'organisation de mariages, conférences et festivals à Dakar et en région.',NULL),(3,5,'TechSénégal','startup','https://techsenegal.sn','Startup tech dakaroise développant des solutions numériques pour les PME sénégalaises.',NULL),(4,6,'Café Teranga','commercant',NULL,'Café-restaurant au cœur de Saint-Louis, nous recrutons régulièrement des étudiants pour le service et l\'accueil.',NULL),(5,7,'Cours Ibra','particulier',NULL,'Professeur particulier en mathématiques et physique, je recherche des étudiants pour donner des cours à domicile.',NULL);
/*!40000 ALTER TABLE `profils_recruteurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `role` enum('etudiant','recruteur','admin') NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `localisation` varchar(100) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `token_reset` varchar(255) DEFAULT NULL,
  `statut` enum('actif','inactif') DEFAULT 'actif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_utilisateurs_email` (`email`),
  KEY `idx_utilisateurs_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--

LOCK TABLES `utilisateurs` WRITE;
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
INSERT INTO `utilisateurs` VALUES (1,'sow','houleye','houleye.sow@etu.ucad.sn','$2y$10$4gmWATtmJph4iWD2/M7N7.khh.sHnL9XUZ/0mnT9zoET0yhNGXL/u','','etudiant',NULL,'dakar,senegal','2026-07-13 13:41:36',0,NULL,'actif','2026-06-12 23:11:22','2026-07-13 13:41:36'),(2,'Diallo','Amadou','recruteur@gmail.com','$2y$10$rjXQGEcMEYLcL80xEgSpeuawX7qLh4ixmGfhtIt1BfRVBtibvmP/C','774040765','recruteur',NULL,'dakar,senegal','2026-07-13 13:38:21',0,NULL,'actif','2026-06-12 23:17:24','2026-07-13 13:38:21'),(3,'Admin','SunuJob','admin@sunujob.sn','$2y$10$L8k5F3lTKl7Qa.V2cJGlx.dJ.Z7pKxhxsV1yOfiYPDoR0RvZQRi2i','+221770000000','admin',NULL,NULL,'2026-07-13 11:33:00',0,NULL,'actif','2026-06-13 20:54:12','2026-07-13 11:33:00'),(4,'Ndiaye','Fatou','contact@eventpro.sn','$2y$10$vfc6kC0ErTX4opNR9/Pf6Oo60w30bT1E8Hmypm/NBCTaM7ABasSpm','77 234 56 78','recruteur',NULL,'Dakar',NULL,0,NULL,'actif','2026-06-15 23:37:32','2026-07-11 22:08:45'),(5,'Kane','Moussa','rh@techsenegal.sn','$2y$10$vfc6kC0ErTX4opNR9/Pf6Oo60w30bT1E8Hmypm/NBCTaM7ABasSpm','76 345 67 89','recruteur',NULL,'Dakar',NULL,0,NULL,'actif','2026-06-15 23:37:32','2026-07-08 20:46:11'),(6,'Sy','Awa','awa.sy@cafeteranga.sn','$2y$10$vfc6kC0ErTX4opNR9/Pf6Oo60w30bT1E8Hmypm/NBCTaM7ABasSpm','78 456 78 90','recruteur',NULL,'Saint-Louis',NULL,0,NULL,'actif','2026-06-15 23:37:32','2026-07-08 20:43:38'),(7,'Fall','Ibrahima','ibrahima.fall@gmail.com','$2y$10$vfc6kC0ErTX4opNR9/Pf6Oo60w30bT1E8Hmypm/NBCTaM7ABasSpm','70 567 89 01','recruteur',NULL,'Rufisque',NULL,0,NULL,'actif','2026-06-15 23:37:32','2026-06-15 23:37:32'),(10,'Diop','Cheikh','cheikh.diop@etu.ucad.sn','$2y$10$vfc6kC0ErTX4opNR9/Pf6Oo60w30bT1E8Hmypm/NBCTaM7ABasSpm','77 111 22 33','etudiant',NULL,'Dakar',NULL,0,NULL,'actif','2026-06-15 23:37:32','2026-06-15 23:37:32'),(11,'Sow','Aminata','aminata.sow@ugb.sn','$2y$10$vfc6kC0ErTX4opNR9/Pf6Oo60w30bT1E8Hmypm/NBCTaM7ABasSpm','76 222 33 44','etudiant',NULL,'Saint-Louis',NULL,0,NULL,'actif','2026-06-15 23:37:32','2026-06-15 23:37:32'),(12,'Gueye','Ousmane','ousmane.gueye@esp.sn','$2y$10$vfc6kC0ErTX4opNR9/Pf6Oo60w30bT1E8Hmypm/NBCTaM7ABasSpm','78 333 44 55','etudiant',NULL,'Dakar',NULL,0,NULL,'actif','2026-06-15 23:37:32','2026-06-15 23:37:32'),(13,'Mbaye','Khady','khady.mbaye@etu.ucad.sn','$2y$10$vfc6kC0ErTX4opNR9/Pf6Oo60w30bT1E8Hmypm/NBCTaM7ABasSpm','70 444 55 66','etudiant',NULL,'Dakar',NULL,0,NULL,'actif','2026-06-15 23:37:32','2026-06-15 23:37:32'),(14,'Faye','Modou','modou.faye@uam.sn','$2y$10$vfc6kC0ErTX4opNR9/Pf6Oo60w30bT1E8Hmypm/NBCTaM7ABasSpm','75 555 66 77','etudiant',NULL,'Ziguinchor',NULL,0,NULL,'actif','2026-06-15 23:37:32','2026-06-15 23:37:32'),(15,'Niang','Rokhaya','rokha.niang@univ-thies.sn','$2y$10$vfc6kC0ErTX4opNR9/Pf6Oo60w30bT1E8Hmypm/NBCTaM7ABasSpm','77 666 77 88','etudiant',NULL,'Thiès',NULL,0,NULL,'actif','2026-06-15 23:37:32','2026-06-15 23:37:32'),(16,'Cissé','Mamadou','mamadou.cisse@etu.ucad.sn','$2y$10$vfc6kC0ErTX4opNR9/Pf6Oo60w30bT1E8Hmypm/NBCTaM7ABasSpm','76 777 88 99','etudiant',NULL,'Dakar',NULL,0,NULL,'actif','2026-06-15 23:37:32','2026-06-15 23:37:32');
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-13 15:22:02
