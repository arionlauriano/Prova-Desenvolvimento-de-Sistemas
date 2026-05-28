-- CRIAÇÃO E SELEÇÂO DO BANCO DE DADOS --
CREATE SCHEMA bd_shoreline;
USE bd_shoreline;

-- CRIAÇÃO DAS TABELAS --
-- USUÁRIOS --
CREATE TABLE usuarios (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR(100) NOT NULL,
	senha VARCHAR(32) NOT NULL
);

-- UNIDADES --
CREATE TABLE unidades (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR(100) NOT NULL,
	local VARCHAR(500) NOT NULL
);

-- AREAS --
CREATE TABLE areas (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR(100) NOT NULL,
	descricao VARCHAR(300) NOT NULL,
	cod_unid INT NOT NULL
);

-- ALAS --
CREATE TABLE alas (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR(50) NOT NULL,
	cod_unid INT NOT NULL
);

-- QUARTOS --
CREATE TABLE quartos (
	id INT AUTO_INCREMENT PRIMARY KEY,
	num VARCHAR(5) NOT NULL,
	cod_ala INT NOT NULL
);

-- RESERVAS --
CREATE TABLE (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR(200) NOT NULL,
	cpf VARCHAR(11) NOT NULL,
	qnt_adultos INT NOT NUL,
	qnt_crianças INT,
	data_checkin DATE NOT NULL,
	data_checkout DATE NOT NULL,
	cod_unid INT NOT NULL,
	cod_quart INT NOT NULL
);


-- CHAVES ESTRANGEIRAS --
-- AREAS --
ALTER TABLE areas 
ADD CONTRAINT fk_area_unid
FOREIGN KEY (cod_unid) REFERENCES unidades(id)
ON DELETE CASCADE;

-- ALAS --
ALTER TABLE alas
ADD CONSTRAINT fk_ala_unid
FOREIGN KEY (cod_unid) REFERENCES unidades(id)
ON DELETE CASCADE;

-- QUARTOS --
ALTER TABLE quartos
ADD CONSTRAINT fk_quarto_ala
FOREIGN KEY (cod_ala) REFERENCES alas(id)
ON DELETE CASCADE;

-- RESERVAS --
ALTER TABLE reservas
ADD CONSTRAINT fk_res_unid
FOREIGN KEY (cod_unid) REFERENCES unidades(id),
ADD CONSTRAINT fk_res_quart
FOREING KEY (cod_quart) REFERENCES quartos(id);


-- INSERTS TESTE --
-- UNIDADES --
INSERT INTO unidades (nome, local) VALUES 
('Resort Tropical Sol', 'Porto de Galinhas, PE'),
('Eco Hotel Vale Verde', 'Bonito, MS');

-- AREAS --
INSERT INTO areas (nome, descricao, cod_unid) VALUES 
-- Áreas da Unidade 1 --
('Piscina Infinita', 'Piscina aquecida com borda infinita e vista para o mar', 1),
('Quadra de Tênis', 'Quadra rápida de tênis com iluminação noturna', 1),
('Restaurante Maré', 'Gastronomia local especializada em frutos do mar', 1),
-- Áreas da Unidade 2 --
('Piscina Natural', 'Piscina integrada com águas correntes da região', 2),
('Arena de Beach Tennis', 'Quadra de areia para prática de esportes praianos', 2),
('Restaurante Ipê Roxo', 'Culinária contemporânea com ingredientes da fazenda', 2);

-- ALAS --
INSERT INTO alas (nome, cod_unid) VALUES 
-- Alas da Unidade 1 --
('Bromélia', 1),
('Orquídea', 1),
-- Alas da Unidade 2 --
('Manacá', 2),
('Jacarandá', 2);

-- QUARTOS --
INSERT INTO quartos (num, cod_ala) VALUES 
-- Quartos da Ala Bromélia (ID 1) --
('B1', 1), ('B2', 1), ('B3', 1), ('B4', 1), ('B5', 1),
-- Quartos da Ala Orquídea (ID 2) --
('O1', 2), ('O2', 2), ('O3', 2), ('O4', 2), ('O5', 2),
-- Quartos da Ala Manacá (ID 3) --
('M1', 3), ('M2', 3), ('M3', 3), ('M4', 3), ('M5', 3),
-- Quartos da Ala Jacarandá (ID 4) --
('J1', 4), ('J2', 4), ('J3', 4), ('J4', 4), ('J5', 4);