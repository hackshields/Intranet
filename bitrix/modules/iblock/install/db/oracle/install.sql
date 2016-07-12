CREATE TABLE b_iblock_type
(
	ID VARCHAR2(50 CHAR) NOT NULL,
	SECTIONS CHAR(1 CHAR) DEFAULT('Y') NOT NULL,
	EDIT_FILE_BEFORE VARCHAR2(255 CHAR),
	EDIT_FILE_AFTER VARCHAR2(255 CHAR),
	IN_RSS CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	SORT NUMBER(18) DEFAULT 500 NOT NULL,
	CONSTRAINT PK_B_IBLOCK_TYPE PRIMARY KEY (ID)
)
/

CREATE TABLE b_iblock_type_lang
(
	IBLOCK_TYPE_ID VARCHAR2(50 CHAR) NOT NULL,
	LID CHAR(2 CHAR) NOT NULL,
	NAME VARCHAR2(100 CHAR) NOT NULL,
	SECTION_NAME VARCHAR2(100 CHAR),
	ELEMENT_NAME VARCHAR2(100 CHAR)
)
/

CREATE TABLE b_iblock
(
	ID number(18) NOT NULL,
	TIMESTAMP_X date DEFAULT SYSDATE NOT NULL,
	IBLOCK_TYPE_ID VARCHAR2(50 CHAR) NOT NULL,
	LID CHAR(2 CHAR) NOT NULL,
	CODE VARCHAR2(50 CHAR),
	NAME VARCHAR2(255 CHAR) NOT NULL,
	ACTIVE CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	SORT NUMBER(18) DEFAULT '500' NOT NULL,
	LIST_PAGE_URL VARCHAR2(255 CHAR),
	SECTION_PAGE_URL VARCHAR2(255 CHAR),
	DETAIL_PAGE_URL VARCHAR2(255 CHAR),
	PICTURE NUMBER(18),
	DESCRIPTION CLOB,
	DESCRIPTION_TYPE CHAR(4 CHAR) DEFAULT 'text' NOT NULL,
	RSS_TTL number(18) DEFAULT 24 NOT NULL,
	RSS_ACTIVE CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	RSS_FILE_ACTIVE CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	RSS_FILE_LIMIT NUMBER(18),
	RSS_FILE_DAYS NUMBER(18),
	RSS_YANDEX_ACTIVE CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	XML_ID VARCHAR2(255 CHAR),
	TMP_ID VARCHAR2(40 CHAR),
	INDEX_ELEMENT CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	INDEX_SECTION CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	WORKFLOW CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	BIZPROC CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	SECTION_CHOOSER CHAR(1 CHAR),
	LIST_MODE CHAR(1 CHAR),
	RIGHTS_MODE CHAR(1 CHAR) NULL,
	SECTION_PROPERTY CHAR(1 CHAR),
	VERSION NUMBER(2) DEFAULT 1 NOT NULL,
	LAST_CONV_ELEMENT NUMBER(18) DEFAULT 0 NOT NULL,
	SOCNET_GROUP_ID NUMBER(18),
	EDIT_FILE_BEFORE VARCHAR2(255 CHAR),
	EDIT_FILE_AFTER VARCHAR2(255 CHAR),
	SECTIONS_NAME VARCHAR2(100 CHAR),
	SECTION_NAME VARCHAR2(100 CHAR),
	ELEMENTS_NAME VARCHAR2(100 CHAR),
	ELEMENT_NAME VARCHAR2(100 CHAR),
	CONSTRAINT PK_B_IBLOCK PRIMARY KEY (ID),
	CONSTRAINT FK_B_IBLOCK FOREIGN KEY (IBLOCK_TYPE_ID) REFERENCES b_iblock_type(ID),
	CONSTRAINT FK_B_IBLOCK1 FOREIGN KEY (LID) REFERENCES b_lang(LID)
)
/

CREATE INDEX ix_iblock ON b_iblock(IBLOCK_TYPE_ID, LID, ACTIVE)
/

CREATE SEQUENCE sq_b_iblock
/

CREATE OR REPLACE TRIGGER b_iblock_insert
BEFORE INSERT
ON b_iblock
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_iblock.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE OR REPLACE TRIGGER b_iblock_update
BEFORE UPDATE
ON b_iblock
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
	IF :NEW.TIMESTAMP_X IS NOT NULL THEN
		:NEW.TIMESTAMP_X := SYSDATE;
	ELSE
		:NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X;
	END IF;
END;
/

CREATE TABLE b_iblock_site
(
	IBLOCK_ID NUMBER(18) NOT NULL,
	SITE_ID CHAR(2 CHAR) NOT NULL,
	CONSTRAINT PK_B_IBLOCK_SITE PRIMARY KEY (IBLOCK_ID, SITE_ID)
)
/

CREATE TABLE b_iblock_messages
(
	IBLOCK_ID NUMBER(18) NOT NULL,
	MESSAGE_ID VARCHAR2(50 CHAR) NOT NULL,
	MESSAGE_TEXT VARCHAR2(255 CHAR),
	CONSTRAINT PK_B_IBLOCK_MESSAGES PRIMARY KEY (IBLOCK_ID, MESSAGE_ID)
)
/

CREATE TABLE b_iblock_fields
(
	IBLOCK_ID NUMBER(18) NOT NULL,
	FIELD_ID VARCHAR2(50 CHAR) NOT NULL,
	IS_REQUIRED CHAR(1 CHAR),
	DEFAULT_VALUE CLOB,
	CONSTRAINT PK_B_IBLOCK_FIELDS PRIMARY KEY (IBLOCK_ID, FIELD_ID)
)
/

CREATE TABLE b_iblock_property
(
	ID NUMBER(18) NOT NULL,
	TIMESTAMP_X DATE DEFAULT SYSDATE NOT NULL,
	IBLOCK_ID NUMBER(18) NOT NULL,
	NAME VARCHAR2(255 CHAR) NOT NULL,
	ACTIVE CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	CODE VARCHAR2(50 CHAR),
	SORT NUMBER(18) DEFAULT 500 NOT NULL,
	DEFAULT_VALUE VARCHAR2(2000 CHAR),
	PROPERTY_TYPE CHAR(1 CHAR) DEFAULT 'S' NOT NULL,
	ROW_COUNT NUMBER(18) DEFAULT 1 NOT NULL,
	COL_COUNT NUMBER(18) DEFAULT 30 NOT NULL,
	LIST_TYPE CHAR(1 CHAR) DEFAULT 'L' NOT NULL,
	MULTIPLE CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	XML_ID VARCHAR2(100 CHAR),
	FILE_TYPE VARCHAR2(200 CHAR),
	MULTIPLE_CNT NUMBER(18),
	TMP_ID VARCHAR2(40 CHAR),
	LINK_IBLOCK_ID NUMBER(18),
	WITH_DESCRIPTION CHAR(1 CHAR),
	SEARCHABLE CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	FILTRABLE CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	IS_REQUIRED CHAR(1 CHAR),
	VERSION NUMBER(2) DEFAULT 1 NOT NULL,
	USER_TYPE VARCHAR2(255 CHAR),
	USER_TYPE_SETTINGS CLOB,
	HINT VARCHAR2(255 CHAR),
	CONSTRAINT PK_B_IBLOCK_PROPERTY PRIMARY KEY (ID),
	CONSTRAINT fk_b_iblock_property FOREIGN KEY (IBLOCK_ID) REFERENCES b_iblock(ID)
)
/
CREATE INDEX ix_iblock_property_1 ON b_iblock_property(IBLOCK_ID)
/
CREATE INDEX ix_iblock_property_2 ON b_iblock_property(UPPER(CODE))
/
CREATE INDEX ix_iblock_property_3 ON b_iblock_property(LINK_IBLOCK_ID)
/
CREATE SEQUENCE sq_b_iblock_property
/


CREATE OR REPLACE TRIGGER b_iblock_property_insert
BEFORE INSERT
ON b_iblock_property
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_iblock_property.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE OR REPLACE TRIGGER b_iblock_property_update
BEFORE UPDATE
ON b_iblock_property
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
	IF :NEW.TIMESTAMP_X IS NOT NULL THEN
		:NEW.TIMESTAMP_X := SYSDATE;
	ELSE
		:NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X;
	END IF;
END;
/


create table b_iblock_property_enum
(
	ID NUMBER(18) NOT NULL,
	PROPERTY_ID NUMBER(18) NOT NULL,
	VALUE VARCHAR2(255 CHAR) NOT NULL,
	DEF CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	SORT NUMBER(18) DEFAULT 500 NOT NULL,
	XML_ID VARCHAR2(200 CHAR) NOT NULL,
	TMP_ID VARCHAR2(40 CHAR),
	CONSTRAINT PK_B_IBLOCK_PROPERTY_ENUM PRIMARY KEY (ID),
	CONSTRAINT fk_b_iblock_propenum FOREIGN KEY (PROPERTY_ID) REFERENCES b_iblock_property(ID)
)
/

CREATE SEQUENCE sq_b_iblock_property_enum
/

CREATE OR REPLACE TRIGGER b_iblock_property_enum_insert
BEFORE INSERT
ON b_iblock_property_enum
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_iblock_property_enum.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

create unique index ux_iblock_property_enum on b_iblock_property_enum(PROPERTY_ID, XML_ID)
/

CREATE TABLE b_iblock_section
(
	ID NUMBER(18) NOT NULL,
	TIMESTAMP_X DATE DEFAULT SYSDATE NOT NULL,
	MODIFIED_BY NUMBER(18),
	DATE_CREATE DATE,
	CREATED_BY NUMBER(18),
	IBLOCK_ID NUMBER(18) NOT NULL,
	IBLOCK_SECTION_ID NUMBER(18),
	ACTIVE CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	GLOBAL_ACTIVE CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	SORT NUMBER(18) DEFAULT 500 NOT NULL,
	NAME VARCHAR2(255 CHAR) NOT NULL,
	PICTURE NUMBER(18),
	LEFT_MARGIN NUMBER(18),
	RIGHT_MARGIN NUMBER(18),
	DEPTH_LEVEL NUMBER(18),
	DESCRIPTION CLOB,
	DESCRIPTION_TYPE CHAR(4 CHAR) DEFAULT 'text' NOT NULL,
	SEARCHABLE_CONTENT CLOB,
	XML_ID VARCHAR2(255 CHAR),
	TMP_ID VARCHAR2(40 CHAR),
	CODE VARCHAR2(255 CHAR),
	DETAIL_PICTURE NUMBER(18),
	SOCNET_GROUP_ID NUMBER(18),
	CONSTRAINT PK_B_IBLOCK_SECTION PRIMARY KEY (ID),
	CONSTRAINT fk_b_iblock_section FOREIGN KEY (IBLOCK_ID) REFERENCES b_iblock(ID),
	CONSTRAINT fk_b_iblock_section1 FOREIGN KEY (IBLOCK_SECTION_ID) REFERENCES b_iblock_section(ID)
)
/
CREATE INDEX ix_iblock_section_1 ON b_iblock_section(IBLOCK_ID, IBLOCK_SECTION_ID)
/
CREATE INDEX ux_iblock_section_1 ON b_iblock_section(IBLOCK_ID, LEFT_MARGIN, RIGHT_MARGIN)
/
CREATE INDEX ix_iblock_section_code ON b_iblock_section(IBLOCK_ID, CODE)
/

CREATE SEQUENCE sq_b_iblock_section
/

CREATE OR REPLACE TRIGGER b_iblock_section_insert
BEFORE INSERT
ON b_iblock_section
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_iblock_section.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE OR REPLACE TRIGGER b_iblock_section_update
BEFORE UPDATE
ON b_iblock_section
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
	IF :NEW.LEFT_MARGIN=:OLD.LEFT_MARGIN AND :NEW.RIGHT_MARGIN=:OLD.RIGHT_MARGIN AND :NEW.GLOBAL_ACTIVE=:OLD.GLOBAL_ACTIVE THEN
		DELFILE(:OLD.PICTURE, :NEW.PICTURE);
		DELFILE(:OLD.DETAIL_PICTURE, :NEW.DETAIL_PICTURE);
	END IF;
	IF :NEW.TIMESTAMP_X IS NOT NULL THEN
		:NEW.TIMESTAMP_X := SYSDATE;
	ELSE
		:NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X;
	END IF;
END;
/

CREATE OR REPLACE TRIGGER b_iblock_section_delete
BEFORE DELETE
ON b_iblock_section
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
	DELFILE(:OLD.PICTURE, NULL);
	DELFILE(:OLD.DETAIL_PICTURE, NULL);
END;
/

CREATE TABLE b_iblock_section_property
(
	IBLOCK_ID NUMBER(18) NOT NULL,
	SECTION_ID NUMBER(18) NOT NULL,
	PROPERTY_ID NUMBER(18) NOT NULL,
	SMART_FILTER CHAR(1 CHAR),
	CONSTRAINT pk_b_iblock_section_property PRIMARY KEY (IBLOCK_ID, SECTION_ID, PROPERTY_ID)
)
/
CREATE INDEX ix_b_iblock_section_property_1 ON b_iblock_section_property (PROPERTY_ID)
/
CREATE INDEX ix_b_iblock_section_property_2 ON b_iblock_section_property (SECTION_ID)
/

CREATE TABLE b_iblock_element
(
	ID NUMBER(18) NOT NULL,
	TIMESTAMP_X DATE DEFAULT SYSDATE NULL,
	MODIFIED_BY NUMBER(18),
	DATE_CREATE DATE,
	CREATED_BY NUMBER(18),
	IBLOCK_ID NUMBER(18) NOT NULL,
	IBLOCK_SECTION_ID NUMBER(18),
	ACTIVE CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
	ACTIVE_FROM DATE,
	ACTIVE_TO DATE,
	SORT NUMBER(18) DEFAULT 500 NOT NULL,
	NAME VARCHAR2(255 CHAR) NOT NULL,
	PREVIEW_PICTURE NUMBER(18),
	PREVIEW_TEXT VARCHAR2(2000 CHAR),
	PREVIEW_TEXT_TYPE CHAR(4 CHAR) DEFAULT 'text' NOT NULL,
	DETAIL_PICTURE NUMBER(18),
	DETAIL_TEXT CLOB,
	DETAIL_TEXT_TYPE CHAR(4 CHAR) DEFAULT 'text' NOT NULL,
	SEARCHABLE_CONTENT CLOB NULL,
	WF_STATUS_ID NUMBER(18) DEFAULT 1,
	WF_PARENT_ELEMENT_ID NUMBER(18),
	WF_NEW CHAR(1 CHAR),
	WF_LOCKED_BY NUMBER(18),
	WF_DATE_LOCK DATE,
	WF_COMMENTS VARCHAR2(2000 CHAR),
	IN_SECTIONS CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
	XML_ID VARCHAR2(255 CHAR),
	TMP_ID VARCHAR2(40 CHAR),
	CODE VARCHAR2(255 CHAR),
	TAGS VARCHAR2(255 CHAR),
	WF_LAST_HISTORY_ID NUMBER(18),
	SHOW_COUNTER NUMBER(18),
	SHOW_COUNTER_START DATE,
	CONSTRAINT PK_B_IBLOCK_ELEMENT PRIMARY KEY (ID),
	CONSTRAINT fk_b_iblock_element FOREIGN KEY (IBLOCK_ID) REFERENCES b_iblock(ID),
	CONSTRAINT fk_b_iblock_element1 FOREIGN KEY (IBLOCK_SECTION_ID) REFERENCES b_iblock_section(ID)
)
/
CREATE INDEX ix_iblock_element_1 ON b_iblock_element(IBLOCK_ID, IBLOCK_SECTION_ID)
/
CREATE INDEX IX_IBLOCK_ELEMENT_41 ON B_IBLOCK_ELEMENT(IBLOCK_ID, XML_ID, WF_PARENT_ELEMENT_ID)
/
CREATE INDEX ix_iblock_element_3 ON B_IBLOCK_ELEMENT(WF_PARENT_ELEMENT_ID)
/
CREATE INDEX ix_iblock_element_sec ON b_iblock_element(IBLOCK_SECTION_ID)
/
CREATE INDEX IX_IBLOCK_ELEMENT_4 ON B_IBLOCK_ELEMENT(ACTIVE_FROM, IBLOCK_ID)
/
CREATE INDEX IX_IBLOCK_ELEMENT_PUB ON B_IBLOCK_ELEMENT(IBLOCK_ID, WF_STATUS_ID, WF_PARENT_ELEMENT_ID, ACTIVE_FROM)
/
CREATE INDEX IX_IBLOCK_ELEMENT_CODE ON B_IBLOCK_ELEMENT(IBLOCK_ID, CODE)
/

CREATE SEQUENCE sq_b_iblock_element
/

CREATE OR REPLACE TRIGGER b_iblock_element_insert
BEFORE INSERT
ON b_iblock_element
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_iblock_element.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE OR REPLACE TRIGGER b_iblock_element_update
BEFORE UPDATE
ON b_iblock_element
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
	IF :NEW.TIMESTAMP_X IS NOT NULL THEN
		:NEW.TIMESTAMP_X := SYSDATE;
	ELSE
		:NEW.TIMESTAMP_X := :OLD.TIMESTAMP_X;
	END IF;
END;
/

create table B_IBLOCK_SECTION_ELEMENT
(
	IBLOCK_SECTION_ID number(18) not null,
	IBLOCK_ELEMENT_ID number(18) not null,
	ADDITIONAL_PROPERTY_ID NUMBER(18) NULL
)
/

CREATE UNIQUE INDEX ux_iblock_section_element ON B_IBLOCK_SECTION_ELEMENT(IBLOCK_SECTION_ID, IBLOCK_ELEMENT_ID, ADDITIONAL_PROPERTY_ID)
/
CREATE INDEX UX_IBLOCK_SECTION_ELEMENT2 ON B_IBLOCK_SECTION_ELEMENT(IBLOCK_ELEMENT_ID)
/

ALTER TABLE B_IBLOCK_SECTION_ELEMENT ADD CONSTRAINT fk_b_iblock_sect_el_el FOREIGN KEY (IBLOCK_ELEMENT_ID) REFERENCES b_iblock_element(ID) ON DELETE CASCADE
/
ALTER TABLE B_IBLOCK_SECTION_ELEMENT ADD CONSTRAINT fk_b_iblock_sect_el_sec FOREIGN KEY (IBLOCK_SECTION_ID) REFERENCES b_iblock_section(ID) ON DELETE CASCADE
/

CREATE TABLE b_iblock_element_property
(
	ID NUMBER(18) NOT NULL,
	IBLOCK_PROPERTY_ID NUMBER(18) NOT NULL,
	IBLOCK_ELEMENT_ID NUMBER(18) NOT NULL,
	VALUE VARCHAR2(2000 CHAR),
	VALUE_TYPE CHAR(4 CHAR) DEFAULT 'text' NOT NULL,
	VALUE_ENUM NUMBER(18),
	VALUE_NUM NUMBER(18, 4),
	DESCRIPTION VARCHAR2(255 CHAR),
	CONSTRAINT PK_B_IBLOCK_ELEMENT_PROPERTY PRIMARY KEY (ID),
	CONSTRAINT fk_b_iblock_element_property FOREIGN KEY (IBLOCK_PROPERTY_ID) REFERENCES b_iblock_property(ID),
	CONSTRAINT fk_b_iblock_element_property1 FOREIGN KEY (IBLOCK_ELEMENT_ID) REFERENCES b_iblock_element(ID)
)
/

CREATE SEQUENCE sq_b_iblock_element_property
/
CREATE INDEX ix_iblock_element_property_1 ON b_iblock_element_property(IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID)
/
CREATE INDEX IX_IBLOCK_ELEMENT_PROP_ENUM ON B_IBLOCK_ELEMENT_PROPERTY(VALUE_ENUM,IBLOCK_PROPERTY_ID)
/
CREATE INDEX IX_IBLOCK_ELEMENT_PROP_NUM ON B_IBLOCK_ELEMENT_PROPERTY(VALUE_NUM,IBLOCK_PROPERTY_ID)
/
CREATE INDEX ix_iblock_element_property_2 ON B_IBLOCK_ELEMENT_PROPERTY(IBLOCK_PROPERTY_ID)
/

CREATE OR REPLACE TRIGGER b_iblock_element_prop_insert
BEFORE INSERT
ON b_iblock_element_property
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_iblock_element_property.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE SEQUENCE sq_b_iblock_right
/
create table b_iblock_right
(
	ID NUMBER(18) NOT NULL,
	IBLOCK_ID NUMBER(18) NOT NULL,
	GROUP_CODE VARCHAR2(50 CHAR) NOT NULL,
	ENTITY_TYPE VARCHAR2(32 CHAR) NOT NULL,
	ENTITY_ID NUMBER(18) NOT NULL,
	DO_INHERIT CHAR(1 CHAR) NOT NULL,
	TASK_ID NUMBER(18) NOT NULL,
	OP_SREAD CHAR(1 CHAR) NOT NULL,
	OP_EREAD CHAR(1 CHAR) NOT NULL,
	XML_ID VARCHAR2(32 CHAR) NULL,
	CONSTRAINT PK_B_IBLOCK_RIGHT PRIMARY KEY (ID),
	CONSTRAINT FK_B_IBLOCK_RIGHT_IBLOCK_ID FOREIGN KEY (IBLOCK_ID) REFERENCES b_iblock(ID),
	CONSTRAINT FK_B_IBLOCK_RIGHT_TASK_ID FOREIGN KEY (TASK_ID) REFERENCES b_task(ID)
)
/
CREATE INDEX IX_B_IBLOCK_RIGHT_IBLOCK_ID ON B_IBLOCK_RIGHT (IBLOCK_ID, ENTITY_TYPE, ENTITY_ID)
/
CREATE INDEX IX_B_IBLOCK_RIGHT_GROUP_CODE ON B_IBLOCK_RIGHT (GROUP_CODE, IBLOCK_ID)
/
CREATE INDEX IX_B_IBLOCK_RIGHT_OP_EREAD ON B_IBLOCK_RIGHT (ID, OP_EREAD, GROUP_CODE)
/
CREATE INDEX IX_B_IBLOCK_RIGHT_OP_SREAD ON B_IBLOCK_RIGHT (ID, OP_SREAD, GROUP_CODE)
/
CREATE INDEX IX_B_IBLOCK_RIGHT_TASK_ID ON B_IBLOCK_RIGHT (TASK_ID)
/

create table b_iblock_section_right
(
	IBLOCK_ID NUMBER(18) NOT NULL,
	SECTION_ID NUMBER(18) NOT NULL,
	RIGHT_ID NUMBER(18) NOT NULL,
	IS_INHERITED CHAR(1 CHAR) NOT NULL,
	CONSTRAINT PK_B_IBLOCK_SECTION_RIGHT PRIMARY KEY (RIGHT_ID, SECTION_ID)
)
/
CREATE INDEX ix_b_iblock_section_right_1 ON b_iblock_section_right (SECTION_ID, IBLOCK_ID)
/
CREATE INDEX ix_b_iblock_section_right_2 ON b_iblock_section_right (IBLOCK_ID, RIGHT_ID)
/

create table b_iblock_element_right
(
	IBLOCK_ID NUMBER(18) NOT NULL,
	SECTION_ID NUMBER(18) NOT NULL,
	ELEMENT_ID NUMBER(18) NOT NULL,
	RIGHT_ID NUMBER(18) NOT NULL,
	IS_INHERITED CHAR(1 CHAR) NOT NULL,
	CONSTRAINT PK_B_IBLOCK_ELEMENT_RIGHT PRIMARY KEY (RIGHT_ID, ELEMENT_ID, SECTION_ID)
)
/
CREATE INDEX ix_b_iblock_element_right_1 ON b_iblock_element_right (ELEMENT_ID, IBLOCK_ID)
/
CREATE INDEX ix_b_iblock_element_right_2 ON b_iblock_element_right (IBLOCK_ID, RIGHT_ID)
/


CREATE TABLE b_iblock_group
(
	IBLOCK_ID NUMBER(18) NOT NULL,
	GROUP_ID NUMBER(18) NOT NULL,
	PERMISSION CHAR(1 CHAR) NOT NULL,
	CONSTRAINT fk_b_iblock_group FOREIGN KEY (IBLOCK_ID) REFERENCES b_iblock(ID),
	CONSTRAINT fk_b_iblock_group1 FOREIGN KEY (GROUP_ID) REFERENCES b_group(ID)
)
/

CREATE UNIQUE INDEX ux_iblock_group_1 ON b_iblock_group(IBLOCK_ID, GROUP_ID)
/

CREATE TABLE B_IBLOCK_RSS
(
	ID NUMBER(18) NOT NULL,
	IBLOCK_ID NUMBER(18) NOT NULL,
	NODE VARCHAR2(50 CHAR) NOT NULL,
	NODE_VALUE VARCHAR2(250 CHAR) NOT NULL,
	CONSTRAINT PK_B_IBLOCK_RSS PRIMARY KEY (ID),
	CONSTRAINT FK_IBLOCK_IBLOCK_RSS FOREIGN KEY (IBLOCK_ID) REFERENCES B_IBLOCK (ID)
)
/

CREATE SEQUENCE SQ_IBLOCK_RSS
/

CREATE TABLE B_IBLOCK_CACHE
(
	CACHE_KEY VARCHAR2(35 CHAR) NOT NULL,
	CACHE CLOB NOT NULL,
	CACHE_DATE DATE NOT NULL,
	CONSTRAINT PK_B_IBLOCK_CACHE PRIMARY KEY (CACHE_KEY)
)
/

CREATE TABLE B_IBLOCK_ELEMENT_LOCK
(
	IBLOCK_ELEMENT_ID NUMBER(18) NOT NULL,
	DATE_LOCK DATE,
	LOCKED_BY VARCHAR2(32 CHAR),
	CONSTRAINT FK_B_IBLOCK_ELEMENT_LOCK_0 FOREIGN KEY (IBLOCK_ELEMENT_ID) REFERENCES B_IBLOCK_ELEMENT (ID),
	CONSTRAINT PK_B_IBLOCK_ELEMENT_LOCK PRIMARY KEY (IBLOCK_ELEMENT_ID)
)
/

CREATE TABLE B_IBLOCK_SEQUENCE
(
	IBLOCK_ID NUMBER(18) NOT NULL,
	CODE VARCHAR2(50 CHAR) NOT NULL,
	SEQ_VALUE NUMBER(18),
	CONSTRAINT PK_B_IBLOCK_SEQUENCE PRIMARY KEY (IBLOCK_ID, CODE)
)
/

CREATE TABLE b_iblock_offers_tmp
(
	ID NUMBER(18) NOT NULL,
	PRODUCT_IBLOCK_ID NUMBER(18) NOT NULL,
	OFFERS_IBLOCK_ID NUMBER(18) NOT NULL,
	TIMESTAMP_X DATE DEFAULT SYSDATE NOT NULL,
	CONSTRAINT pk_b_iblock_offers_tmp PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_iblock_offers_tmp
/
CREATE OR REPLACE TRIGGER b_iblock_offers_tmp_insert
BEFORE INSERT
ON b_iblock_offers_tmp
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_iblock_offers_tmp.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
