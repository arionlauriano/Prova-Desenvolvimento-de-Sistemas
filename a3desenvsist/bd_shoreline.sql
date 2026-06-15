DROP SCHEMA bd_shoreline;
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
	loc VARCHAR(500) NOT NULL
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
	cod_unid INT NOT NULL,
    vlr_diaria DECIMAL(10,2) NOT NULL
);

-- QUARTOS --
CREATE TABLE quartos (
	id INT AUTO_INCREMENT PRIMARY KEY,
	num VARCHAR(5) NOT NULL,
	cod_ala INT NOT NULL
);

-- SERVIÇOS --
CREATE TABLE servicos (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR(50) NOT NULL,
	descricao VARCHAR(500) NOT NULL,
	vlr_serv DECIMAL(10,2) NOT NULL
);

-- SOLICITAÇÂO DE SERVIÇOS --
CREATE TABLE soli_serv (
	id INT AUTO_INCREMENT PRIMARY KEY,
	cod_quart INT NOT NULL,
	cod_reserv INT NOT NULL,
    cod_serv INT NOT NULL, 
	status ENUM('Pendente', 'Feito') NOT NULL DEFAULT 'Pendente'
);

-- RESERVAS --
CREATE TABLE reservas(
	id INT AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR(200) NOT NULL,
	cpf VARCHAR(11) NOT NULL,
	qnt_adultos INT NOT NULL,
	qnt_criancas INT,
	data_checkin DATE NOT NULL,
	data_checkout DATE NOT NULL,
	vlr_reserv DECIMAL(10,2) NOT NULL,
	cod_unid INT NOT NULL,
	cod_quart INT NOT NULL,
	cod_usuario INT NOT NULL
);


-- CHAVES ESTRANGEIRAS --
-- AREAS --
ALTER TABLE areas 
ADD CONSTRAINT fk_area_unid
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
FOREIGN KEY (cod_quart) REFERENCES quartos(id),
ADD CONSTRAINT fk_res_usuario
FOREIGN KEY (cod_usuario) REFERENCES usuarios(id);

-- SOLICITAÇÃO DE SERVIÇOS --
ALTER TABLE soli_serv
ADD CONSTRAINT fk_soli_quart
FOREIGN KEY (cod_quart) REFERENCES quartos(id),
ADD CONSTRAINT fk_soli_reserv
FOREIGN KEY (cod_reserv) REFERENCES reservas(id),
ADD CONSTRAINT fk_soli_serv
FOREIGN KEY (cod_serv) REFERENCES servicos(id);

-- INSERTS TESTE --
-- USUARIOS --
INSERT INTO usuarios (nome, senha) VALUES
('admin', MD5('admin1234')),
('lucas_silva', MD5('senha123')),
('mariana_costa', MD5('senha456'));


-- UNIDADES --
INSERT INTO unidades (nome, loc) VALUES 
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
INSERT INTO alas (nome, cod_unid, vlr_diaria) VALUES 
-- Alas da Unidade 1 --
('Bromélia', 1, 250.00),
('Orquídea', 1, 500.00),
-- Alas da Unidade 2 --
('Manacá', 2, 250.00),
('Jacarandá', 2, 500.00);

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

-- SERVIÇOS --
INSERT INTO servicos (nome, descricao, vlr_serv) VALUES
('Café da Manhã Quarto', 'Serviço de entrega de café da manhã continental completo no quarto.', 45.00),
('Almoço Executivo', 'Prato principal com entrada e sobremesa no restaurante da unidade.', 79.90),
('Massagem Relaxante', 'Sessão de 50 minutos de massagem terapêutica no SPA do resort.', 150.00),
('Lavanderia Express', 'Lavagem e passação de até 5 peças de roupa com entrega no mesmo dia.', 35.00),
('Translado Aeroporto', 'Transporte privativo de ida ou volta para o aeroporto mais próximo.', 120.00);

-- RESERVAS --
INSERT INTO reservas (nome, cpf, qnt_adultos, qnt_criancas, data_checkin, data_checkout, vlr_reserv, cod_unid, cod_quart, cod_usuario) VALUES
('Carlos Alberto Vignoli', '11122233344', 2, 1, '2026-07-01', '2026-07-05', 1000.00, 1, 1, 2), 
('Ana Beatriz Rocha', '22233344455', 1, 0, '2026-07-10', '2026-07-12', 500.00, 1, 2, 2),  
('Marcos Paulo Souza', '33344455566', 2, 2, '2026-07-15', '2026-07-20', 1250.00, 1, 3, 3),
('Juliana Mendes Lima', '44455566677', 2, 0, '2026-08-01', '2026-08-03', 1000.00, 1, 6, 2),
('Roberto Carlos Faria', '55566677788', 3, 0, '2026-08-05', '2026-08-10', 2500.00, 1, 7, 3),
('Fernanda Costa Melo', '66677788899', 2, 1, '2026-07-02', '2026-07-05', 750.00, 2, 11, 2),
('Ricardo Oliveira Antunes', '77788899900', 1, 0, '2026-07-08', '2026-07-12', 1000.00, 2, 12, 3),
('Camila Rodrigues Dias', '88899900011', 2, 0, '2026-07-22', '2026-07-25', 750.00, 2, 13, 2), 
('Gabriel Almeida Santos', '99900011122', 4, 1, '2026-08-12', '2026-08-15', 1500.00, 2, 16, 3),
('Patricia Gomes Silva', '00011122233', 2, 0, '2026-08-18', '2026-08-22', 2000.00, 2, 17, 2);

-- SOLICITAÇÕES DE SERVIÇO --
INSERT INTO soli_serv (cod_quart, cod_reserv, cod_serv, status) VALUES
(1, 1, 1, 'Pendente'), 
(3, 3, 3, 'Pendente'); 