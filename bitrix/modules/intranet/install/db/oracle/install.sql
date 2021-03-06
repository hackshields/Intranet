CREATE TABLE b_intranet_sharepoint
(
	IBLOCK_ID NUMBER(18) NOT NULL,
	SP_LIST_ID VARCHAR2(32 CHAR) NOT NULL,
	SP_URL VARCHAR2(255 CHAR) NOT NULL,
	SP_AUTH_USER VARCHAR2(50 CHAR) DEFAULT '' NULL,
	SP_AUTH_PASS VARCHAR2(50 CHAR) DEFAULT '' NULL,
	SYNC_DATE DATE DEFAULT SYSDATE NULL,
	SYNC_PERIOD NUMBER(18) DEFAULT 86400 NULL,
	SYNC_ERRORS NUMBER(1) DEFAULT 0 NULL,
	SYNC_LAST_TOKEN VARCHAR2(100 CHAR) DEFAULT '' NULL,
	SYNC_PAGING VARCHAR2(100 CHAR) DEFAULT '' NULL,
	HANDLER_MODULE VARCHAR2(50 CHAR) DEFAULT '' NULL,
	HANDLER_CLASS VARCHAR2(100 CHAR) DEFAULT '' NULL,
	PRIORITY CHAR(1 CHAR) DEFAULT 'B' NULL,
	CONSTRAINT PK_B_INTRANET_SHAREPOINT PRIMARY KEY (IBLOCK_ID)
)
/
CREATE TABLE b_intranet_sharepoint_field
(
	IBLOCK_ID NUMBER(18) NOT NULL,
	FIELD_ID VARCHAR2(50 CHAR) NOT NULL,
	SP_FIELD VARCHAR2(50 CHAR) NOT NULL,
	SP_FIELD_TYPE VARCHAR2(50 CHAR) NOT NULL,
	SETTINGS CLOB NULL,
	CONSTRAINT PK_B_INTRANET_SHAREPOINT_FIELD PRIMARY KEY (IBLOCK_ID, FIELD_ID)
)
/

CREATE TABLE b_intranet_sharepoint_queue
(
	ID NUMBER(18) NOT NULL,
	IBLOCK_ID NUMBER(18) NOT NULL,
	SP_METHOD VARCHAR2(100 CHAR) NOT NULL,
	SP_METHOD_PARAMS CLOB DEFAULT '' NULL,
	CALLBACK CLOB DEFAULT '' NULL,
	CONSTRAINT PK_B_INTRANET_SHAREPOINT_QUEUE PRIMARY KEY (ID)
)
/

CREATE INDEX ix_b_intranet_sharepoint_queue ON b_intranet_sharepoint_queue(IBLOCK_ID)
/

CREATE SEQUENCE sq_b_intranet_sharepoint_queue START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_intranet_sharepoint_queue_in
BEFORE INSERT
ON b_intranet_sharepoint_queue
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_intranet_sharepoint_queue.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE b_intranet_sharepoint_log
(
	ID NUMBER(18) NOT NULL,
	IBLOCK_ID NUMBER(18) NOT NULL,
	ELEMENT_ID NUMBER(18) NOT NULL,
	VERSION NUMBER(18) NULL,
	CONSTRAINT PK_B_INTRANET_SHAREPOINT_LOG PRIMARY KEY (ID)
)
/

CREATE UNIQUE INDEX ui_b_intranet_sharepoint_log ON b_intranet_sharepoint_log(IBLOCK_ID, ELEMENT_ID)  
/

CREATE SEQUENCE sq_b_intranet_sharepoint_log START WITH 1 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

CREATE OR REPLACE TRIGGER b_intranet_sharepoint_log_in
BEFORE INSERT
ON b_intranet_sharepoint_log
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT sq_b_intranet_sharepoint_log.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE B_RATING_SUBORDINATE (
	ID number(11) not null,
	RATING_ID number(11) not null,
	ENTITY_ID number(11) not null,
	VOTES NUMBER(18, 4) default 0 null,
	PRIMARY KEY (ID)
)
/

CREATE SEQUENCE SQ_B_RATING_SUBORDINATE
/

CREATE OR REPLACE TRIGGER B_RAT_SUBORDINATE_insert
BEFORE INSERT
ON B_RATING_SUBORDINATE
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT SQ_B_RATING_SUBORDINATE.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/
CREATE INDEX IX_B_RAT_SUBORDINATE ON B_RATING_SUBORDINATE (RATING_ID, ENTITY_ID)
/

