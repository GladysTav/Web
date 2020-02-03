-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Client :  127.0.0.1
-- Généré le :  Lun 18 Mars 2019 à 10:23
-- Version du serveur :  5.7.14
-- Version de PHP :  7.0.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `ppe4`
--

-- --------------------------------------------------------

--
-- Structure de la table `affectation`
--

CREATE TABLE `affectation` (
  `id_aff` int(11) NOT NULL,
  `dateaff` date NOT NULL,
  `Id_Utilisateur` int(11) NOT NULL,
  `id_region` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `affectation`
--

INSERT INTO `affectation` (`id_aff`, `dateaff`, `Id_Utilisateur`, `id_region`) VALUES
(1, '2019-01-07', 2, 14),
(2, '2019-03-20', 4, 10),
(3, '2019-03-03', 2, 13),
(4, '2019-02-11', 2, 3),
(5, '2018-11-18', 2, 17),
(6, '2018-07-16', 6, 14);

-- --------------------------------------------------------

--
-- Structure de la table `choix`
--

CREATE TABLE `choix` (
  `id_choix` int(11) NOT NULL,
  `Rang` int(11) NOT NULL,
  `Id_Utilisateur` int(11) NOT NULL,
  `id_region` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `choix`
--

INSERT INTO `choix` (`id_choix`, `Rang`, `Id_Utilisateur`, `id_region`) VALUES
(1, 2, 2, 11);

-- --------------------------------------------------------

--
-- Structure de la table `equipement`
--

CREATE TABLE `equipement` (
  `Id_Equipement` int(11) NOT NULL,
  `Equipement` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `equipement`
--

INSERT INTO `equipement` (`Id_Equipement`, `Equipement`) VALUES
(1, 'Logiciel'),
(2, 'Ordinateur portable'),
(3, 'Ordinateur fixe'),
(4, 'Imprimante'),
(5, 'Ecran'),
(6, 'Téléphone'),
(7, 'Mot de passe'),
(8, 'Connexion réseau'),
(9, 'Périphériques'),
(10, 'Autre');

-- --------------------------------------------------------

--
-- Structure de la table `etat`
--

CREATE TABLE `etat` (
  `Id_Etat` int(11) NOT NULL,
  `Etat` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `etat`
--

INSERT INTO `etat` (`Id_Etat`, `Etat`) VALUES
(1, 'Nouveau'),
(2, 'Work in progress'),
(3, 'En attente'),
(4, 'Fermé');

-- --------------------------------------------------------

--
-- Structure de la table `intervention`
--

CREATE TABLE `intervention` (
  `Id_Intervention` int(11) NOT NULL,
  `DateIntervention` date NOT NULL,
  `Message` varchar(500) NOT NULL,
  `Id_Ticket` int(11) NOT NULL,
  `Id_Utilisateur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `niveau`
--

CREATE TABLE `niveau` (
  `Id_Niveau` int(11) NOT NULL,
  `Niveau` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `niveau`
--

INSERT INTO `niveau` (`Id_Niveau`, `Niveau`) VALUES
(1, '1'),
(2, '2');

-- --------------------------------------------------------

--
-- Structure de la table `priorite`
--

CREATE TABLE `priorite` (
  `Id_Prio` int(11) NOT NULL,
  `Priorite` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `priorite`
--

INSERT INTO `priorite` (`Id_Prio`, `Priorite`) VALUES
(1, 'Critique'),
(2, 'Elevé'),
(3, 'Moyen'),
(4, 'Basse');

-- --------------------------------------------------------

--
-- Structure de la table `region`
--

CREATE TABLE `region` (
  `id_region` int(11) NOT NULL,
  `region` varchar(100) NOT NULL,
  `place_dispo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `region`
--

INSERT INTO `region` (`id_region`, `region`, `place_dispo`) VALUES
(1, 'Grand Est', 3),
(2, 'Nouvelle-Aquitaine', 4),
(3, 'Auvergne-Rhône-Alpes', 5),
(4, 'Bourgogne-Franche-Comté', 5),
(5, 'Bretagne', 4),
(6, 'Centre-Val de Loire', 2),
(7, 'Corse', 4),
(8, 'Île-de-France', 5),
(9, 'Occitanie', 6),
(10, 'Hauts-de-France', 2),
(11, 'Normandie', 6),
(12, 'Pays de la Loire', 6),
(13, 'Provence-Alpes-Côte d\'Azur', 5),
(14, 'Guadeloupe', 6),
(15, 'Martinique', 4),
(16, 'Guyane', 6),
(17, 'La Réunion', 0),
(18, 'Mayotte', 1);

-- --------------------------------------------------------

--
-- Structure de la table `statut`
--

CREATE TABLE `statut` (
  `Id_Statut` int(11) NOT NULL,
  `Statut` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `statut`
--

INSERT INTO `statut` (`Id_Statut`, `Statut`) VALUES
(1, 'Utilisateur'),
(2, 'technicien 1'),
(3, 'technicien 2'),
(4, 'RH');

-- --------------------------------------------------------

--
-- Structure de la table `ticket`
--

CREATE TABLE `ticket` (
  `Id_Ticket` int(11) NOT NULL,
  `Titre` varchar(100) NOT NULL,
  `Description` varchar(500) NOT NULL,
  `Technicien` varchar(10) DEFAULT NULL,
  `Duree` varchar(10) DEFAULT NULL,
  `DateDemande` date NOT NULL,
  `DateFinApproximative` date DEFAULT NULL,
  `DateCloture` date DEFAULT NULL,
  `Solution` varchar(10) DEFAULT NULL,
  `Id_Niveau` int(11) DEFAULT NULL,
  `Id_Equipement` int(11) NOT NULL,
  `Id_Prio` int(11) DEFAULT NULL,
  `Id_Etat` int(11) NOT NULL,
  `Id_Utilisateur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `ticket`
--

INSERT INTO `ticket` (`Id_Ticket`, `Titre`, `Description`, `Technicien`, `Duree`, `DateDemande`, `DateFinApproximative`, `DateCloture`, `Solution`, `Id_Niveau`, `Id_Equipement`, `Id_Prio`, `Id_Etat`, `Id_Utilisateur`) VALUES
(1, 'test', 'aaaaaaaaaaaaa aaaaaaaaaaa aaaaaaaaaaa', 'gladys', '1', '2018-11-01', '2018-11-02', '2018-11-02', 'aaaaaa', 2, 5, 1, 4, 2),
(2, 'test1', 'bbbbbbbbbbbbbbbbbb bbbbbb', 'maneth', '1', '2018-11-06', '2018-11-06', '2018-11-06', 'bbbb', 1, 4, 1, 4, 5),
(3, 'test2', 'ccccccc', 'matthieu', '3', '2018-10-16', '2018-10-18', '2018-10-18', 'cc', 2, 2, 3, 2, 3),
(4, 'test3', 'ddddddddddd', 'gladys', '7', '2018-11-01', '2018-11-09', '2018-11-09', 'dddd', 2, 1, 3, 2, 2),
(5, 'eeeeeeee', 'defrrgvzsdcqx', 'gladys', '2', '2018-11-09', '2018-11-12', NULL, NULL, 2, 8, 3, 2, 2),
(6, 'ffff', 'bvepoiodivjcspdc', NULL, NULL, '2018-11-11', NULL, NULL, NULL, NULL, 9, NULL, 1, 5);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `Id_Utilisateur` int(11) NOT NULL,
  `Login` varchar(50) NOT NULL,
  `Mdp` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Nom` varchar(50) NOT NULL,
  `Prenom` varchar(50) NOT NULL,
  `Id_Statut` int(11) NOT NULL,
  `DateEntree` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `utilisateur`
--

INSERT INTO `utilisateur` (`Id_Utilisateur`, `Login`, `Mdp`, `Email`, `Nom`, `Prenom`, `Id_Statut`, `DateEntree`) VALUES
(1, 'scoubymat', 'Matthieu1*', 'matthieutheffo@icloud.com', 'Theffo', 'Matthieu', 3, '2018-01-16'),
(2, 'Blandine', 'Blandine1*', 'blandine.danteuille@wanadoo.fr', 'Danteuille', 'Blandine', 1, '2016-09-17'),
(3, 'Gladys', 'Gladys1*', 'gladys.tavenaux@edu.itescia.fr', 'Tavenaux', 'Gladys', 4, '2018-03-19'),
(4, 'Maneth', 'Maneth1*', 'seng.maneth@edu.itescia.fr', 'Seng', 'Maneth', 2, '2017-06-14'),
(5, 'marianne', 'Marianne1*', 'm.dant@gsb.fr', 'Danteuille', 'Marianne', 1, '0000-00-00'),
(6, 'Lucie1', 'Lucie1**', 'l.b@gsb.fr', 'Bidon', 'Lucie', 1, '0000-00-00');

--
-- Index pour les tables exportées
--

--
-- Index pour la table `affectation`
--
ALTER TABLE `affectation`
  ADD PRIMARY KEY (`id_aff`),
  ADD KEY `Choix_Utilisateur_FK` (`Id_Utilisateur`),
  ADD KEY `fk_aff` (`id_region`);

--
-- Index pour la table `choix`
--
ALTER TABLE `choix`
  ADD PRIMARY KEY (`id_choix`),
  ADD KEY `Id_Utilisateur` (`Id_Utilisateur`),
  ADD KEY `fk_choix` (`id_region`);

--
-- Index pour la table `equipement`
--
ALTER TABLE `equipement`
  ADD PRIMARY KEY (`Id_Equipement`);

--
-- Index pour la table `etat`
--
ALTER TABLE `etat`
  ADD PRIMARY KEY (`Id_Etat`);

--
-- Index pour la table `intervention`
--
ALTER TABLE `intervention`
  ADD PRIMARY KEY (`Id_Intervention`),
  ADD KEY `Intervention_Ticket_FK` (`Id_Ticket`),
  ADD KEY `Intervention_Utilisateur0_FK` (`Id_Utilisateur`);

--
-- Index pour la table `niveau`
--
ALTER TABLE `niveau`
  ADD PRIMARY KEY (`Id_Niveau`);

--
-- Index pour la table `priorite`
--
ALTER TABLE `priorite`
  ADD PRIMARY KEY (`Id_Prio`);

--
-- Index pour la table `region`
--
ALTER TABLE `region`
  ADD PRIMARY KEY (`id_region`);

--
-- Index pour la table `statut`
--
ALTER TABLE `statut`
  ADD PRIMARY KEY (`Id_Statut`);

--
-- Index pour la table `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`Id_Ticket`),
  ADD KEY `Ticket_Niveau_FK` (`Id_Niveau`),
  ADD KEY `Ticket_Equipement0_FK` (`Id_Equipement`),
  ADD KEY `Ticket_Priorite1_FK` (`Id_Prio`),
  ADD KEY `Ticket_Etat2_FK` (`Id_Etat`),
  ADD KEY `Ticket_Utilisateur4_FK` (`Id_Utilisateur`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`Id_Utilisateur`),
  ADD KEY `Utilisateur_Statut_FK` (`Id_Statut`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `affectation`
--
ALTER TABLE `affectation`
  MODIFY `id_aff` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT pour la table `choix`
--
ALTER TABLE `choix`
  MODIFY `id_choix` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pour la table `intervention`
--
ALTER TABLE `intervention`
  MODIFY `Id_Intervention` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `region`
--
ALTER TABLE `region`
  MODIFY `id_region` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT pour la table `statut`
--
ALTER TABLE `statut`
  MODIFY `Id_Statut` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT pour la table `ticket`
--
ALTER TABLE `ticket`
  MODIFY `Id_Ticket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `Id_Utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `affectation`
--
ALTER TABLE `affectation`
  ADD CONSTRAINT `Affectation_Utilisateur_FK` FOREIGN KEY (`Id_Utilisateur`) REFERENCES `utilisateur` (`Id_Utilisateur`),
  ADD CONSTRAINT `fk_aff` FOREIGN KEY (`id_region`) REFERENCES `region` (`id_region`);

--
-- Contraintes pour la table `choix`
--
ALTER TABLE `choix`
  ADD CONSTRAINT `fk_choix` FOREIGN KEY (`id_region`) REFERENCES `region` (`id_region`);

--
-- Contraintes pour la table `intervention`
--
ALTER TABLE `intervention`
  ADD CONSTRAINT `Intervention_Ticket_FK` FOREIGN KEY (`Id_Ticket`) REFERENCES `ticket` (`Id_Ticket`),
  ADD CONSTRAINT `Intervention_Utilisateur0_FK` FOREIGN KEY (`Id_Utilisateur`) REFERENCES `utilisateur` (`Id_Utilisateur`);

--
-- Contraintes pour la table `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `Ticket_Equipement0_FK` FOREIGN KEY (`Id_Equipement`) REFERENCES `equipement` (`Id_Equipement`),
  ADD CONSTRAINT `Ticket_Etat2_FK` FOREIGN KEY (`Id_Etat`) REFERENCES `etat` (`Id_Etat`),
  ADD CONSTRAINT `Ticket_Niveau_FK` FOREIGN KEY (`Id_Niveau`) REFERENCES `niveau` (`Id_Niveau`),
  ADD CONSTRAINT `Ticket_Priorite1_FK` FOREIGN KEY (`Id_Prio`) REFERENCES `priorite` (`Id_Prio`),
  ADD CONSTRAINT `Ticket_Utilisateur4_FK` FOREIGN KEY (`Id_Utilisateur`) REFERENCES `utilisateur` (`Id_Utilisateur`);

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `Utilisateur_Statut_FK` FOREIGN KEY (`Id_Statut`) REFERENCES `statut` (`Id_Statut`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
