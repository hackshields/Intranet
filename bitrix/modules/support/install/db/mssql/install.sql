CREATE TABLE B_TICKET
(
	ID int NOT NULL IDENTITY (1, 1),
	SITE_ID char(2) NOT NULL,
	DATE_CREATE datetime NULL,
	DAY_CREATE datetime NULL,
	TIMESTAMP_X datetime NULL,
	DATE_CLOSE datetime NULL,
	AUTO_CLOSED char(1) NULL,
	AUTO_CLOSE_DAYS tinyint NULL,
	SLA_ID int NOT NULL,
	NOTIFY_AGENT_ID int NULL,
	EXPIRE_AGENT_ID int NULL,
	OVERDUE_MESSAGES int NOT NULL,
	IS_NOTIFIED char(1) NOT NULL,
	IS_OVERDUE char(1) NOT NULL,
	CATEGORY_ID int NULL,
	CRITICALITY_ID int NULL,
	STATUS_ID int NULL,
	MARK_ID int NULL,
	SOURCE_ID int NULL,
	DIFFICULTY_ID int NULL,
	TITLE varchar(2000) NOT NULL,
	MESSAGES int NOT NULL,
	IS_SPAM char(1) NULL,
	OWNER_USER_ID int NULL,
	OWNER_GUEST_ID int NULL,
	OWNER_SID varchar(8000) NULL,
	CREATED_USER_ID int NULL,
	CREATED_GUEST_ID int NULL,
	CREATED_MODULE_NAME varchar(255) NULL,
	RESPONSIBLE_USER_ID int NULL,
	MODIFIED_USER_ID int NULL,
	MODIFIED_GUEST_ID int NULL,
	MODIFIED_MODULE_NAME varchar(255) NULL,
	LAST_MESSAGE_USER_ID int NULL,
	LAST_MESSAGE_GUEST_ID int NULL,
	LAST_MESSAGE_SID varchar(8000) NULL,
	LAST_MESSAGE_BY_SUPPORT_TEAM char(1) NOT NULL,
	LAST_MESSAGE_DATE DATETIME NULL,
	SUPPORT_COMMENTS varchar(8000) NULL,
	PROBLEM_TIME int NULL,
	HOLD_ON CHAR(1) NOT NULL DEFAULT 'N',
	REOPEN CHAR(1) NOT NULL DEFAULT 'N',
	COUPON varchar(255) NULL,
	SUPPORT_DEADLINE DATETIME NULL,
	SUPPORT_DEADLINE_NOTIFY DATETIME NULL,
	D_1_USER_M_AFTER_SUP_M DATETIME NULL,
	ID_1_USER_M_AFTER_SUP_M int NULL
)
GO
ALTER TABLE B_TICKET ADD CONSTRAINT PK_B_TICKET PRIMARY KEY (ID)
GO
ALTER TABLE B_TICKET ADD CONSTRAINT DF_B_TICKET_SLA_ID DEFAULT '1' FOR SLA_ID
GO
ALTER TABLE B_TICKET ADD CONSTRAINT DF_B_TICKET_OVERDUE_MESSAGES DEFAULT '0' FOR OVERDUE_MESSAGES
GO
ALTER TABLE B_TICKET ADD CONSTRAINT DF_B_TICKET_IS_NOTIFIED DEFAULT 'N' FOR IS_NOTIFIED
GO
ALTER TABLE B_TICKET ADD CONSTRAINT DF_B_TICKET_IS_OVERDUE DEFAULT 'N' FOR IS_OVERDUE
GO
ALTER TABLE B_TICKET ADD CONSTRAINT DF_B_TICKET_MESSAGES DEFAULT '0' FOR MESSAGES
GO
ALTER TABLE B_TICKET ADD CONSTRAINT DF_B_TICKET_LAST_MESSAGE_BY_SUPPORT_TEAM DEFAULT 'N' FOR LAST_MESSAGE_BY_SUPPORT_TEAM
GO


CREATE TABLE B_TICKET_MESSAGE
(
	ID int NOT NULL IDENTITY (1, 1),
	TIMESTAMP_X datetime NULL,
	DATE_CREATE datetime NULL,
	DAY_CREATE datetime NULL,
	C_NUMBER int NULL,
	TICKET_ID int NOT NULL,
	IS_HIDDEN char(1) NOT NULL,
	IS_LOG char(1) NOT NULL,
	IS_OVERDUE char(1) NOT NULL,
	CURRENT_RESPONSIBLE_USER_ID int NULL,
	NOTIFY_AGENT_DONE char(1) NOT NULL,
	EXPIRE_AGENT_DONE char(1) NOT NULL,
	MESSAGE text NULL,
	MESSAGE_SEARCH text NULL,
	IS_SPAM char(1) NULL,
	EXTERNAL_ID int NULL,
	EXTERNAL_FIELD_1 varchar(8000) NULL,
	OWNER_USER_ID int NULL,
	OWNER_GUEST_ID int NULL,
	OWNER_SID varchar(8000) NULL,
	SOURCE_ID int NULL,
	CREATED_USER_ID int NULL,
	CREATED_GUEST_ID int NULL,
	CREATED_MODULE_NAME varchar(255) NULL,
	MODIFIED_USER_ID int NULL,
	MODIFIED_GUEST_ID int NULL,
	MESSAGE_BY_SUPPORT_TEAM char(1) NULL,
	TASK_TIME int NULL,
	NOT_CHANGE_STATUS CHAR(1) NOT NULL DEFAULT 'N'
)
GO
ALTER TABLE B_TICKET_MESSAGE ADD CONSTRAINT PK_B_TICKET_MESSAGE PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_TICKET_MESSAGE_1 ON B_TICKET_MESSAGE (TICKET_ID)
GO
ALTER TABLE B_TICKET_MESSAGE ADD CONSTRAINT DF_B_TICKET_MESSAGE_IS_HIDDEN DEFAULT 'N' FOR IS_HIDDEN
GO
ALTER TABLE B_TICKET_MESSAGE ADD CONSTRAINT DF_B_TICKET_MESSAGE_IS_LOG DEFAULT 'N' FOR IS_LOG
GO
ALTER TABLE B_TICKET_MESSAGE ADD CONSTRAINT DF_B_TICKET_MESSAGE_IS_OVERDUE DEFAULT 'N' FOR IS_OVERDUE
GO
ALTER TABLE B_TICKET_MESSAGE ADD CONSTRAINT DF_B_TICKET_MESSAGE_NOTIFY_AGENT_DONE DEFAULT 'N' FOR NOTIFY_AGENT_DONE
GO
ALTER TABLE B_TICKET_MESSAGE ADD CONSTRAINT DF_B_TICKET_MESSAGE_EXPIRE_AGENT_DONE DEFAULT 'N' FOR EXPIRE_AGENT_DONE
GO


CREATE TABLE B_TICKET_MESSAGE_2_FILE
(
	ID int NOT NULL IDENTITY (1, 1),
	HASH varchar(255),
	MESSAGE_ID int NOT NULL,
	FILE_ID int NOT NULL,
	TICKET_ID int NOT NULL,
	EXTENSION_SUFFIX varchar(255)
)
GO
ALTER TABLE B_TICKET_MESSAGE_2_FILE ADD CONSTRAINT PK_B_TICKET_MESSAGE_2_FILE PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_TICKET_MESSAGE_2_FILE_1 ON B_TICKET_MESSAGE_2_FILE (HASH)
GO
CREATE INDEX IX_B_TICKET_MESSAGE_2_FILE_2 ON B_TICKET_MESSAGE_2_FILE (MESSAGE_ID)
GO


CREATE TABLE B_TICKET_DICTIONARY
(
	ID int NOT NULL IDENTITY (1, 1),
	FIRST_SITE_ID char(2) NULL,
	C_TYPE varchar(5) NOT NULL,
	SID varchar(255) NULL,
	SET_AS_DEFAULT char(1) NULL,
	C_SORT int NULL,
	NAME varchar(255) NOT NULL,
	DESCR varchar(8000) NULL,
	RESPONSIBLE_USER_ID int NULL,
	EVENT1 varchar(255) NULL,
	EVENT2 varchar(255) NULL,
	EVENT3 varchar(255) NULL
)
GO
ALTER TABLE B_TICKET_DICTIONARY ADD CONSTRAINT PK_B_TICKET_DICTIONARY PRIMARY KEY (ID)
GO
ALTER TABLE B_TICKET_DICTIONARY ADD CONSTRAINT DF_B_TICKET_DICTIONARY_C_SORT DEFAULT '100' FOR C_SORT
GO
ALTER TABLE B_TICKET_DICTIONARY ADD CONSTRAINT DF_B_TICKET_DICTIONARY_EVENT1 DEFAULT 'ticket' FOR EVENT1
GO


CREATE TABLE B_TICKET_DICTIONARY_2_SITE
(
	DICTIONARY_ID int NOT NULL,
	SITE_ID char(2) NOT NULL
)
GO
ALTER TABLE B_TICKET_DICTIONARY_2_SITE ADD CONSTRAINT PK_B_TICKET_DICTIONARY_2_SITE PRIMARY KEY (DICTIONARY_ID, SITE_ID)
GO


CREATE TABLE B_TICKET_ONLINE
(
	ID int NOT NULL IDENTITY (1, 1),
	TIMESTAMP_X datetime NULL,
	TICKET_ID int NULL,
	USER_ID int NULL,
	CURRENT_MODE varchar(20) NULL
)
GO
ALTER TABLE B_TICKET_ONLINE ADD CONSTRAINT PK_B_TICKET_ONLINE PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_TICKET_ONLINE_1 ON B_TICKET_ONLINE (TICKET_ID)
GO


CREATE TABLE B_TICKET_SLA
(
	ID int NOT NULL IDENTITY (1, 1),
	PRIORITY int NOT NULL,
	FIRST_SITE_ID varchar(5) NULL,
	NAME varchar(255) NOT NULL,
	DESCRIPTION varchar(8000) NULL,
	RESPONSE_TIME int NULL,
	RESPONSE_TIME_UNIT varchar(10) NOT NULL,
	NOTICE_TIME int NULL,
	NOTICE_TIME_UNIT varchar(10) NOT NULL,
	RESPONSIBLE_USER_ID int NULL,
	DATE_CREATE datetime NULL,
	CREATED_USER_ID int NULL,
	CREATED_GUEST_ID int NULL,
	DATE_MODIFY datetime NULL,
	MODIFIED_USER_ID int NULL,
	MODIFIED_GUEST_ID int NULL,
	TIMETABLE_ID int NULL
)
GO
ALTER TABLE B_TICKET_SLA ADD CONSTRAINT PK_B_TICKET_SLA PRIMARY KEY (ID)
GO
ALTER TABLE B_TICKET_SLA ADD CONSTRAINT DF_B_TICKET_SLA_PRIORITY DEFAULT '0' FOR PRIORITY
GO
ALTER TABLE B_TICKET_SLA ADD CONSTRAINT DF_B_TICKET_SLA_RESPONSE_TIME_UNIT DEFAULT 'hour' FOR RESPONSE_TIME_UNIT
GO
ALTER TABLE B_TICKET_SLA ADD CONSTRAINT DF_B_TICKET_SLA_NOTICE_TIME_UNIT DEFAULT 'hour' FOR NOTICE_TIME_UNIT
GO


CREATE TABLE B_TICKET_SLA_2_SITE
(
	SLA_ID int NOT NULL,  
	SITE_ID varchar(5) NOT NULL
)
GO
ALTER TABLE B_TICKET_SLA_2_SITE ADD CONSTRAINT PK_B_TICKET_SLA_2_SITE PRIMARY KEY (SLA_ID, SITE_ID)
GO


CREATE TABLE B_TICKET_SLA_2_CATEGORY
(
	SLA_ID int NOT NULL,
	CATEGORY_ID int NOT NULL
)
GO
ALTER TABLE B_TICKET_SLA_2_CATEGORY ADD CONSTRAINT PK_B_TICKET_SLA_2_CATEGORY PRIMARY KEY (SLA_ID, CATEGORY_ID)
GO


CREATE TABLE B_TICKET_SLA_2_CRITICALITY
(
	SLA_ID int NOT NULL,
	CRITICALITY_ID int NOT NULL
)
GO
ALTER TABLE B_TICKET_SLA_2_CRITICALITY ADD CONSTRAINT PK_B_TICKET_SLA_2_CRITICALITY PRIMARY KEY (SLA_ID, CRITICALITY_ID)
GO


CREATE TABLE B_TICKET_SLA_2_MARK
(
	SLA_ID int NOT NULL,
	MARK_ID int NOT NULL
)
GO
ALTER TABLE B_TICKET_SLA_2_MARK ADD CONSTRAINT PK_B_TICKET_SLA_2_MARK PRIMARY KEY (SLA_ID, MARK_ID)
GO


CREATE TABLE B_TICKET_SLA_2_USER_GROUP
(
	SLA_ID int NOT NULL,
	GROUP_ID int NOT NULL
)
GO
ALTER TABLE B_TICKET_SLA_2_USER_GROUP ADD CONSTRAINT PK_B_TICKET_SLA_2_USER_GROUP PRIMARY KEY (SLA_ID, GROUP_ID)
GO


CREATE TABLE B_TICKET_SLA_SHEDULE
(
	ID int NOT NULL IDENTITY (1, 1),
	SLA_ID int NOT NULL,
	WEEKDAY_NUMBER int NOT NULL,
	OPEN_TIME varchar(10) NOT NULL,
	MINUTE_FROM int NULL,
	MINUTE_TILL int NULL,
	TIMETABLE_ID int NULL,
)
GO
ALTER TABLE B_TICKET_SLA_SHEDULE ADD CONSTRAINT PK_B_TICKET_SLA_SHEDULE PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_TICKET_SLA_SHEDULE_1 ON B_TICKET_SLA_SHEDULE (SLA_ID)
GO
ALTER TABLE B_TICKET_SLA_SHEDULE ADD CONSTRAINT DF_B_TICKET_SLA_SHEDULE_WEEKDAY_NUMBER DEFAULT '0' FOR WEEKDAY_NUMBER
GO
ALTER TABLE B_TICKET_SLA_SHEDULE ADD CONSTRAINT DF_B_TICKET_SLA_SHEDULE_OPEN_TIME DEFAULT '24H' FOR OPEN_TIME
GO

CREATE TABLE B_TICKET_UGROUPS
(
	ID int NOT NULL IDENTITY (1, 1),
	NAME varchar(255) NOT NULL,
	XML_ID varchar(255) NULL,
	SORT int NOT NULL,
	IS_TEAM_GROUP char(1) NOT NULL
)
GO
ALTER TABLE B_TICKET_UGROUPS ADD CONSTRAINT PK_B_TICKET_UGROUPS PRIMARY KEY (ID)
GO
ALTER TABLE B_TICKET_UGROUPS ADD CONSTRAINT DF_B_TICKET_UGROUPS_SORT DEFAULT '100' FOR SORT
GO
ALTER TABLE B_TICKET_UGROUPS ADD CONSTRAINT DF_B_TICKET_UGROUPS_IS_TEAM_GROUP DEFAULT 'N' FOR IS_TEAM_GROUP
GO

CREATE TABLE B_TICKET_USER_UGROUP
(
	GROUP_ID int NOT NULL,
	USER_ID int NOT NULL,
	CAN_VIEW_GROUP_MESSAGES char(1) NOT NULL,
	CAN_MAIL_GROUP_MESSAGES char(1) NOT NULL,
	CAN_MAIL_UPDATE_GROUP_MESSAGES char(1) NOT NULL
)
GO
ALTER TABLE B_TICKET_USER_UGROUP ADD CONSTRAINT PK_B_TICKET_USER_UGROUP PRIMARY KEY (GROUP_ID, USER_ID)
GO
ALTER TABLE B_TICKET_USER_UGROUP ADD CONSTRAINT DF_B_TICKET_USER_UGROUP_1 DEFAULT 'N' FOR CAN_VIEW_GROUP_MESSAGES
GO
ALTER TABLE B_TICKET_USER_UGROUP ADD CONSTRAINT DF_B_TICKET_USER_UGROUP_2 DEFAULT 'N' FOR CAN_MAIL_GROUP_MESSAGES
GO
ALTER TABLE B_TICKET_USER_UGROUP ADD CONSTRAINT DF_B_TICKET_USER_UGROUP_3 DEFAULT 'N' FOR CAN_MAIL_UPDATE_GROUP_MESSAGES
GO

CREATE TABLE B_TICKET_SUPERCOUPONS
(
  ID int NOT NULL IDENTITY (1, 1),
  COUNT_TICKETS int NOT NULL,
  COUPON varchar(255) NOT NULL,
  TIMESTAMP_X datetime NOT NULL,
  DATE_CREATE datetime NOT NULL,
  CREATED_USER_ID int NULL,
  UPDATED_USER_ID int NULL,
  ACTIVE char(1) NOT NULL,
  ACTIVE_FROM datetime NULL,
  ACTIVE_TO datetime NULL,
  SLA_ID int NULL,
  COUNT_USED int NOT NULL
)
GO
ALTER TABLE B_TICKET_SUPERCOUPONS ADD CONSTRAINT PK_B_TICKET_SUPERCOUPONS PRIMARY KEY (ID)
GO
ALTER TABLE B_TICKET_SUPERCOUPONS ADD CONSTRAINT DF_B_TICKET_SUPERCOUPONS_COUNT_TICKETS DEFAULT '0' FOR COUNT_TICKETS
GO
ALTER TABLE B_TICKET_SUPERCOUPONS ADD CONSTRAINT DF_B_TICKET_SUPERCOUPONS_ACTIVE DEFAULT 'Y' FOR ACTIVE
GO
ALTER TABLE B_TICKET_SUPERCOUPONS ADD CONSTRAINT IX_B_TICKET_SUPERCOUPONS_COUPON UNIQUE NONCLUSTERED (COUPON ASC) WITH (IGNORE_DUP_KEY = OFF)
GO
ALTER TABLE B_TICKET_SUPERCOUPONS ADD CONSTRAINT DF_B_TICKET_SUPERCOUPONS_COUNT_USED DEFAULT '0' FOR COUNT_USED
GO

CREATE TABLE B_TICKET_SUPERCOUPONS_LOG
(
  TIMESTAMP_X datetime NOT NULL,
  COUPON_ID int NOT NULL,
  USER_ID int NULL,
  SUCCESS char(1) NOT NULL,
  AFTER_COUNT int NOT NULL,
  SESSION_ID int NULL,
  GUEST_ID int NULL,
  AFFECTED_ROWS int NULL,
  COUPON varchar(255) NULL
)
GO
ALTER TABLE B_TICKET_SUPERCOUPONS_LOG ADD CONSTRAINT DF_B_TICKET_SUPERCOUPONS_LOG_SUCCESS DEFAULT 'N' FOR SUCCESS
GO
ALTER TABLE B_TICKET_SUPERCOUPONS_LOG ADD CONSTRAINT DF_B_TICKET_SUPERCOUPONS_LOG_COUPON_ID DEFAULT '0' FOR COUPON_ID
GO
ALTER TABLE B_TICKET_SUPERCOUPONS_LOG ADD CONSTRAINT DF_B_TICKET_SUPERCOUPONS_LOG_AFTER_COUNT DEFAULT '0' FOR AFTER_COUNT
GO
CREATE INDEX IX_B_TICKET_SUPERCOUPONS_LOG_COUPON_ID ON B_TICKET_SUPERCOUPONS_LOG (COUPON_ID)
GO

CREATE TABLE b_ticket_timetable
(
ID int NOT NULL IDENTITY (1, 1),
NAME varchar(255) NOT NULL,
DESCRIPTION TEXT NULL
)
GO
ALTER TABLE b_ticket_timetable ADD CONSTRAINT PK_b_ticket_timetable PRIMARY KEY (ID)
GO

CREATE TABLE b_ticket_holidays
(
ID int NOT NULL IDENTITY (1, 1),
NAME varchar(255) NOT NULL,
DESCRIPTION TEXT NULL,
OPEN_TIME varchar(255) NOT NULL,
DATE_FROM datetime NOT NULL,
DATE_TILL datetime NOT NULL
)
GO
ALTER TABLE b_ticket_holidays ADD CONSTRAINT PK_b_ticket_holidays PRIMARY KEY (ID)
GO
ALTER TABLE b_ticket_holidays ADD CONSTRAINT DF_b_ticket_holidays_OPEN_TIME DEFAULT 'HOLIDAY' FOR OPEN_TIME
GO

CREATE TABLE b_ticket_sla_2_holidays
(
SLA_ID int NOT NULL,
HOLIDAYS_ID int NOT NULL
)

CREATE TABLE b_ticket_search
(
MESSAGE_ID int NOT NULL,
SEARCH_WORD varchar(70) NOT NULL
)
GO

CREATE INDEX UX_b_ticket_search ON b_ticket_search (SEARCH_WORD)
GO


CREATE TABLE b_ticket_timetable_cache
(
ID int NOT NULL IDENTITY (1, 1),
SLA_ID int NOT NULL,
DATE_FROM datetime NOT NULL,
DATE_TILL datetime NOT NULL,
W_TIME int NOT NULL,
W_TIME_INC int NOT NULL
)
GO
ALTER TABLE b_ticket_timetable_cache ADD CONSTRAINT PK_b_ticket_timetable_cache PRIMARY KEY (ID)
GO


