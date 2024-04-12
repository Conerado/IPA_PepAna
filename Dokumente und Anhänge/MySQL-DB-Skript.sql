DROP database IF EXISTS pepana;
CREATE DATABASE pepana;
USE pepana;

-- CREATE TABLES
CREATE TABLE dailyinformation (
date date PRIMARY KEY,
Information text, 
 UNIQUE KEY `date` (`date`)
);

CREATE TABLE users (
id int(1) PRIMARY KEY NOT NULL AUTO_INCREMENT,
username varchar(30),
password varbinary(60),
permissionLevel int(1) NOT NULL DEFAULT 3
);

-- INSERT TEMP ADMIN (DELETE ASAP AFTER CREATING REPLACEMENT)
-- Password: Passwort123

INSERT INTO users (username, password, permissionLevel) VALUES
('tempAdmin', '$2y$10$NbFJ.EqgNZmcK0M9jUc8EuKVtGgE36EmfV3TqS2T6/K7uwCj.4Zni', 1);