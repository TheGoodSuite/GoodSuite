BEGIN TRANSACTION;
CREATE TABLE `yetanothertype` (
	`id`	INTEGER,
	`yourint`	INTEGER,
	PRIMARY KEY(id)
);
CREATE TABLE `thirdtype` (
	`id`	INTEGER,
	`ref`	INTEGER,
	PRIMARY KEY(id)
);
CREATE TABLE `simpleupdatetype` (
	`id`	INTEGER,
	`myint`	INTEGER,
	`myfloat`	REAL,
	`mytext`	TEXT,
	`mydatetime`	TEXT,
	`myreference`	INTEGER,
	PRIMARY KEY(id)
);
CREATE TABLE `persistencetype` (
	`id`	INTEGER,
	`myint`	INTEGER,
	`myfloat`	REAL,
	`mytext`	TEXT,
	`mydatetime`	TEXT,
	PRIMARY KEY(id)
);
CREATE TABLE `parenttype2` (
	`id`	INTEGER,
	`yourint`	INTEGER,
	PRIMARY KEY(id)
);
CREATE TABLE `parenttype1` (
	`id`	INTEGER,
	`myint`	INTEGER,
	`myfloat`	REAL,
	`mytext`	TEXT,
	`mydatetime`	TEXT,
	`myothertype`	INTEGER,
	`mycircular`	INTEGER,
	PRIMARY KEY(id)
);
CREATE TABLE `othertype` (
	`id`	INTEGER,
	`yourint`	INTEGER,
	PRIMARY KEY(id)
);
CREATE TABLE `inserttype` (
	`id`	INTEGER,
	`myint`	INTEGER,
	`myfloat`	REAL,
	`mytext`	TEXT,
	`mydatetime`	TEXT,
	`mycircularreference`	INTEGER,
	PRIMARY KEY(id)
);
CREATE TABLE `idtype` (
	`id`	INTEGER,
	`mytext`	TEXT,
	`reference`	INTEGER,
	PRIMARY KEY(id)
);
CREATE TABLE `mygettype` (
	`id`	INTEGER,
	`myint`	INTEGER,
	`myfloat`	REAL,
	`mytext`	TEXT,
	`mydatetime`	TEXT,
	`myothertype`	INTEGER,
	`mycircular`	INTEGER,
	PRIMARY KEY(id)
);
CREATE TABLE `deletetype` (
	`id`	INTEGER,
	`myint`	INTEGER,
	`myfloat`	REAL,
	`mytext`	TEXT,
	`mydatetime`	TEXT,
	PRIMARY KEY(id)
);
CREATE TABLE "anothertype" (
	`id`	INTEGER,
	`yourint`	INTEGER,
	PRIMARY KEY(id)
);
CREATE TABLE "advancedupdatetype" (
	`id`	INTEGER,
	`myint`	INTEGER,
	`myfloat`	REAL,
	`mytext`	TEXT,
	`mydatetime`	TEXT,
	`myreference`	INTEGER,
	`ref`	INTEGER,
	PRIMARY KEY(id)
);
COMMIT;
