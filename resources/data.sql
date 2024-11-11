DROP DATABASE pw2;
CREATE DATABASE IF NOT EXISTS pw2;

USE pw2;

CREATE TABLE Usuario (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         usuario VARCHAR(100) NOT NULL,
                         nombre VARCHAR(150),
                         mail VARCHAR(100) NOT NULL,
                         contraseña VARCHAR(100) NOT NULL,
                         año_nac INT,
                         foto VARCHAR(100),
                         activo BOOL,
                         latitud DOUBLE NOT NULL,
                         longitud DOUBLE NOT NULL,
                         nivel DOUBLE DEFAULT 0.5,
                         preguntas_recibidas INT(10) DEFAULT 0,
                         preguntas_acertadas INT(10) DEFAULT 0,
                         rol varchar(10) DEFAULT 'USER'
);

create table token(
                      id int primary key auto_increment,
                      valor int,
                      usuario_id int,
                      foreign key(usuario_id) references usuario(id));

CREATE TABLE modulo (
                        id INT PRIMARY KEY,
                        name VARCHAR(255)
);

CREATE TABLE tipo (
                      id INT PRIMARY KEY,
                      name VARCHAR(255)
);

CREATE TABLE pregunta (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          pregunta TEXT NOT NULL,
                          estado VARCHAR(255) NOT NULL,
                          dificultad DOUBLE,
                          veces_entregada INT DEFAULT 0,
                          veces_acertada INT DEFAULT 0,
                          id_modulo INT,
                          id_tipo INT,
                          FOREIGN KEY (id_modulo) REFERENCES modulo(id),
                          FOREIGN KEY (id_tipo) REFERENCES tipo(id)
);

CREATE TABLE opcion(
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       pregunta_id INT,
                       opcion TEXT NOT NULL,
                       opcion_correcta VARCHAR(2) DEFAULT 'NO',
                       FOREIGN KEY (pregunta_id) REFERENCES pregunta(id) ON DELETE CASCADE
);

CREATE TABLE partida (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         usuario_id INT NOT NULL,
                         puntaje VARCHAR(255) NOT NULL,
                         fecha DATETIME,
                         FOREIGN KEY (usuario_id) REFERENCES usuario(id)
);

DROP TABLE IF EXISTS ranking;
CREATE TABLE ranking (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         usuario_id INT NOT NULL,
                         puntaje VARCHAR(255) NOT NULL,
                         FOREIGN KEY (usuario_id) REFERENCES usuario(id)
);

CREATE TABLE reporte_pregunta (
                                  id INT AUTO_INCREMENT PRIMARY KEY,
                                  usuario_id INT NOT NULL,
                                  pregunta_id INT NOT NULL,
                                  caso VARCHAR(120) DEFAULT NULL,
                                  mensaje VARCHAR(255) DEFAULT NULL,
                                  resuelto VARCHAR(80) DEFAULT 'NO',
                                  FOREIGN KEY (usuario_id) REFERENCES usuario(id),
                                  FOREIGN KEY (pregunta_id) REFERENCES pregunta(id)
);

CREATE TABLE pregunta_sugerida (
                                   id INT AUTO_INCREMENT PRIMARY KEY,
                                   pregunta TEXT NOT NULL,
                                   modulo VARCHAR(80),
                                   id_tipo INT,
                                   FOREIGN KEY (id_tipo) REFERENCES tipo(id)
);

CREATE TABLE opcion_sugerida (
                                 id INT AUTO_INCREMENT PRIMARY KEY,
                                 pregunta_id INT,
                                 opcion TEXT NOT NULL,
                                 opcion_correcta VARCHAR(2) DEFAULT 'NO',
                                 FOREIGN KEY (pregunta_id) REFERENCES pregunta_sugerida(id) ON DELETE CASCADE
);

create table usuario_pregunta(
                                 usuario_id int,
                                 pregunta_id int,
                                 constraint usuario_pregunta_fk primary key(pregunta_id,usuario_id),
                                 foreign key(pregunta_id) references pregunta(id),
                                 foreign key(usuario_id) references usuario(id));

INSERT INTO usuario (usuario, nombre, mail, contraseña, año_nac, foto, activo, latitud, longitud) values
    ('LiraDTA', 'Lira', 'lira@gmail.com', '123', 2003, 'lira.jpg', 1, -34.609801928878525, -58.39413128051759);

INSERT INTO modulo(id,name) VALUES (1,'HISTORIA'), (2,'MATEMÁTICAS');
INSERT INTO tipo(id,name) VALUES (1,'Opciones con respuesta única');

INSERT INTO pregunta(pregunta,estado,id_modulo,veces_enviada,veces_acertada,id_tipo) VALUES
                                                                                         ('¿Cuál era una de las ciudades-estado más importantes de la antigua Grecia?', 'ACTIVA', 1, 0, 0, 1),
                                                                                         ('¿Cuál es la capital de Italia?', 'ACTIVA', 1, 0, 0, 1),
                                                                                         ('¿Cuál es la capital de Perú?', 'ACTIVA', 1, 0, 0, 1),
                                                                                         ('2 + 2 = ?', 'ACTIVA', 2, 0, 0, 1),
                                                                                         ('1 + 1 = ?', 'ACTIVA', 2, 0, 0, 1),
                                                                                         ('3 + 3 = ?', 'ACTIVA', 2, 0, 0, 1);

INSERT INTO opcion (pregunta_id, opcion, opcion_correcta) VALUES
                                                              (1, 'Roma', 'NO'),
                                                              (1, 'Atenas', 'SI'),
                                                              (1, 'Esparta', 'NO'),
                                                              (1, 'Egipto', 'NO'),

                                                              (2, 'Roma', 'SI'),
                                                              (2, 'Atenas', 'NO'),
                                                              (2, 'Esparta', 'NO'),
                                                              (2, 'Egipto', 'NO'),

                                                              (3, 'Limón', 'NO'),
                                                              (3, 'Lemonchelo', 'NO'),
                                                              (3, 'Lima', 'SI'),
                                                              (3, 'Limo', 'NO'),

                                                              (4, '8', 'NO'),
                                                              (4, '2', 'NO'),
                                                              (4, '4', 'SI'),
                                                              (4, '6', 'NO'),

                                                              (5, '0', 'NO'),
                                                              (5, '3', 'NO'),
                                                              (5, '1', 'NO'),
                                                              (5, '2', 'SI'),

                                                              (6, '3', 'NO'),
                                                              (6, '6', 'si'),
                                                              (6, '12', 'no'),
                                                              (6, '9', 'NO');
