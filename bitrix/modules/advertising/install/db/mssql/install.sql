CREATE TABLE B_ADV_BANNER
(
	ID int NOT NULL IDENTITY (1, 1),
	CONTRACT_ID int NOT NULL,
	TYPE_SID varchar(255) NOT NULL,
	STATUS_SID varchar(255) NOT NULL,
	STATUS_COMMENTS varchar(500) NULL,
	NAME varchar(255) NULL,
	GROUP_SID varchar(255) NULL,
	FIRST_SITE_ID char(2) NULL,
	ACTIVE char(1) NOT NULL,
	WEIGHT int NOT NULL,
	MAX_SHOW_COUNT int NULL,
	SHOW_COUNT int NOT NULL,
	FIX_CLICK char(1) NOT NULL,
	FIX_SHOW CHAR(1) NOT NULL DEFAULT 'Y',
	MAX_CLICK_COUNT int NULL,
	CLICK_COUNT int NOT NULL,
	MAX_VISITOR_COUNT int NULL,
	VISITOR_COUNT int NOT NULL,
	SHOWS_FOR_VISITOR int NULL,
	DATE_LAST_SHOW datetime NULL,
	DATE_LAST_CLICK datetime NULL,
	DATE_SHOW_FROM datetime NULL,
	DATE_SHOW_TO datetime NULL,
	IMAGE_ID int NULL,
	IMAGE_ALT varchar(255) NULL,
	URL varchar(8000) NULL,
	URL_TARGET varchar(255) NULL,
	CODE text NULL,
	CODE_TYPE varchar(5) NOT NULL,
	STAT_EVENT_1 varchar(255) NULL,
	STAT_EVENT_2 varchar(255) NULL,
	STAT_EVENT_3 varchar(255) NULL,
	FOR_NEW_GUEST char(1) NULL,
	KEYWORDS varchar(1000) NULL,
	COMMENTS varchar(1000) NULL,
	DATE_CREATE datetime NULL,
	CREATED_BY int NULL,
	DATE_MODIFY datetime NULL,
	MODIFIED_BY int NULL,
	SHOW_USER_GROUP char(1) NOT NULL,
	NO_URL_IN_FLASH char(1) NOT NULL,
	FLYUNIFORM CHAR( 1 ) NOT NULL DEFAULT 'N',
	DATE_SHOW_FIRST DATETIME NULL,
	AD_TYPE VARCHAR( 20 ),
	FLASH_TRANSPARENT VARCHAR( 20 ),
	FLASH_IMAGE int,
	FLASH_JS CHAR( 1 ) NOT NULL DEFAULT 'N',
	FLASH_VER VARCHAR( 20 ),
	STAT_TYPE varchar(20),
	STAT_COUNT int
)
GO
ALTER TABLE B_ADV_BANNER ADD CONSTRAINT PK_B_ADV_BANNER PRIMARY KEY (ID)
GO
ALTER TABLE B_ADV_BANNER ADD CONSTRAINT DF_B_ADV_BANNER_CONTRACT_ID DEFAULT '1' FOR CONTRACT_ID
GO
ALTER TABLE B_ADV_BANNER ADD CONSTRAINT DF_B_ADV_BANNER_STATUS_SID DEFAULT 'PUBLISHED' FOR STATUS_SID
GO
ALTER TABLE B_ADV_BANNER ADD CONSTRAINT DF_B_ADV_BANNER_ACTIVE DEFAULT 'Y' FOR ACTIVE
GO
ALTER TABLE B_ADV_BANNER ADD CONSTRAINT DF_B_ADV_BANNER_WEIGHT DEFAULT '100' FOR WEIGHT
GO
ALTER TABLE B_ADV_BANNER ADD CONSTRAINT DF_B_ADV_BANNER_SHOW_COUNT DEFAULT '0' FOR SHOW_COUNT
GO
ALTER TABLE B_ADV_BANNER ADD CONSTRAINT DF_B_ADV_BANNER_FIX_CLICK DEFAULT 'Y' FOR FIX_CLICK
GO
ALTER TABLE B_ADV_BANNER ADD CONSTRAINT DF_B_ADV_BANNER_CLICK_COUNT DEFAULT '0' FOR CLICK_COUNT
GO
ALTER TABLE B_ADV_BANNER ADD CONSTRAINT DF_B_ADV_BANNER_VISITOR_COUNT DEFAULT '0' FOR VISITOR_COUNT
GO
ALTER TABLE B_ADV_BANNER ADD CONSTRAINT DF_B_ADV_BANNER_CODE_TYPE DEFAULT 'html' FOR CODE_TYPE
GO
ALTER TABLE B_ADV_BANNER ADD CONSTRAINT DF_B_ADV_BANNER_DATE_CREATE DEFAULT GETDATE() FOR DATE_CREATE
GO
ALTER TABLE B_ADV_BANNER ADD CONSTRAINT DF_B_ADV_BANNER_SHOW_USER_GROUP DEFAULT 'N' FOR SHOW_USER_GROUP
GO
ALTER TABLE B_ADV_BANNER ADD CONSTRAINT DF_B_ADV_BANNER_NO_URL_IN_FLASH DEFAULT 'N' FOR NO_URL_IN_FLASH
GO
CREATE INDEX IX_B_ADV_BANNER_ACTIVE_TYPE_SID ON B_ADV_BANNER (ACTIVE, TYPE_SID)
GO
CREATE INDEX IX_B_ADV_BANNER_CONTRACT_ID_TYPE_SID ON B_ADV_BANNER (CONTRACT_ID, TYPE_SID)
GO


CREATE TABLE B_ADV_BANNER_2_COUNTRY
(
	BANNER_ID int NOT NULL,
	COUNTRY_ID char(2) NOT NULL,
	REGION varchar(200),
	CITY_ID int
)
GO
ALTER TABLE B_ADV_BANNER_2_COUNTRY ADD CONSTRAINT DF_B_ADV_BANNER_2_COUNTRY_BANNER_ID DEFAULT '0' FOR BANNER_ID
GO
create index IX_B_ADV_BANNER_2_COUNTRY_1 on B_ADV_BANNER_2_COUNTRY (COUNTRY_ID, REGION, BANNER_ID)
GO
create index IX_B_ADV_BANNER_2_COUNTRY_2 on B_ADV_BANNER_2_COUNTRY (CITY_ID, BANNER_ID)
GO
create index IX_B_ADV_BANNER_2_COUNTRY_3 on B_ADV_BANNER_2_COUNTRY (BANNER_ID)
GO


CREATE TABLE B_ADV_BANNER_2_DAY
(
	DATE_STAT datetime NOT NULL,
	BANNER_ID int NOT NULL,
	SHOW_COUNT int NOT NULL,
	CLICK_COUNT int NOT NULL,
	VISITOR_COUNT int NOT NULL
)
GO
ALTER TABLE B_ADV_BANNER_2_DAY ADD CONSTRAINT PK_B_ADV_BANNER_2_DAY PRIMARY KEY (BANNER_ID, DATE_STAT)
GO
ALTER TABLE B_ADV_BANNER_2_DAY ADD CONSTRAINT DF_B_ADV_BANNER_2_DAY_DATE_STAT DEFAULT GETDATE() FOR DATE_STAT
GO
ALTER TABLE B_ADV_BANNER_2_DAY ADD CONSTRAINT DF_B_ADV_BANNER_2_DAY_BANNER_ID DEFAULT '0' FOR BANNER_ID
GO
ALTER TABLE B_ADV_BANNER_2_DAY ADD CONSTRAINT DF_B_ADV_BANNER_2_DAY_SHOW_COUNT DEFAULT '0' FOR SHOW_COUNT
GO
ALTER TABLE B_ADV_BANNER_2_DAY ADD CONSTRAINT DF_B_ADV_BANNER_2_DAY_CLICK_COUNT DEFAULT '0' FOR CLICK_COUNT
GO
ALTER TABLE B_ADV_BANNER_2_DAY ADD CONSTRAINT DF_B_ADV_BANNER_2_DAY_VISITOR_COUNT DEFAULT '0' FOR VISITOR_COUNT
GO


CREATE TABLE B_ADV_BANNER_2_SITE
(
	BANNER_ID int NOT NULL,
	SITE_ID char(2) NOT NULL
)
GO
ALTER TABLE B_ADV_BANNER_2_SITE ADD CONSTRAINT PK_B_ADV_BANNER_2_SITE PRIMARY KEY (BANNER_ID, SITE_ID)
GO
ALTER TABLE B_ADV_BANNER_2_SITE ADD CONSTRAINT DF_B_ADV_BANNER_2_SITE_BANNER_ID DEFAULT '0' FOR BANNER_ID
GO


CREATE TABLE B_ADV_BANNER_2_PAGE
(
	ID int NOT NULL IDENTITY (1, 1),
	BANNER_ID int NOT NULL,
	PAGE varchar(255) NOT NULL,
	SHOW_ON_PAGE char(1) NOT NULL
)
GO
ALTER TABLE B_ADV_BANNER_2_PAGE ADD CONSTRAINT PK_B_ADV_BANNER_2_PAGE PRIMARY KEY (ID)
GO
ALTER TABLE B_ADV_BANNER_2_PAGE ADD CONSTRAINT DF_B_ADV_BANNER_2_PAGE_BANNER_ID DEFAULT '0' FOR BANNER_ID
GO
ALTER TABLE B_ADV_BANNER_2_PAGE ADD CONSTRAINT DF_B_ADV_BANNER_2_PAGE_SHOW_ON_PAGE DEFAULT 'Y' FOR SHOW_ON_PAGE
GO
CREATE INDEX IX_B_ADV_BANNER_2_PAGE_BANNER_ID ON B_ADV_BANNER_2_PAGE (BANNER_ID)
GO


CREATE TABLE B_ADV_BANNER_2_STAT_ADV
(
	BANNER_ID int NOT NULL,
	STAT_ADV_ID int NOT NULL
)
GO
ALTER TABLE B_ADV_BANNER_2_STAT_ADV ADD CONSTRAINT PK_B_ADV_BANNER_2_STAT_ADV PRIMARY KEY (BANNER_ID, STAT_ADV_ID)
GO
ALTER TABLE B_ADV_BANNER_2_STAT_ADV ADD CONSTRAINT DF_B_ADV_BANNER_2_STAT_ADV_BANNER_ID DEFAULT '0' FOR BANNER_ID
GO
ALTER TABLE B_ADV_BANNER_2_STAT_ADV ADD CONSTRAINT DF_B_ADV_BANNER_2_STAT_ADV_STAT_ADV_ID DEFAULT '0' FOR STAT_ADV_ID
GO


CREATE TABLE B_ADV_BANNER_2_WEEKDAY
(
	BANNER_ID int NOT NULL,
	C_WEEKDAY varchar(10) NOT NULL,
	C_HOUR int NOT NULL,
)
GO
ALTER TABLE B_ADV_BANNER_2_WEEKDAY ADD CONSTRAINT PK_B_ADV_BANNER_2_WEEKDAY PRIMARY KEY (BANNER_ID, C_WEEKDAY, C_HOUR)
GO
ALTER TABLE B_ADV_BANNER_2_WEEKDAY ADD CONSTRAINT DF_B_ADV_BANNER_2_WEEKDAY_BANNER_ID DEFAULT '0' FOR BANNER_ID
GO
ALTER TABLE B_ADV_BANNER_2_WEEKDAY ADD CONSTRAINT DF_B_ADV_BANNER_2_WEEKDAY_C_HOUR DEFAULT '0' FOR C_HOUR
GO


CREATE TABLE B_ADV_CONTRACT
(
	ID int NOT NULL IDENTITY (1, 1),
	ACTIVE char(1) NOT NULL,
	NAME varchar(255) NULL,
	DESCRIPTION varchar(2000) NULL,
	KEYWORDS varchar(1000) NULL,
	ADMIN_COMMENTS varchar(500) NULL,
	WEIGHT int NOT NULL,
	SORT int NULL,
	MAX_SHOW_COUNT int NULL,
	SHOW_COUNT int NOT NULL,
	MAX_CLICK_COUNT int NULL,
	CLICK_COUNT int NOT NULL,
	MAX_VISITOR_COUNT int NULL,
	VISITOR_COUNT int NOT NULL,
	DATE_SHOW_FROM datetime NULL,
	DATE_SHOW_TO datetime NULL,
	DEFAULT_STATUS_SID varchar(255) NOT NULL,
	EMAIL_COUNT int NOT NULL,
	DATE_CREATE datetime NULL,
	CREATED_BY int NULL,
	DATE_MODIFY datetime NULL,
	MODIFIED_BY int NULL
)
GO
ALTER TABLE B_ADV_CONTRACT ADD CONSTRAINT PK_B_ADV_CONTRACT PRIMARY KEY (ID)
GO
ALTER TABLE B_ADV_CONTRACT ADD CONSTRAINT DF_B_ADV_CONTRACT_ACTIVE DEFAULT 'Y' FOR ACTIVE
GO
ALTER TABLE B_ADV_CONTRACT ADD CONSTRAINT DF_B_ADV_CONTRACT_WEIGHT DEFAULT '100' FOR WEIGHT
GO
ALTER TABLE B_ADV_CONTRACT ADD CONSTRAINT DF_B_ADV_CONTRACT_SHOW_COUNT DEFAULT '0' FOR SHOW_COUNT
GO
ALTER TABLE B_ADV_CONTRACT ADD CONSTRAINT DF_B_ADV_CONTRACT_CLICK_COUNT DEFAULT '0' FOR CLICK_COUNT
GO
ALTER TABLE B_ADV_CONTRACT ADD CONSTRAINT DF_B_ADV_CONTRACT_VISITOR_COUNT DEFAULT '0' FOR VISITOR_COUNT
GO
ALTER TABLE B_ADV_CONTRACT ADD CONSTRAINT DF_B_ADV_CONTRACT_DEFAULT_STATUS_SID DEFAULT 'PUBLISHED' FOR DEFAULT_STATUS_SID
GO
ALTER TABLE B_ADV_CONTRACT ADD CONSTRAINT DF_B_ADV_CONTRACT_EMAIL_COUNT DEFAULT '0' FOR EMAIL_COUNT
GO
ALTER TABLE B_ADV_CONTRACT ADD CONSTRAINT DF_B_ADV_CONTRACT_DATE_CREATE DEFAULT GETDATE() FOR DATE_CREATE
GO


CREATE TABLE B_ADV_CONTRACT_2_SITE
(
	CONTRACT_ID int NOT NULL,
	SITE_ID char(2) NOT NULL
)
GO
ALTER TABLE B_ADV_CONTRACT_2_SITE ADD CONSTRAINT PK_B_ADV_CONTRACT_2_SITE PRIMARY KEY (CONTRACT_ID, SITE_ID)
GO
ALTER TABLE B_ADV_CONTRACT_2_SITE ADD CONSTRAINT DF_B_ADV_CONTRACT_2_SITE_CONTRACT_ID DEFAULT '0' FOR CONTRACT_ID
GO


CREATE TABLE B_ADV_CONTRACT_2_PAGE
(
	ID int NOT NULL IDENTITY (1, 1),
	CONTRACT_ID int NOT NULL,
	PAGE varchar(255) NOT NULL,
	SHOW_ON_PAGE char(1) NOT NULL
)
GO
ALTER TABLE B_ADV_CONTRACT_2_PAGE ADD CONSTRAINT PK_B_ADV_CONTRACT_2_PAGE PRIMARY KEY (ID)
GO
ALTER TABLE B_ADV_CONTRACT_2_PAGE ADD CONSTRAINT DF_B_ADV_CONTRACT_2_PAGE_CONTRACT_ID DEFAULT '0' FOR CONTRACT_ID
GO
ALTER TABLE B_ADV_CONTRACT_2_PAGE ADD CONSTRAINT DF_B_ADV_CONTRACT_2_PAGE_SHOW_ON_PAGE DEFAULT 'Y' FOR SHOW_ON_PAGE
GO
CREATE INDEX IX_B_ADV_CONTRACT_2_PAGE_CONTRACT_ID ON B_ADV_CONTRACT_2_PAGE (CONTRACT_ID)
GO


CREATE TABLE B_ADV_CONTRACT_2_TYPE
(
	CONTRACT_ID int NOT NULL,
	TYPE_SID varchar(255) NOT NULL
)
GO
ALTER TABLE B_ADV_CONTRACT_2_TYPE ADD CONSTRAINT PK_B_ADV_CONTRACT_2_TYPE PRIMARY KEY (CONTRACT_ID, TYPE_SID)
GO
ALTER TABLE B_ADV_CONTRACT_2_TYPE ADD CONSTRAINT DF_B_ADV_CONTRACT_2_TYPE_CONTRACT_ID DEFAULT '0' FOR CONTRACT_ID
GO


CREATE TABLE B_ADV_CONTRACT_2_USER
(
	ID int NOT NULL IDENTITY (1, 1),
	CONTRACT_ID int NOT NULL,
	USER_ID int NOT NULL,
	PERMISSION varchar(255) NOT NULL
)
GO
ALTER TABLE B_ADV_CONTRACT_2_USER ADD CONSTRAINT PK_B_ADV_CONTRACT_2_USER PRIMARY KEY (ID)
GO
ALTER TABLE B_ADV_CONTRACT_2_USER ADD CONSTRAINT DF_B_ADV_CONTRACT_2_USER_CONTRACT_ID DEFAULT '0' FOR CONTRACT_ID
GO
ALTER TABLE B_ADV_CONTRACT_2_USER ADD CONSTRAINT DF_B_ADV_CONTRACT_2_USER_USER_ID DEFAULT '1' FOR USER_ID
GO
CREATE INDEX IX_B_ADV_CONTRACT_2_USER_CONTRACT_ID ON B_ADV_CONTRACT_2_USER (CONTRACT_ID)
GO


CREATE TABLE B_ADV_CONTRACT_2_WEEKDAY
(
	CONTRACT_ID int NOT NULL,
	C_WEEKDAY varchar(10) NOT NULL,
	C_HOUR int NOT NULL
)
GO
ALTER TABLE B_ADV_CONTRACT_2_WEEKDAY ADD CONSTRAINT PK_B_ADV_CONTRACT_2_WEEKDAY PRIMARY KEY (CONTRACT_ID, C_WEEKDAY, C_HOUR)
GO
ALTER TABLE B_ADV_CONTRACT_2_WEEKDAY ADD CONSTRAINT DF_B_ADV_CONTRACT_2_WEEKDAY_CONTRACT_ID DEFAULT '0' FOR CONTRACT_ID
GO
ALTER TABLE B_ADV_CONTRACT_2_WEEKDAY ADD CONSTRAINT DF_B_ADV_CONTRACT_2_WEEKDAY_C_HOUR DEFAULT '0' FOR C_HOUR
GO


CREATE TABLE B_ADV_TYPE
(
	SID varchar(255) NOT NULL,
	ACTIVE char(1) NOT NULL,
	SORT int NOT NULL,
	NAME varchar(255) NULL,
	DESCRIPTION varchar(500) NULL,
	DATE_CREATE datetime NULL,
	CREATED_BY int NULL,
	DATE_MODIFY datetime NULL,
	MODIFIED_BY int NULL
)
GO
ALTER TABLE B_ADV_TYPE ADD CONSTRAINT PK_B_ADV_TYPE PRIMARY KEY (SID)
GO
ALTER TABLE B_ADV_TYPE ADD CONSTRAINT DF_B_ADV_TYPE_ACTIVE DEFAULT 'Y' FOR ACTIVE
GO
ALTER TABLE B_ADV_TYPE ADD CONSTRAINT DF_B_ADV_TYPE_SORT DEFAULT '100' FOR SORT
GO
ALTER TABLE B_ADV_TYPE ADD CONSTRAINT DF_B_ADV_TYPE_DATE_CREATE DEFAULT GETDATE() FOR DATE_CREATE
GO
CREATE TABLE B_ADV_BANNER_2_GROUP
(
  	BANNER_ID int NOT NULL,
	GROUP_ID int NOT NULL
)
GO
ALTER TABLE B_ADV_BANNER_2_GROUP ADD CONSTRAINT PK_B_ADV_BANNER_2_GROUP PRIMARY KEY (BANNER_ID, GROUP_ID)
GO
ALTER TABLE B_ADV_BANNER_2_GROUP ADD CONSTRAINT DF_B_ADV_BANNER_2_GROUP_BANNER_ID DEFAULT '0' FOR BANNER_ID
GO
ALTER TABLE B_ADV_BANNER_2_GROUP ADD CONSTRAINT DF_B_ADV_BANNER_2_GROUP_GROUP_ID DEFAULT '0' FOR GROUP_ID
GO
