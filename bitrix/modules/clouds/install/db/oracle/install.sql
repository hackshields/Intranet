CREATE TABLE b_clouds_file_bucket
(
	ID NUMBER(18) NOT NULL,
	ACTIVE CHAR(1 CHAR) DEFAULT 'Y',
	SORT NUMBER(18) DEFAULT 500,
	READ_ONLY CHAR(1 CHAR) DEFAULT 'N',
	SERVICE_ID VARCHAR2(50 CHAR),
	BUCKET VARCHAR2(63 CHAR),
	LOCATION VARCHAR2(50 CHAR),
	CNAME VARCHAR2(100 CHAR),
	FILE_COUNT NUMBER(18) DEFAULT 0,
	FILE_SIZE NUMBER DEFAULT 0,
	LAST_FILE_ID NUMBER(18),
	PREFIX VARCHAR2(100 CHAR),
	SETTINGS VARCHAR2(2000 CHAR),
	FILE_RULES VARCHAR2(2000 CHAR),
	CONSTRAINT PK_B_CLOUDS_FILE_BUCKET PRIMARY KEY (ID)
)
/

CREATE SEQUENCE sq_b_clouds_file_bucket
/

CREATE OR REPLACE TRIGGER b_clouds_file_bucket_insert
BEFORE INSERT
ON b_clouds_file_bucket
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_clouds_file_bucket.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE b_clouds_file_upload
(
	ID VARCHAR2(32 CHAR) NOT NULL,
	TIMESTAMP_X DATE NOT NULL,
	FILE_PATH VARCHAR2(500 CHAR) NOT NULL,
	TMP_FILE VARCHAR2(500 CHAR) NULL,
	BUCKET_ID NUMBER(18) NOT NULL,
	PART_SIZE NUMBER(18) NOT NULL,
	PART_NO NUMBER(18) NOT NULL,
	PART_FAIL_COUNTER NUMBER(18) NOT NULL,
	NEXT_STEP CLOB,
	CONSTRAINT PK_B_CLOUDS_FILE_UPLOAD PRIMARY KEY (ID)
)
/

CREATE TABLE b_clouds_file_resize
(
	ID NUMBER(18) NOT NULL,
	TIMESTAMP_X DATE NOT NULL,
	ERROR_CODE CHAR(1 CHAR) DEFAULT '0' NOT NULL,
	FILE_ID NUMBER(18),
	PARAMS VARCHAR2(2000 CHAR),
	FROM_PATH VARCHAR2(500 CHAR),
	TO_PATH VARCHAR2(500 CHAR),
	CONSTRAINT pk_b_file_resize PRIMARY KEY (ID)
)
/
CREATE INDEX ix_b_file_resize_ts ON b_clouds_file_resize (TIMESTAMP_X)
/
CREATE INDEX ix_b_file_resize_path ON b_clouds_file_resize (TO_PATH)
/
CREATE INDEX ix_b_file_resize_file ON b_clouds_file_resize (FILE_ID)
/
CREATE SEQUENCE sq_b_clouds_file_resize
/
CREATE OR REPLACE TRIGGER b_clouds_file_resize_insert
BEFORE INSERT
ON b_clouds_file_resize
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT sq_b_clouds_file_resize.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

