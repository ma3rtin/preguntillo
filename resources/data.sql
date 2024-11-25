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

CREATE TABLE categoria (
                           id INT PRIMARY KEY,
                           nombre VARCHAR(20),
                           color VARCHAR(10)
);

CREATE TABLE pregunta (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          pregunta TEXT NOT NULL,
                          estado VARCHAR(255) NOT NULL,
                          dificultad DOUBLE DEFAULT 0.5,
                          veces_entregada INT DEFAULT 0,
                          veces_acertada INT DEFAULT 0,
                          categoria_id INT,
                          FOREIGN KEY (categoria_id) REFERENCES categoria(id)
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
                         fecha DATE,
                         hora TIME,
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
                                   categoria VARCHAR(80)
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

INSERT INTO categoria(id,nombre,color) VALUES
                                           (1, 'HISTORIA', '#ffd700'),
                                           (2, 'MATEMÁTICAS', '#1cb0f6'),
                                           (3, 'ENTRETENIMIENTO', '#fd67c6'),
                                           (4, 'CIENCIAS', '#2ace98'),
                                           (5, 'ARTE', '#f85757'),
                                           (6, 'DEPORTE', '#ff971c');

INSERT INTO pregunta(pregunta,estado,categoria_id,veces_entregada,veces_acertada) VALUES
                                                                                      ('¿Cuál era una de las ciudades-estado más importantes de la antigua Grecia?', 'ACTIVA', 1, 0, 0),
                                                                                      ('¿Cuál es la capital de Italia?', 'ACTIVA', 1, 0, 0),
                                                                                      ('¿Cuál es la capital de Perú?', 'ACTIVA', 1, 0, 0),
                                                                                      ('2 + 2 = ?', 'ACTIVA', 2, 0, 0),
                                                                                      ('1 + 1 = ?', 'ACTIVA', 2, 0, 0),
                                                                                      ('3 + 3 = ?', 'ACTIVA', 2, 0, 0),
                                                                                      ('¿Quién ganó el Oscar a Mejor Actor en 2024?', 'ACTIVA', 3, 0, 0),
                                                                                      ('¿Cuántos hijos tiene Shrek?', 'ACTIVA', 3, 0, 0),
                                                                                      ('¿Cuál es el nombre del hijo de Anakin en Star Wars?', 'ACTIVA', 3, 0, 0),
                                                                                      ('¿Qué gas es más abundante en la atmósfera terrestre?', 'ACTIVA', 4, 0, 0),
                                                                                      ('¿Cuál es el planeta más grande del sistema solar?', 'ACTIVA', 4, 0, 0),
                                                                                      ('¿Qué órgano bombea sangre por todo el cuerpo?', 'ACTIVA', 4, 0, 0),
                                                                                      ('¿Quién pintó La Gioconda?', 'ACTIVA', 5, 0, 0),
                                                                                      ('¿Qué movimiento artístico lideró Salvador Dalí?', 'ACTIVA', 5, 0, 0),
                                                                                      ('¿En qué país se encuentra el Museo del Louvre?', 'ACTIVA', 5, 0, 0),
                                                                                      ('¿En qué deporte se utiliza un disco llamado puck?', 'ACTIVA', 6, 0, 0),
                                                                                      ('¿Cuántos jugadores tiene un equipo de fútbol en el campo?', 'ACTIVA', 6, 0, 0),
                                                                                      ('¿Qué país ha ganado más Copas del Mundo de Fútbol?', 'ACTIVA', 6, 0, 0);

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
                                                              (6, '6', 'SI'),
                                                              (6, '12', 'NO'),
                                                              (6, '9', 'NO'),

                                                              (7, 'Cillian Murphy', 'SI'),
                                                              (7, 'Austin Butler', 'NO'),
                                                              (7, 'Leonardo DiCaprio', 'NO'),
                                                              (7, 'Timothée Chalamet', 'NO'),

                                                              (8, '6', 'NO'),
                                                              (8, '5', 'NO'),
                                                              (8, '4', 'NO'),
                                                              (8, '3', 'SI'),

                                                              (9, 'Anakin Jr.', 'NO'),
                                                              (9, 'Luke Skywalker', 'SI'),
                                                              (9, 'Ben Solo', 'NO'),
                                                              (9, 'Han Solo', 'NO'),

                                                              (10, 'Oxígeno', 'NO'),
                                                              (10, 'Dióxido de carbono', 'NO'),
                                                              (10, 'Hidrógeno', 'NO'),
                                                              (10, 'Nitrógeno', 'SI'),

                                                              (11, 'Júpiter', 'SI'),
                                                              (11, 'Saturno', 'NO'),
                                                              (11, 'Urano', 'NO'),
                                                              (11, 'Neptuno', 'NO'),

                                                              (12, 'Hígado', 'NO'),
                                                              (12, 'Riñón', 'NO'),
                                                              (12, 'Pulmones', 'NO'),
                                                              (12, 'Corazón', 'SI'),

                                                              (13, 'Leonardo da Vinci', 'SI'),
                                                              (13, 'Pablo Picasso', 'NO'),
                                                              (13, 'Vincent van Gogh', 'NO'),
                                                              (13, 'Claude Monet', 'NO'),

                                                              (14, 'Cubismo', 'NO'),
                                                              (14, 'Impresionismo', 'NO'),
                                                              (14, 'Surrealismo', 'SI'),
                                                              (14, 'Futurismo', 'NO'),

                                                              (15, 'Italia', 'NO'),
                                                              (15, 'Francia', 'SI'),
                                                              (15, 'España', 'NO'),
                                                              (15, 'Alemania', 'NO'),

                                                              (16, 'Hockey sobre hielo', 'SI'),
                                                              (16, 'Béisbol', 'NO'),
                                                              (16, 'Tenis', 'NO'),
                                                              (16, 'Rugby', 'NO'),

                                                              (17, '9', 'NO'),
                                                              (17, '11', 'SI'),
                                                              (17, '13', 'NO'),
                                                              (17, '15', 'NO'),

                                                              (18, 'Alemania', 'NO'),
                                                              (18, 'Argentina', 'NO'),
                                                              (18, 'Brasil', 'SI'),
                                                              (18, 'Italia', 'NO');


INSERT INTO usuario (usuario, nombre, mail, contraseña, año_nac, foto, pais, genero, activo, latitud, longitud, fecha_creacion) values
                                                                                                                                    ('LiraDTA', 'Lira', 'lira@gmail.com', '123', 2003, 'lira.jpg', 'Uruguay', 'Femenino', 1, -34.609801928878525, -58.39413128051759, '2024-11-19'),
                                                                                                                                    ('Carlos123', 'Carlos', 'carlos123@gmail.com', '456', 1988, 'carlos.jpg', 'Argentina', 'Hombre', 1, -34.603722, -58.381592, '2024-11-18'),
                                                                                                                                    ('Ana_Rocha', 'Ana Rocha', 'ana.rocha@gmail.com', '789', 1995, 'ana.jpg', 'Brasil', 'Femenino', 1, -23.55052, -46.633308, '2024-11-17'),
                                                                                                                                    ('Julio_Fer', 'Julio Fernández', 'julio.fer@gmail.com', '321', 1990, 'julio.jpg', 'Chile', 'Hombre', 1, -33.44889, -70.669265, '2024-11-15'),
                                                                                                                                    ('Sofia2024', 'Sofia', 'sofia2024@gmail.com', '555', 2000, 'sofia.jpg', 'Perú', 'Femenino', 1, -12.046374, -77.042793, '2024-11-14'),
                                                                                                                                    ('JuanP1989', 'Juan Pablo', 'juanp1989@gmail.com', '1234', 1989, 'juanp.jpg', 'Colombia', 'Hombre', 1, 4.60971, -74.08175, '2024-11-13'),
                                                                                                                                    ('MartinezNoa', 'Noa Martínez', 'noa.martinez@gmail.com', '9876', 2005, 'noa.jpg', 'Argentina', 'Prefiero no cargarlo', 1, -34.601806, -58.380378, '2024-11-12'),
                                                                                                                                    ('LuisAlva', 'Luis Alvarez', 'luis.alvarez@gmail.com', '1122', 1992, 'luis.jpg', 'Chile', 'Hombre', 1, -33.44889, -70.669265, '2024-11-11'),
                                                                                                                                    ('Mara.Lopez', 'Mara López', 'mara.lopez@gmail.com', '3344', 1998, 'mara.jpg', 'México', 'Femenino', 1, 19.432608, -99.133209, '2024-11-10'),
                                                                                                                                    ('Emma_93', 'Emma Rodríguez', 'emma.rodriguez@gmail.com', '4455', 1993, 'emma.jpg', 'España', 'Femenino', 1, 40.416775, -3.70379, '2023-11-09'),
                                                                                                                                    ('CarlosM_2022', 'Carlos Morales', 'carlos.morales@gmail.com', '8899', 1994, 'carlosm.jpg', 'México', 'Hombre', 1, 19.432608, -99.133209, '2022-11-19');
