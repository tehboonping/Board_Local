CREATE DATABASE IF NOT EXISTS boarddata;
USE boarddata;

CREATE TABLE accounts(accountid INT AUTO_INCREMENT NOT NULL,
	user VARCHAR(255) UNIQUE,
	pass VARCHAR(255),
	username VARCHAR(255) UNIQUE,
	lv INT,
	PRIMARY KEY(accountid)
)DEFAULT CHARACTER SET=utf8;

CREATE TABLE datas(id INT AUTO_INCREMENT NOT NULL,
	accountid INT,
	name VARCHAR(255),
	message TEXT,
	posttime DATETIME,
	image VARCHAR(255),
	PRIMARY KEY(id)
)DEFAULT CHARACTER SET=utf8;

CREATE TABLE managers(id INT AUTO_INCREMENT NOT NULL,
	manager VARCHAR(255) UNIQUE,
	pass VARCHAR(255),
	name VARCHAR(255) UNIQUE,
	post_edit BOOLEAN,
	post_delete BOOLEAN,
	image_view BOOLEAN,
	image_delete BOOLEAN,
	maintenance_view BOOLEAN,
	maintenance_edit BOOLEAN,
	system BOOLEAN,
	PRIMARY KEY(id)
)DEFAULT CHARACTER SET=utf8;

CREATE TABLE maintenances(id INT AUTO_INCREMENT NOT NULL,
	starttime DATETIME,
	endtime DATETIME,
	comment TEXT,
	enable BOOLEAN,
	name VARCHAR(255),
	PRIMARY KEY(id)
)DEFAULT CHARACTER SET=utf8;