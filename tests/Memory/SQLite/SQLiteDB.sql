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
	`myboolean`		BOOLEAN,
	PRIMARY KEY(id)
);
CREATE TABLE `persistencetype` (
	`id`	INTEGER,
	`myint`	INTEGER,
	`myfloat`	REAL,
	`mytext`	TEXT,
	`mydatetime`	TEXT,
	`myboolean`		BOOLEAN,
	PRIMARY KEY(id)
);
CREATE TABLE `argumentstype` (
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
	`myboolean`	BOOLEAN,
	PRIMARY KEY(id)
);
CREATE TABLE `idtype` (
	`id`	INTEGER,
	`mytext`	TEXT,
	`reference`	INTEGER,
	PRIMARY KEY(id)
);
CREATE TABLE `myfetchtype` (
	`id`	INTEGER,
	`myint`	INTEGER,
	`myfloat`	REAL,
	`mytext`	TEXT,
	`mydatetime`	TEXT,
	`myothertype`	INTEGER,
	`mycircular`	INTEGER,
	`myboolean`	BOOLEAN,
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
	`myBoolean`	BOOLEAN,
	PRIMARY KEY(id)
);
CREATE TABLE `select` (
	`id`	INTEGER,
	`from`	INTEGER,
	`where` REAL,
	`order` TEXT,
	`by` DATETIME,
	`group` INTEGER,
	`drop` BOOLEAN,
	PRIMARY KEY(id)
);
CREATE TABLE `create` (
	`id`	INTEGER,
	`table`	INTEGER,
	`view` REAL,
	`values` TEXT,
	`as` DATETIME,
	PRIMARY KEY(id)
);
CREATE TABLE `collectiontype` (
	`id`	INTEGER,
	`someint` INTEGER,
	PRIMARY KEY(id)
);
CREATE TABLE `collectiontype_myints` (
	`owner`	INTEGER,
	`value` INTEGER,
	PRIMARY KEY(owner, value)
);
CREATE TABLE `collectiontype_myfloats` (
	`owner`	INTEGER,
	`value` REAL,
	PRIMARY KEY(owner, value)
);
CREATE TABLE `collectiontype_mytexts` (
	`owner`	INTEGER,
	`value` TEXT
);
CREATE TABLE `collectiontype_mydatetimes` (
	`owner`	INTEGER,
	`value` DATETIME,
	PRIMARY KEY(owner, value)
);
CREATE TABLE `collectiontype_myreferences` (
	`owner`	INTEGER,
	`value` INTEGER,
	PRIMARY KEY(owner, value)
);
CREATE TABLE `collectiontype_mybooleans` (
	`owner`	INTEGER,
	`value` BOOLEAN,
	PRIMARY KEY(owner, value)
);
CREATE TABLE `paginationtype` (
	`id`	INTEGER,
	`myint` INTEGER,
	`myreference` INTEGER,
	PRIMARY KEY(id)
);
CREATE TABLE `paginationtype_collectionofints` (
	`owner`	INTEGER,
	`value` INTEGER,
	PRIMARY KEY(owner, value)
);
CREATE TABLE `paginationtype_collectionofreferences` (
	`owner`	INTEGER,
	`value` INTEGER,
	PRIMARY KEY(owner, value)
);
CREATE TABLE `referencedbypagination` (
	`id`	INTEGER,
	`mytext` TEXT,
	PRIMARY KEY(id)
);
COMMIT;
