CREATE TABLE B_LISTS_PERMISSION
(
	IBLOCK_TYPE_ID varchar(50) NOT NULL,
	GROUP_ID int NOT NULL,
	CONSTRAINT PK_B_LISTS_PERMISSION PRIMARY KEY (IBLOCK_TYPE_ID, GROUP_ID)
)
GO
CREATE TABLE B_LISTS_FIELD
(
	IBLOCK_ID int not null,
	FIELD_ID varchar(50) not null,
	SORT int not null,
	NAME varchar(100) not null,
	SETTINGS text,
	CONSTRAINT PK_B_LISTS_FIELD PRIMARY KEY (IBLOCK_ID, FIELD_ID)
)
GO
CREATE TABLE B_LISTS_SOCNET_GROUP
(
	IBLOCK_ID int not null,
	SOCNET_ROLE char(1),
	PERMISSION char(1) not null
)
GO
CREATE UNIQUE INDEX UX_B_LISTS_SOCNET_GROUP_1 ON B_LISTS_SOCNET_GROUP (IBLOCK_ID, SOCNET_ROLE)
GO
CREATE TABLE B_LISTS_URL
(
	IBLOCK_ID int not null,
	URL varchar(500) not null,
	CONSTRAINT PK_B_LISTS_URL PRIMARY KEY (IBLOCK_ID)
)
GO