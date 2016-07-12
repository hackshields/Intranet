CREATE TABLE b_lists_permission
(
	IBLOCK_TYPE_ID VARCHAR2(50 CHAR) NOT NULL,
	GROUP_ID NUMBER(18) NOT NULL,
	CONSTRAINT PK_B_LISTS_PERMISSION PRIMARY KEY (IBLOCK_TYPE_ID, GROUP_ID)
)
/
CREATE TABLE b_lists_field
(
	IBLOCK_ID number(18) not null,
	FIELD_ID varchar2(50 char) not null,
	SORT number(18) not null,
	NAME varchar2(100 char) not null,
	SETTINGS clob,
	CONSTRAINT PK_B_LISTS_FIELD PRIMARY KEY (IBLOCK_ID, FIELD_ID)
)
/
CREATE TABLE b_lists_socnet_group
(
	IBLOCK_ID NUMBER(18) NOT NULL,
	SOCNET_ROLE CHAR(1 CHAR),
	PERMISSION CHAR(1 CHAR) NOT NULL
)
/
CREATE UNIQUE INDEX ux_b_lists_socnet_group_1 ON b_lists_socnet_group(IBLOCK_ID, SOCNET_ROLE)
/
CREATE TABLE b_lists_url
(
	IBLOCK_ID number(18) not null,
	URL varchar2(500 char) not null,
	CONSTRAINT PK_B_LISTS_URL PRIMARY KEY (IBLOCK_ID)
)
/
