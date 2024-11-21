DROP DATABASE pw2;
CREATE DATABASE IF NOT EXISTS pw2;

USE pw2;

CREATE TABLE usuario (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         usuario VARCHAR(100) NOT NULL,
                         nombre VARCHAR(150),
                         mail VARCHAR(100) NOT NULL,
                         contraseña VARCHAR(100) NOT NULL,
                         año_nac INT,
                         foto VARCHAR(100),
                         pais VARCHAR(100),
                         genero VARCHAR(100),
                         activo BOOL,
                         latitud DOUBLE NOT NULL,
                         longitud DOUBLE NOT NULL,
                         nivel DOUBLE DEFAULT 0.5,
                         preguntas_recibidas INT(10) DEFAULT 0,
                         preguntas_acertadas INT(10) DEFAULT 0,
                         rol varchar(10) DEFAULT 'USER',
                         fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
                          dificultad DOUBLE DEFAULT 0.5,
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


INSERT INTO modulo(id,name) VALUES (1,'HISTORIA'), (2,'MATEMÁTICAS');
INSERT INTO tipo(id,name) VALUES (1,'Opciones con respuesta única');

INSERT INTO pregunta(pregunta,estado,id_modulo,veces_entregada,veces_acertada,id_tipo) VALUES
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


INSERT INTO usuario (usuario, nombre, mail, contraseña, año_nac, foto, pais, genero, activo, latitud, longitud, fecha_creacion) values
                                                                                                                                    ('LiraDTA', 'Lira', 'lira@gmail.com', '123', 2003, 'lira.jpg', 'Uruguay', 'MUJER', 1, -34.609801928878525, -58.39413128051759, '2024-11-19'),
                                                                                                                                    ('Carlos123', 'Carlos', 'carlos123@gmail.com', '456', 1988, 'carlos.jpg', 'Argentina', 'HOMBRE', 1, -34.603722, -58.381592, '2024-11-18'),
                                                                                                                                    ('Ana_Rocha', 'Ana Rocha', 'ana.rocha@gmail.com', '789', 1995, 'ana.jpg', 'Brasil', 'MUJER', 1, -23.55052, -46.633308, '2024-11-17'),
                                                                                                                                    ('Julio_Fer', 'Julio Fernández', 'julio.fer@gmail.com', '321', 1990, 'julio.jpg', 'Chile', 'HOMBRE', 1, -33.44889, -70.669265, '2024-11-15'),
                                                                                                                                    ('Sofia2024', 'Sofia', 'sofia2024@gmail.com', '555', 2000, 'sofia.jpg', 'Perú', 'MUJER', 1, -12.046374, -77.042793, '2024-11-14'),
                                                                                                                                    ('JuanP1989', 'Juan Pablo', 'juanp1989@gmail.com', '1234', 1989, 'juanp.jpg', 'Colombia', 'HOMBRE', 1, 4.60971, -74.08175, '2024-11-13'),
                                                                                                                                    ('MartinezNoa', 'Noa Martínez', 'noa.martinez@gmail.com', '9876', 2005, 'noa.jpg', 'Argentina', 'NO ESPECIFICA', 1, -34.601806, -58.380378, '2024-11-12'),
                                                                                                                                    ('LuisAlva', 'Luis Alvarez', 'luis.alvarez@gmail.com', '1122', 1992, 'luis.jpg', 'Chile', 'HOMBRE', 1, -33.44889, -70.669265, '2024-11-11'),
                                                                                                                                    ('Mara.Lopez', 'Mara López', 'mara.lopez@gmail.com', '3344', 1998, 'mara.jpg', 'México', 'MUJER', 1, 19.432608, -99.133209, '2024-11-10'),
                                                                                                                                    ('Emma_93', 'Emma Rodríguez', 'emma.rodriguez@gmail.com', '4455', 1993, 'emma.jpg', 'España', 'MUJER', 1, 40.416775, -3.70379, '2023-11-09'),
                                                                                                                                    ('CarlosM_2022', 'Carlos Morales', 'carlos.morales@gmail.com', '8899', 1994, 'carlosm.jpg', 'México', 'HOMBRE', 1, 19.432608, -99.133209, '2022-11-19');
