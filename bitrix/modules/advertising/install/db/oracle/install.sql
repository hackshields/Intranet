CREATE TABLE B_ADV_BANNER
(
    ID				NUMBER(18)	NOT NULL,
    CONTRACT_ID		NUMBER(18)	DEFAULT 1 NOT NULL,
    TYPE_SID			VARCHAR2(255 CHAR)	NOT NULL,
    STATUS_SID			VARCHAR2(255 CHAR)	DEFAULT 'PUBLISHED' NOT NULL,
    STATUS_COMMENTS		VARCHAR2(2000 CHAR)	NULL,
    NAME				VARCHAR2(255 CHAR)	NULL,
    GROUP_SID			VARCHAR2(255 CHAR)	NULL,
    FIRST_SITE_ID		CHAR(2 CHAR)		NULL,
    ACTIVE			CHAR(1 CHAR)		DEFAULT 'Y' NOT NULL,
    WEIGHT			NUMBER(18)	DEFAULT 100 NOT NULL,
    MAX_SHOW_COUNT		NUMBER(18)	NULL,
    SHOW_COUNT			NUMBER(18)	DEFAULT 0 NOT NULL,
    FIX_CLICK			CHAR(1 CHAR)		DEFAULT 'Y' NOT NULL,
    FIX_SHOW			CHAR(1 CHAR)		DEFAULT 'Y' NOT NULL,
    MAX_CLICK_COUNT		NUMBER(18)	NULL,
    CLICK_COUNT		NUMBER(18)	DEFAULT 0 NOT NULL,
    MAX_VISITOR_COUNT	NUMBER(18)	NULL,
    VISITOR_COUNT		NUMBER(18)	DEFAULT 0 NOT NULL,
    SHOWS_FOR_VISITOR	NUMBER(18)	NULL,
    DATE_LAST_SHOW		DATE			NULL,
    DATE_LAST_CLICK		DATE			NULL,
    DATE_SHOW_FROM		DATE			NULL,
    DATE_SHOW_TO		DATE			NULL,
    IMAGE_ID			NUMBER(18)	NULL,
    IMAGE_ALT			VARCHAR2(255 CHAR)	NULL,
    URL				VARCHAR2(2000 CHAR)	NULL,
    URL_TARGET			VARCHAR2(255 CHAR)	NULL,
    CODE				CLOB			NULL,
    CODE_TYPE			VARCHAR2(5 CHAR)	DEFAULT 'html' NOT NULL,
    STAT_EVENT_1		VARCHAR2(255 CHAR)	NULL,
    STAT_EVENT_2		VARCHAR2(255 CHAR)	NULL,
    STAT_EVENT_3		VARCHAR2(255 CHAR)	NULL,
    FOR_NEW_GUEST		CHAR(1 CHAR)		NULL,
    KEYWORDS			VARCHAR2(2000 CHAR)	NULL,
    COMMENTS			VARCHAR2(2000 CHAR)	NULL,
    DATE_CREATE		DATE			NULL,
    CREATED_BY			NUMBER(18)	NULL,
    DATE_MODIFY		DATE			NULL,
    MODIFIED_BY		NUMBER(18)	NULL,
    SHOW_USER_GROUP		char(1 CHAR) 		DEFAULT 'N' NOT NULL,
    NO_URL_IN_FLASH 	char(1 CHAR)		DEFAULT 'N' NOT NULL,
	FLYUNIFORM CHAR( 1 CHAR ) DEFAULT 'N' NOT NULL,
	DATE_SHOW_FIRST DATE NULL,
	AD_TYPE VARCHAR2( 20 CHAR ),
	FLASH_TRANSPARENT VARCHAR2( 20 CHAR ),
	FLASH_IMAGE NUMBER( 18 ),
	FLASH_JS CHAR( 1 CHAR ) DEFAULT 'N' NOT NULL,
	FLASH_VER VARCHAR2( 20 CHAR ),
	STAT_TYPE varchar2(20 CHAR),
	STAT_COUNT number(18),
    PRIMARY KEY (ID)
)
/
CREATE SEQUENCE SQ_B_ADV_BANNER START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE INDEX IX_B_ADV_BANNER_ACTIVE_TYPE ON B_ADV_BANNER(ACTIVE,TYPE_SID)
/
CREATE INDEX IX_B_ADV_BANNER_CONTRACT_TYPE ON B_ADV_BANNER(CONTRACT_ID,TYPE_SID)
/
CREATE TABLE B_ADV_BANNER_2_COUNTRY
(
    BANNER_ID  NUMBER(18) NOT NULL,
    COUNTRY_ID CHAR(2 CHAR)    NOT NULL,
	REGION varchar2(200 CHAR),
	CITY_ID number(18)
)
/
create index IX_B_ADV_BANNER_2_COUNTRY_1 on B_ADV_BANNER_2_COUNTRY (COUNTRY_ID, REGION, BANNER_ID)
/
create index IX_B_ADV_BANNER_2_COUNTRY_2 on B_ADV_BANNER_2_COUNTRY (CITY_ID, BANNER_ID)
/
create index IX_B_ADV_BANNER_2_COUNTRY_3 on B_ADV_BANNER_2_COUNTRY (BANNER_ID)
/

CREATE TABLE B_ADV_BANNER_2_DAY
(
    DATE_STAT     DATE       NOT NULL,
    BANNER_ID     NUMBER(18) NOT NULL,
    SHOW_COUNT    NUMBER(18) DEFAULT 0 NOT NULL,
    CLICK_COUNT   NUMBER(18) DEFAULT 0 NOT NULL,
    VISITOR_COUNT NUMBER(18) DEFAULT 0 NOT NULL,
    PRIMARY KEY (BANNER_ID,DATE_STAT)
)
/
CREATE TABLE B_ADV_BANNER_2_SITE
(
    BANNER_ID NUMBER(18) NOT NULL,
    SITE_ID       CHAR(2 CHAR)    NOT NULL,
    PRIMARY KEY (BANNER_ID,SITE_ID)
)
/
CREATE TABLE B_ADV_BANNER_2_PAGE
(
    ID           NUMBER(18)    NOT NULL,
    BANNER_ID    NUMBER(18)    NOT NULL,
    PAGE         VARCHAR2(255 CHAR) NOT NULL,
    SHOW_ON_PAGE CHAR(1 CHAR)       DEFAULT 'Y' NOT NULL,
    PRIMARY KEY (ID)
)
/
CREATE SEQUENCE SQ_B_ADV_BANNER_2_PAGE START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE INDEX IX_ADV_BANNER_2_PAGE_BANNER_ID ON B_ADV_BANNER_2_PAGE(BANNER_ID)
/
CREATE TABLE B_ADV_BANNER_2_STAT_ADV
(
    BANNER_ID   NUMBER(18) NOT NULL,
    STAT_ADV_ID NUMBER(18) NOT NULL,
    PRIMARY KEY (BANNER_ID,STAT_ADV_ID)
)
/
CREATE TABLE B_ADV_BANNER_2_WEEKDAY
(
    BANNER_ID NUMBER(18)   NOT NULL,
    C_WEEKDAY VARCHAR2(10 CHAR) NOT NULL,
    C_HOUR    NUMBER(2)    NOT NULL,
    PRIMARY KEY (BANNER_ID,C_WEEKDAY,C_HOUR)
)
/
CREATE TABLE B_ADV_CONTRACT
(
    ID                 NUMBER(18)     NOT NULL,
    ACTIVE             CHAR(1 CHAR)        DEFAULT 'Y' NOT NULL,
    NAME               VARCHAR2(255 CHAR)      NULL,
    DESCRIPTION        VARCHAR2(2000 CHAR)     NULL,
    KEYWORDS           VARCHAR2(2000 CHAR)     NULL,
    ADMIN_COMMENTS     VARCHAR2(2000 CHAR)     NULL,
    WEIGHT             NUMBER(18)         NULL,
    SORT               NUMBER(18)         NULL,
    MAX_SHOW_COUNT     NUMBER(18)         NULL,
    SHOW_COUNT         NUMBER(18)     DEFAULT 0 NOT NULL,
    MAX_CLICK_COUNT    NUMBER(18)         NULL,
    CLICK_COUNT        NUMBER(18)     DEFAULT 0 NOT NULL,
    MAX_VISITOR_COUNT  NUMBER(18)         NULL,
    VISITOR_COUNT      NUMBER(18)     DEFAULT 0 NOT NULL,
    DATE_SHOW_FROM     DATE               NULL,
    DATE_SHOW_TO       DATE               NULL,
    DEFAULT_STATUS_SID VARCHAR2(255 CHAR)  DEFAULT 'PUBLISHED' NOT NULL,
    EMAIL_COUNT        NUMBER(18)     DEFAULT 0 NOT NULL,
    DATE_CREATE        DATE               NULL,
    CREATED_BY         NUMBER(18)         NULL,
    DATE_MODIFY        DATE               NULL,
    MODIFIED_BY        NUMBER(18)         NULL,
    PRIMARY KEY (ID)
)
PCTFREE 50 INITRANS 100 MAXTRANS 255
/
CREATE SEQUENCE SQ_B_ADV_CONTRACT START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE TABLE B_ADV_CONTRACT_2_SITE
(
    CONTRACT_ID NUMBER(18) NOT NULL,
    SITE_ID         CHAR(2 CHAR)    NOT NULL,
    PRIMARY KEY (CONTRACT_ID,SITE_ID)
)
/
CREATE TABLE B_ADV_CONTRACT_2_PAGE
(
    ID           NUMBER(18)    NOT NULL,
    CONTRACT_ID  NUMBER(18)    NOT NULL,
    PAGE         VARCHAR2(255 CHAR) NOT NULL,
    SHOW_ON_PAGE CHAR(1 CHAR)       DEFAULT 'Y' NOT NULL,
    PRIMARY KEY (ID)
)
/
CREATE SEQUENCE SQ_B_ADV_CONTRACT_2_PAGE START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE INDEX IX_ADV_CONTRACT_2_PAGE_CONTRAC ON B_ADV_CONTRACT_2_PAGE(CONTRACT_ID)
/
CREATE TABLE B_ADV_CONTRACT_2_TYPE
(
    CONTRACT_ID NUMBER(18)    NOT NULL,
    TYPE_SID    VARCHAR2(255 CHAR) NOT NULL,
    PRIMARY KEY (CONTRACT_ID,TYPE_SID)
)
/
CREATE TABLE B_ADV_CONTRACT_2_USER
(
    ID          NUMBER(18)    NOT NULL,
    CONTRACT_ID NUMBER(18)    NOT NULL,
    USER_ID     NUMBER(18)    DEFAULT 1 NOT NULL,
    PERMISSION  VARCHAR2(255 CHAR) NOT NULL,
    PRIMARY KEY (ID)
)
/
CREATE SEQUENCE SQ_B_ADV_CONTRACT_2_USER START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
CREATE INDEX IX_B_ADV_CONTRACT_2_USER_CONTR ON B_ADV_CONTRACT_2_USER(CONTRACT_ID)
/
CREATE TABLE B_ADV_CONTRACT_2_WEEKDAY
(
    CONTRACT_ID NUMBER(18) NOT NULL,
    C_WEEKDAY   VARCHAR2(10 CHAR) NOT NULL,
    C_HOUR      NUMBER(18) NOT NULL,
    PRIMARY KEY (CONTRACT_ID,C_WEEKDAY,C_HOUR)
)
/
CREATE TABLE B_ADV_TYPE
(
    SID         VARCHAR2(255 CHAR)  NOT NULL,
    ACTIVE      CHAR(1 CHAR)        DEFAULT 'Y' NOT NULL,
    SORT        NUMBER(18)     DEFAULT 100 NOT NULL,
    NAME        VARCHAR2(255 CHAR)      NULL,
    DESCRIPTION VARCHAR2(2000 CHAR)     NULL,
    DATE_CREATE DATE               NULL,
    CREATED_BY  NUMBER(18)         NULL,
    DATE_MODIFY DATE               NULL,
    MODIFIED_BY NUMBER(18)         NULL,
    PRIMARY KEY (SID)
)
/
CREATE TABLE B_ADV_BANNER_2_GROUP
(
  	BANNER_ID NUMBER(18) NOT NULL,
	GROUP_ID NUMBER(18) NOT NULL,
	PRIMARY KEY (BANNER_ID, GROUP_ID)
)
/
