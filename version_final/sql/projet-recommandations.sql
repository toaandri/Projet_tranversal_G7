
CREATE DATABASE IF NOT EXISTS portail_recommandations;
USE portail_recommandations;

CREATE TABLE utilisateur (
    id_utilisateur VARCHAR(10) PRIMARY KEY,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('etudiant', 'admin', 'enseignant', 'rp') NOT NULL
);

CREATE TABLE etudiant (
    id_etudiant VARCHAR(10) PRIMARY KEY,
    mot_de_passe VARCHAR(255) NOT NULL,
    filiere VARCHAR(50) NOT NULL,
    semestre VARCHAR(10) NOT NULL,
    FOREIGN KEY (id_etudiant) REFERENCES utilisateur(id_utilisateur)
);

CREATE TABLE admin (
    id_admin VARCHAR(10) PRIMARY KEY,
    mot_de_passe VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_admin) REFERENCES utilisateur(id_utilisateur)
);

CREATE TABLE enseignant (
    id_enseignant VARCHAR(10) PRIMARY KEY,
    nom_enseignant VARCHAR(100) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_enseignant) REFERENCES utilisateur(id_utilisateur)
);

CREATE TABLE rp (
    id_rp VARCHAR(10) PRIMARY KEY,
    mot_de_passe VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_rp) REFERENCES utilisateur(id_utilisateur)
);

CREATE TABLE cours (
    id_cours VARCHAR(10) PRIMARY KEY,
    nom_cours VARCHAR(100) NOT NULL,
    id_enseignant VARCHAR(10) NOT NULL,
    filiere VARCHAR(50) NOT NULL,
    semestre VARCHAR(10) NOT NULL,
    horaire VARCHAR(50) NOT NULL,
    FOREIGN KEY (id_enseignant) REFERENCES enseignant(id_enseignant)
);

CREATE TABLE evaluation (
    id_evaluation INT AUTO_INCREMENT PRIMARY KEY,
    id_enseignant VARCHAR(10) NOT NULL,
    id_cours VARCHAR(10) NOT NULL,
    note DECIMAL(2,1) CHECK (note >= 0 AND note <= 5),
    commentaire TEXT,
    date_evaluation DATE NOT NULL,
    validee BOOLEAN DEFAULT 0,
    signale BOOLEAN DEFAULT 0,
    FOREIGN KEY (id_enseignant) REFERENCES enseignant(id_enseignant),
    FOREIGN KEY (id_cours) REFERENCES cours(id_cours)
);

CREATE TABLE suivi_cours (
    id_suivi INT AUTO_INCREMENT PRIMARY KEY,
    id_etudiant VARCHAR(10) NOT NULL,
    id_cours VARCHAR(10) NOT NULL,
    date_suivi DATE NOT NULL,
    FOREIGN KEY (id_etudiant) REFERENCES etudiant(id_etudiant),
    FOREIGN KEY (id_cours) REFERENCES cours(id_cours)
);

CREATE TABLE reunion (
    id_reunion INT AUTO_INCREMENT PRIMARY KEY,
    id_enseignant VARCHAR(10) NOT NULL,
    date_proposition DATE NOT NULL,
    message TEXT NOT NULL,
    statut ENUM('proposee', 'acceptee', 'refusee') DEFAULT 'proposee',
    FOREIGN KEY (id_enseignant) REFERENCES enseignant(id_enseignant)
);

INSERT INTO utilisateur (id_utilisateur, mot_de_passe, role) VALUES
('ETU001', 'etudiant123', 'etudiant'),
('ADM001', 'admin123', 'admin'),
('ENS001', 'enseignant123', 'enseignant'),
('RP001', 'rp123', 'rp');

INSERT INTO etudiant (id_etudiant, mot_de_passe, filiere, semestre) VALUES
('ETU001', 'etudiant123', 'Informatique', 'S1');

INSERT INTO admin (id_admin, mot_de_passe) VALUES
('ADM001', 'admin123');

INSERT INTO enseignant (id_enseignant, nom_enseignant, mot_de_passe) VALUES
('ENS001', 'Dupont', 'enseignant123');

INSERT INTO rp (id_rp, mot_de_passe) VALUES
('RP001', 'rp123');

INSERT INTO cours (id_cours, nom_cours, id_enseignant, filiere, semestre, horaire) VALUES
('CSI101', 'Algorithmique', 'ENS001', 'Informatique', 'S1', 'Lun 10h-12h');

INSERT INTO evaluation (id_enseignant, id_cours, note, commentaire, date_evaluation, validee) VALUES
('ENS001', 'CSI101', 4.5, 'Très bon cours!', '2025-07-01', 1);
