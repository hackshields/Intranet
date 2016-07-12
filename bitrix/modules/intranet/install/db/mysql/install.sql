create table if not exists b_intranet_sharepoint
(
	IBLOCK_ID int(11) not null,
	SP_LIST_ID varchar(32) not null,
	SP_URL varchar(255) not null,
	SP_AUTH_USER varchar(50) null default '',
	SP_AUTH_PASS varchar(50) null default '',
	SYNC_DATE datetime,
	SYNC_PERIOD int(11) null default 86400,
	SYNC_ERRORS int(1) null default 0,
	SYNC_LAST_TOKEN varchar(100) null default '',
	SYNC_PAGING varchar(100) null default '',
	HANDLER_MODULE varchar(50) null default '',
	HANDLER_CLASS varchar(100) null default '',
	PRIORITY char(1) null default 'B',
	PRIMARY KEY pk_b_intranet_sharepoint (IBLOCK_ID)
);

create table if not exists b_intranet_sharepoint_field
(
	IBLOCK_ID int(11) not null,
	FIELD_ID varchar(50) not null,
	SP_FIELD varchar(50) not null,
	SP_FIELD_TYPE varchar(50) not null,
	SETTINGS text null default '',
	PRIMARY KEY pk_b_intranet_sharepoint_field (IBLOCK_ID, FIELD_ID)
);

create table if not exists b_intranet_sharepoint_queue
(
	ID int(11) not null auto_increment,
	IBLOCK_ID int(11) not null,
	SP_METHOD varchar(100) not null,
	SP_METHOD_PARAMS text null default '',
	CALLBACK text null default '',
	PRIMARY KEY pk_b_intranet_sharepoint_queue (ID),
	INDEX ix_b_intranet_sharepoint_queue_1 (IBLOCK_ID)
);

create table if not exists b_intranet_sharepoint_log
(
	ID int(11) not null auto_increment,
	IBLOCK_ID int(11) not null,
	ELEMENT_ID int(11) not null,
	VERSION int(5) null default 0,
	PRIMARY KEY pk_b_intranet_sharepoint_log (ID),
	UNIQUE INDEX ui_b_intranet_sharepoint_log (IBLOCK_ID, ELEMENT_ID)
);

create table if not exists b_rating_subordinate (
  ID int(11) NOT NULL auto_increment,
  RATING_ID int(11) NOT NULL,
  ENTITY_ID int(11) NOT NULL,
  VOTES decimal(18,4) NULL default '0.0000',
  PRIMARY KEY  (ID),
  KEY RATING_ID (RATING_ID, ENTITY_ID)
);