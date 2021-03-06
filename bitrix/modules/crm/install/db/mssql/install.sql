CREATE TABLE B_CRM_LEAD
(
	ID int IDENTITY(1,1) NOT NULL,
	DATE_CREATE datetime NULL,
	DATE_MODIFY datetime NULL,
	CREATED_BY_ID int NOT NULL,
	MODIFY_BY_ID int NULL,
	ASSIGNED_BY_ID int NULL,
	OPENED char(1) NULL,
	CONTACT_ID int NULL,	
	STATUS_ID varchar(50) NULL,
	PRODUCT_ID varchar(50) NULL,
	STATUS_DESCRIPTION text NULL,
	OPPORTUNITY decimal(18, 2) NULL,
	CURRENCY_ID varchar(50) NULL,
	OPPORTUNITY_ACCOUNT decimal(18, 2) NULL,
	ACCOUNT_CURRENCY_ID varchar(50) NULL,
	COMPANY_ID int NULL,
	SOURCE_ID varchar(50) NULL,
	SOURCE_DESCRIPTION text NULL,
	TITLE varchar(255) NULL,
	COMPANY_TITLE varchar(255) NULL,
	NAME varchar(50) NULL,
	LAST_NAME varchar(50) NULL,
	SECOND_NAME varchar(50) NULL,
	FULL_NAME varchar(100) NULL,
	POST varchar(255) NULL,
	ADDRESS text NULL,
	COMMENTS text NULL,
	EXCH_RATE decimal(18,2) DEFAULT 1,
	ORIGINATOR_ID varchar(255) NULL,
	ORIGIN_ID varchar(255) NULL
) 
GO
ALTER TABLE B_CRM_LEAD ADD CONSTRAINT PK_B_CRM_LEAD PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_CRM_CT_FULL_NAME ON B_CRM_LEAD (FULL_NAME)
GO
ALTER TABLE B_CRM_LEAD ADD CONSTRAINT DF_B_CRM_LEAD_DATE_CREATE DEFAULT GETDATE() FOR DATE_CREATE
GO
ALTER TABLE B_CRM_LEAD ADD CONSTRAINT DF_B_CRM_LEAD_DATE_MODIFY DEFAULT GETDATE() FOR DATE_MODIFY
GO
ALTER TABLE B_CRM_LEAD ADD CONSTRAINT DF_B_CRM_LEAD_OPENED DEFAULT 'N' FOR OPENED
GO
CREATE TABLE B_CRM_DEAL
(
	ID int IDENTITY(1,1) NOT NULL,
	DATE_CREATE datetime NULL,
	DATE_MODIFY datetime NULL,
	CREATED_BY_ID int NOT NULL,
	MODIFY_BY_ID int NULL,
	ASSIGNED_BY_ID int NULL,
	OPENED char(1) NULL,
	LEAD_ID int NULL,
	COMPANY_ID int NULL,
	CONTACT_ID int NULL,
	TITLE varchar(255) NULL,
	PRODUCT_ID varchar(50) NULL,
	STAGE_ID varchar(50) NULL,
	CLOSED char(1) NULL,
	TYPE_ID varchar(50) NULL,
	OPPORTUNITY decimal(18, 2) NULL,
	CURRENCY_ID varchar(50) NULL,
	OPPORTUNITY_ACCOUNT decimal(18, 2) NULL,
	ACCOUNT_CURRENCY_ID varchar(50) NULL,
	PROBABILITY smallint NULL,
	COMMENTS text NULL,
	BEGINDATE datetime NULL,
	CLOSEDATE datetime NULL,
	EVENT_DATE datetime NULL,
	EVENT_ID varchar(50) NULL,
	EVENT_DESCRIPTION text NULL,
	EXCH_RATE decimal(18,2) DEFAULT 1,
	ORIGINATOR_ID varchar(255) NULL,
	ORIGIN_ID varchar(255) NULL,
	ADDITIONAL_INFO text NULL
)
GO
ALTER TABLE B_CRM_DEAL ADD CONSTRAINT PK_B_CRM_DEAL PRIMARY KEY (ID)
GO
ALTER TABLE B_CRM_DEAL ADD CONSTRAINT DF_B_CRM_DEAL_DATE_CREATE DEFAULT GETDATE() FOR DATE_CREATE
GO
ALTER TABLE B_CRM_DEAL ADD CONSTRAINT DF_B_CRM_DEAL_DATE_MODIFY DEFAULT GETDATE() FOR DATE_MODIFY
GO
ALTER TABLE B_CRM_DEAL ADD CONSTRAINT DF_B_CRM_DEAL_CLOSED DEFAULT 'N' FOR CLOSED
GO
ALTER TABLE B_CRM_DEAL ADD CONSTRAINT DF_B_CRM_DEAL_OPENED DEFAULT 'N' FOR OPENED
GO
CREATE TABLE B_CRM_CONTACT
(
	ID int IDENTITY(1,1) NOT NULL,
	LEAD_ID int NULL,
	DATE_CREATE datetime NULL,
	DATE_MODIFY datetime NULL,
	CREATED_BY_ID int NOT NULL,
	MODIFY_BY_ID int NULL,
	ASSIGNED_BY_ID int NULL,
	OPENED char(1) NULL,
	COMPANY_ID int NULL,
	SOURCE_ID varchar(50) NULL,
	SOURCE_DESCRIPTION text NULL,
	FULL_NAME varchar(100) NULL,
	NAME varchar(50) NULL,
	LAST_NAME varchar(50) NULL,
	SECOND_NAME varchar(50) NULL,
	PHOTO varchar(10) NULL,
	BIRTHDATE datetime NULL,
	POST varchar(255) NULL,
	ADDRESS text NULL,
	COMMENTS text NULL,
	TYPE_ID varchar(50) NULL,	
	EXPORT char(1) NULL,
	ORIGINATOR_ID varchar(255) NULL,
	ORIGIN_ID varchar(255) NULL
) 
GO
ALTER TABLE B_CRM_CONTACT ADD CONSTRAINT PK_B_CRM_CONTACT PRIMARY KEY (ID)
GO
ALTER TABLE B_CRM_CONTACT ADD CONSTRAINT DF_B_CRM_CONTACT_EXPORT DEFAULT 'N' FOR EXPORT
GO
ALTER TABLE B_CRM_CONTACT ADD CONSTRAINT DF_B_CRM_CONTACT_DATE_CREATE DEFAULT GETDATE() FOR DATE_CREATE
GO
ALTER TABLE B_CRM_CONTACT ADD CONSTRAINT DF_B_CRM_CONTACT_DATE_MODIFY DEFAULT GETDATE() FOR DATE_MODIFY
GO
ALTER TABLE B_CRM_CONTACT ADD CONSTRAINT DF_B_CRM_CONTACT_OPENED DEFAULT 'N' FOR OPENED
GO
CREATE INDEX IX_B_CRM_CT_FULL_NAME ON B_CRM_CONTACT (FULL_NAME)
GO
CREATE INDEX IX_B_CRM_CT_LAST_NAME ON B_CRM_CONTACT (LAST_NAME)
GO
CREATE INDEX IX_B_CRM_CT_NAME ON B_CRM_CONTACT (NAME)
GO
CREATE TABLE B_CRM_COMPANY
(
	ID int IDENTITY(1,1) NOT NULL,
	LEAD_ID int NULL,
	DATE_CREATE datetime NULL,
	DATE_MODIFY datetime NULL,
	CREATED_BY_ID int NOT NULL,
	MODIFY_BY_ID int NULL,
	ASSIGNED_BY_ID int NULL,
	OPENED char(1) NULL,
	TITLE varchar(255) NULL,
	LOGO varchar(10) NULL,
	ADDRESS text NULL,
	ADDRESS_LEGAL text NULL,
	BANKING_DETAILS text NULL,
	COMMENTS text NULL,	
	COMPANY_TYPE varchar(50) NULL,
	INDUSTRY varchar(50) NULL,
	REVENUE varchar(255) NULL,
	EMPLOYEES varchar(50) NULL,
	CURRENCY_ID varchar(50) NULL,
	ORIGINATOR_ID varchar(255) NULL,
	ORIGIN_ID varchar(255) NULL
) 
GO
ALTER TABLE B_CRM_COMPANY ADD CONSTRAINT PK_B_CRM_COMPANY PRIMARY KEY (ID)
GO
ALTER TABLE B_CRM_COMPANY ADD CONSTRAINT DF_B_CRM_COMPANY_DATE_CREATE DEFAULT GETDATE() FOR DATE_CREATE
GO
ALTER TABLE B_CRM_COMPANY ADD CONSTRAINT DF_B_CRM_COMPANY_DATE_MODIFY DEFAULT GETDATE() FOR DATE_MODIFY
GO
ALTER TABLE B_CRM_COMPANY ADD CONSTRAINT DF_B_CRM_COMPANY_OPENED DEFAULT 'N' FOR OPENED
GO
CREATE INDEX IX_B_CRM_CY_TITLE ON B_CRM_COMPANY (TITLE)
GO
CREATE TABLE B_CRM_STATUS
(
	ID int IDENTITY(1,1) NOT NULL,
	ENTITY_ID varchar(50) NOT NULL,
	STATUS_ID varchar(50) NOT NULL,
	NAME varchar(100) NOT NULL,
	NAME_INIT varchar(100) NULL,
	SORT int NOT NULL,
	SYSTEM char NOT NULL
)
GO
ALTER TABLE B_CRM_STATUS ADD CONSTRAINT PK_B_CRM_STATUS PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_CRM_STATUS ON B_CRM_STATUS (STATUS_ID, ENTITY_ID)
GO

CREATE TABLE B_CRM_FIELD_MULTI
(
	ID int IDENTITY(1,1) NOT NULL,
	ENTITY_ID varchar(50) NOT NULL,
	ELEMENT_ID int NOT NULL,
	TYPE_ID varchar(50) NOT NULL,
	VALUE_TYPE varchar(50) NOT NULL,
	COMPLEX_ID varchar(100) NOT NULL,
	VALUE varchar(250) NOT NULL
)
GO
ALTER TABLE B_CRM_FIELD_MULTI ADD CONSTRAINT PK_B_CRM_FIELD_MULTI PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_CRM_FM_EE ON B_CRM_FIELD_MULTI (ENTITY_ID, ELEMENT_ID)
GO

CREATE TABLE B_CRM_EVENT
(
	ID int IDENTITY(1,1) NOT NULL,
	DATE_CREATE datetime NULL,
	CREATED_BY_ID int NULL,
	ASSIGNED_BY_ID int NULL,
	ENTITY_TYPE varchar(50) NULL,
	ENTITY_ID int NULL,
	ENTITY_FIELD varchar(255) NULL,
	EVENT_ID varchar(50) NULL,
	EVENT_NAME varchar(255) NOT NULL,
	EVENT_TEXT_1 text NULL,
	EVENT_TEXT_2 text NULL,
	EVENT_TYPE int NULL,
	FILES text NULL
)
GO
ALTER TABLE B_CRM_EVENT ADD CONSTRAINT PK_B_CRM_EVENT PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_CRM_EVENT ON B_CRM_EVENT (ENTITY_TYPE, ENTITY_ID)
GO
CREATE INDEX IX_B_CRM_EVENT_1 ON B_CRM_EVENT (DATE_CREATE)
GO

CREATE TABLE B_CRM_EVENT_RELATIONS
(
	ID int IDENTITY(1,1) NOT NULL,
	ASSIGNED_BY_ID int NULL,
	ENTITY_TYPE varchar(50) NULL,
	ENTITY_ID int NOT NULL,
	ENTITY_FIELD varchar(255) NULL,
	EVENT_ID int NULL
)
GO
ALTER TABLE B_CRM_EVENT_RELATIONS ADD CONSTRAINT PK_B_CRM_EVENT_RELATIONS PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_CRM_EVENT_REL ON B_CRM_EVENT_RELATIONS (ENTITY_TYPE, ENTITY_ID, ENTITY_FIELD)
GO
CREATE INDEX IX_EVENT_REL_1 ON B_CRM_EVENT_RELATIONS (EVENT_ID)
GO

CREATE TABLE B_CRM_ENTITY_LOCK
(
  ENTITY_ID int NOT NULL,
  ENTITY_TYPE VARCHAR (10) NOT NULL,
  DATE_LOCK datetime NULL,
  LOCKED_BY VARCHAR (32) NULL
)
GO
ALTER TABLE B_CRM_ENTITY_LOCK ADD CONSTRAINT PK_B_CRM_ENTITY_LOCK PRIMARY KEY (ENTITY_ID)
GO
CREATE TABLE B_CRM_ENTITY_PERMS
(
  ID INT NOT NULL IDENTITY(1,1),
  ENTITY VARCHAR(20) NOT NULL,
  ENTITY_ID INT NOT NULL,
  ATTR VARCHAR(30) NOT NULL
)
GO
ALTER TABLE B_CRM_ENTITY_PERMS ADD CONSTRAINT PK_B_CRM_ENTITY_PERMS PRIMARY KEY (ID)
GO
CREATE INDEX IX_ENTITY_ATTR ON B_CRM_ENTITY_PERMS (ENTITY, ENTITY_ID, ATTR)
GO
CREATE TABLE B_CRM_ROLE
(
  ID INT NOT NULL IDENTITY(1,1),
  NAME VARCHAR(255) NOT NULL
)
GO
ALTER TABLE B_CRM_ROLE ADD CONSTRAINT PK_B_CRM_ROLE PRIMARY KEY (ID)
GO
CREATE TABLE B_CRM_ROLE_PERMS
(
  ID INT NOT NULL IDENTITY(1,1),
  ROLE_ID INT NOT NULL,
  ENTITY VARCHAR(20) NOT NULL,
  FIELD VARCHAR(30),
  FIELD_VALUE VARCHAR(255),
  PERM_TYPE VARCHAR(20) NOT NULL,
  ATTR CHAR(1)
)
GO
ALTER TABLE B_CRM_ROLE_PERMS ADD CONSTRAINT PK_B_CRM_ROLE_PERMS PRIMARY KEY (ID)
GO
CREATE INDEX IX_ROLE_ID ON B_CRM_ROLE_PERMS (ROLE_ID)
GO
ALTER TABLE B_CRM_ROLE_PERMS ADD CONSTRAINT DF_B_CRM_ROLE_PERMS_FIELD DEFAULT '-' FOR FIELD
GO
ALTER TABLE B_CRM_ROLE_PERMS ADD CONSTRAINT DF_B_CRM_ROLE_PERMS_FIELD_VALUE DEFAULT NULL FOR FIELD_VALUE
GO
ALTER TABLE B_CRM_ROLE_PERMS ADD CONSTRAINT DF_B_CRM_ROLE_PERMS_ATTR DEFAULT '' FOR ATTR
GO
CREATE TABLE B_CRM_ROLE_RELATION
(
  ID INT NOT NULL IDENTITY(1,1),
  ROLE_ID INT NOT NULL,
  RELATION VARCHAR(100) NOT NULL
)
GO
ALTER TABLE B_CRM_ROLE_RELATION ADD CONSTRAINT PK_B_CRM_ROLE_RELATION PRIMARY KEY (ID)
GO
CREATE INDEX IX_ROLE_RELATION ON B_CRM_ROLE_RELATION (ROLE_ID, RELATION)
GO

IF OBJECT_ID(N'B_CRM_EXTERNAL_SALE', N'U') IS NULL
  CREATE TABLE B_CRM_EXTERNAL_SALE
  (
	  ID INT NOT NULL IDENTITY(1,1),
	  ACTIVE CHAR(1) NOT NULL DEFAULT 'Y',
	  DATE_CREATE DATETIME NOT NULL,
	  DATE_UPDATE DATETIME NOT NULL,
	  NAME VARCHAR(128) NULL,
	  SCHEME VARCHAR(5) NOT NULL DEFAULT 'http',
	  SERVER VARCHAR(192) NOT NULL,
	  PORT INT NOT NULL DEFAULT 80,
	  LOGIN VARCHAR(64) NOT NULL,
	  PASSWORD VARCHAR(128) NOT NULL,
	  MODIFICATION_LABEL INT NULL,
	  IMPORT_SIZE INT NULL,
	  IMPORT_PERIOD INT NULL,
	  IMPORT_PROBABILITY INT NULL,
	  IMPORT_RESPONSIBLE INT NULL,
	  IMPORT_PUBLIC CHAR(1) NULL,
	  IMPORT_PREFIX VARCHAR(128) NULL,
	  IMPORT_ERRORS INT NULL,
	  IMPORT_GROUP_ID INT NULL,
	  COOKIE TEXT NULL,
	  LAST_STATUS TEXT NULL,
	  LAST_STATUS_DATE DATETIME NULL,
	  CONSTRAINT PK_B_CRM_EXTERNAL_SALE PRIMARY KEY CLUSTERED(ID ASC)
  )
GO

IF OBJECT_ID(N'B_CRM_CATALOG', N'U') IS NULL
  CREATE TABLE B_CRM_CATALOG
  (
	ID INT NOT NULL,
	ORIGINATOR_ID VARCHAR(255) NULL,
	ORIGIN_ID VARCHAR(255) NULL,
	CONSTRAINT PK_B_CRM_CATALOG PRIMARY KEY CLUSTERED(ID ASC)
  )
GO

IF OBJECT_ID(N'B_CRM_PRODUCT', N'U') IS NULL
  CREATE TABLE B_CRM_PRODUCT
  (
	ID INT NOT NULL,
	CATALOG_ID INT NOT NULL,
	CURRENCY_ID VARCHAR(50) NOT NULL,
	PRICE DECIMAL(18,2) NOT NULL DEFAULT 0,
	ORIGINATOR_ID VARCHAR(255) NULL,
	ORIGIN_ID VARCHAR(255) NULL,
	CONSTRAINT PK_B_CRM_PRODUCT PRIMARY KEY CLUSTERED(ID ASC)
  )
GO

IF OBJECT_ID(N'B_CRM_PRODUCT_ROW', N'U') IS NULL
  CREATE TABLE B_CRM_PRODUCT_ROW
  (
	ID INT IDENTITY(1,1) NOT NULL,
	OWNER_ID INT NOT NULL,
	OWNER_TYPE CHAR(3) NOT NULL,
	PRODUCT_ID INT NOT NULL,
	PRICE DECIMAL(18,2) NOT NULL,
	PRICE_ACCOUNT DECIMAL(18,2) NOT NULL DEFAULT 0,
	QUANTITY INT NOT NULL,
	CONSTRAINT PK_B_CRM_PRODUCT_ROW PRIMARY KEY CLUSTERED(ID ASC)
  )
GO

IF OBJECT_ID(N'B_CRM_ACT', N'U') IS NULL
	CREATE TABLE B_CRM_ACT
	(
		ID INT IDENTITY(1,1) NOT NULL,
		TYPE_ID SMALLINT NOT NULL,
		OWNER_ID INT NOT NULL,
		OWNER_TYPE_ID SMALLINT NOT NULL,
		ASSOCIATED_ENTITY_ID INT,
		SUBJECT VARCHAR(512) NOT NULL,
		COMPLETED CHAR(1) NOT NULL DEFAULT 'N',
		RESPONSIBLE_ID INT NOT NULL,
		PRIORITY INT NOT NULL,
		NOTIFY_TYPE INT NOT NULL,
		NOTIFY_VALUE INT,
		DESCRIPTION VARCHAR(2048),
		DESCRIPTION_TYPE SMALLINT NULL,
		DIRECTION SMALLINT NOT NULL,
		LOCATION VARCHAR(256),
		CREATED DATETIME NOT NULL,
		LAST_UPDATED DATETIME NOT NULL,
		START_TIME DATETIME,
		END_TIME DATETIME,
		STORAGE_TYPE_ID SMALLINT NULL,
		STORAGE_ELEMENT_IDS TEXT NULL,
		PARENT_ID INT NOT NULL DEFAULT 0,
		URN VARCHAR(64) NULL,
		SETTINGS TEXT NULL,
		ORIGIN_ID VARCHAR(255) NULL,
		AUTHOR_ID INT NULL,
		EDITOR_ID INT NULL,
		CONSTRAINT PK_B_CRM_ACT PRIMARY KEY CLUSTERED(ID ASC)
	)
GO

IF NOT EXISTS (SELECT name FROM sys.indexes WHERE name = N'IX_B_CRM_ACT')
	CREATE INDEX IX_B_CRM_ACT ON B_CRM_ACT (ID ASC, PARENT_ID ASC, OWNER_ID ASC, OWNER_TYPE_ID ASC)
GO

IF NOT EXISTS (SELECT name FROM sys.indexes WHERE name = N'IX_B_CRM_ACT_1')
	CREATE INDEX IX_B_CRM_ACT_1 ON B_CRM_ACT (RESPONSIBLE_ID ASC, COMPLETED ASC, START_TIME ASC)
GO

IF OBJECT_ID(N'B_CRM_ACT_BIND', N'U') IS NULL
	CREATE TABLE B_CRM_ACT_BIND
	(
		ID INT IDENTITY(1,1) NOT NULL,
		ACTIVITY_ID INT NOT NULL,
		OWNER_ID INT NOT NULL,
		OWNER_TYPE_ID SMALLINT NOT NULL,
		CONSTRAINT PK_B_CRM_ACT_BIND PRIMARY KEY CLUSTERED(ID ASC)
	)
GO

IF NOT EXISTS (SELECT name FROM sys.indexes WHERE name = N'IX_B_CRM_ACT_BIND')
	CREATE INDEX IX_B_CRM_ACT_BIND ON B_CRM_ACT_BIND (ACTIVITY_ID ASC, OWNER_ID ASC, OWNER_TYPE_ID ASC, ID ASC)
GO

IF OBJECT_ID(N'B_CRM_ACT_COMM', N'U') IS NULL
	CREATE TABLE B_CRM_ACT_COMM
	(
		ID INT IDENTITY(1,1) NOT NULL,
		ACTIVITY_ID INT NOT NULL,
		OWNER_ID INT NOT NULL,
		OWNER_TYPE_ID SMALLINT NOT NULL,
		TYPE VARCHAR(64),
		VALUE VARCHAR(256),
		ENTITY_ID INT NOT NULL,
		ENTITY_TYPE_ID SMALLINT NOT NULL,
		ENTITY_SETTINGS TEXT,
		CONSTRAINT PK_B_CRM_ACT_COMM PRIMARY KEY CLUSTERED(ID ASC)
	)
GO

IF NOT EXISTS (SELECT name FROM sys.indexes WHERE name = N'IX_B_CRM_ACT_COMM')
	CREATE INDEX IX_B_CRM_ACT_COMM ON B_CRM_ACT_COMM (ACTIVITY_ID ASC, OWNER_ID ASC, OWNER_TYPE_ID ASC, ENTITY_ID ASC, ENTITY_TYPE_ID ASC, ID ASC)
GO

IF OBJECT_ID(N'B_CRM_ACT_ELEM', N'U') IS NULL
	CREATE TABLE B_CRM_ACT_ELEM
	(
		ACTIVITY_ID INT NOT NULL,
		STORAGE_TYPE_ID SMALLINT NOT NULL,
		ELEMENT_ID INT NOT NULL,
		CONSTRAINT PK_B_CRM_ACT_ELEM PRIMARY KEY CLUSTERED(ACTIVITY_ID ASC, STORAGE_TYPE_ID ASC, ELEMENT_ID ASC)
	)
GO

IF OBJECT_ID(N'B_CRM_USR_ACT', N'U') IS NULL
	CREATE TABLE B_CRM_USR_ACT
	(
		USER_ID INT NOT NULL,
		OWNER_ID INT NOT NULL,
		OWNER_TYPE_ID SMALLINT NOT NULL,
		ACTIVITY_TIME DATETIME NOT NULL,
		ACTIVITY_ID INT NOT NULL,
		DEPARTMENT_ID INT NOT NULL,
		CONSTRAINT PK_B_CRM_USR_ACT PRIMARY KEY CLUSTERED(USER_ID ASC, OWNER_ID ASC, OWNER_TYPE_ID ASC)
	)
GO

IF NOT EXISTS (SELECT name FROM sys.indexes WHERE name = N'IX_B_CRM_USR_ACT')
	CREATE INDEX IX_B_CRM_USR_ACT ON B_CRM_USR_ACT (USER_ID ASC, OWNER_ID ASC, OWNER_TYPE_ID ASC, ACTIVITY_TIME ASC, ACTIVITY_ID ASC, DEPARTMENT_ID ASC)
GO

IF OBJECT_ID(N'B_CRM_USR_MT', N'U') IS NULL
	CREATE TABLE B_CRM_USR_MT
	(
		ID INT IDENTITY(1,1) NOT NULL,
		OWNER_ID INT NOT NULL,
		ENTITY_TYPE_ID SMALLINT NOT NULL,
		SCOPE SMALLINT NOT NULL,
		IS_ACTIVE CHAR(1) NOT NULL DEFAULT 'N',
		TITLE VARCHAR(128),
		EMAIL_FROM VARCHAR(255),
		SUBJECT VARCHAR(255),
		BODY TEXT,
		SING_REQUIRED CHAR(1) NOT NULL DEFAULT 'N',
		SORT INT NOT NULL DEFAULT 100,
		CREATED DATETIME NOT NULL,
		LAST_UPDATED DATETIME NOT NULL,
		AUTHOR_ID INT NOT NULL,
		EDITOR_ID INT NOT NULL,
		CONSTRAINT PK_B_CRM_USR_MT PRIMARY KEY CLUSTERED(ID ASC)
	)
GO

IF NOT EXISTS (SELECT name FROM sys.indexes WHERE name = N'IX_B_CRM_USR_MT')
	CREATE INDEX IX_B_CRM_USR_MT ON B_CRM_USR_MT (OWNER_ID ASC, ENTITY_TYPE_ID ASC, SCOPE ASC, IS_ACTIVE ASC)
GO
