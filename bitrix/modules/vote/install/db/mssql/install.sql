CREATE TABLE B_VOTE_CHANNEL
(
	ID int NOT NULL IDENTITY (1, 1),
	SYMBOLIC_NAME varchar(255) NOT NULL,
	C_SORT int NULL,
	FIRST_SITE_ID char(2) NULL,
	ACTIVE char(1) NOT NULL,
	HIDDEN char(1) NOT NULL,
	TIMESTAMP_X datetime NOT NULL,
	TITLE varchar(255) NOT NULL, 
	VOTE_SINGLE char(1) NOT NULL,
    USE_CAPTCHA char(1) NOT NULL
)
GO
ALTER TABLE B_VOTE_CHANNEL ADD CONSTRAINT PK_B_VOTE_CHANNEL PRIMARY KEY (ID)
GO
ALTER TABLE B_VOTE_CHANNEL ADD CONSTRAINT DF_B_VOTE_CHANNEL_C_SORT DEFAULT '100' FOR C_SORT
GO
ALTER TABLE B_VOTE_CHANNEL ADD CONSTRAINT DF_B_VOTE_CHANNEL_ACTIVE DEFAULT 'Y' FOR ACTIVE
GO
ALTER TABLE B_VOTE_CHANNEL ADD CONSTRAINT DF_B_VOTE_CHANNEL_HIDDEN DEFAULT 'N' FOR HIDDEN
GO
ALTER TABLE B_VOTE_CHANNEL ADD CONSTRAINT DF_B_VOTE_CHANNEL_VOTE_SINGLE DEFAULT 'Y' FOR VOTE_SINGLE
GO
ALTER TABLE B_VOTE_CHANNEL ADD CONSTRAINT DF_B_VOTE_CHANNEL_USE_CAPTCHA DEFAULT 'N' FOR USE_CAPTCHA
GO

CREATE TABLE B_VOTE_CHANNEL_2_GROUP
(
	ID int NOT NULL IDENTITY (1, 1),
	CHANNEL_ID int NOT NULL,
	GROUP_ID int NOT NULL,
	PERMISSION int NOT NULL
)
GO
ALTER TABLE B_VOTE_CHANNEL_2_GROUP ADD CONSTRAINT PK_B_VOTE_CHANNEL_2_GROUP PRIMARY KEY (ID)
GO
ALTER TABLE B_VOTE_CHANNEL_2_GROUP ADD CONSTRAINT DF_B_VOTE_CHANNEL_2_GROUP_PERMISSION DEFAULT '0' FOR PERMISSION
GO

CREATE TABLE B_VOTE_CHANNEL_2_SITE
(
	CHANNEL_ID int NOT NULL,
	SITE_ID char(2) NOT NULL
)
GO
ALTER TABLE B_VOTE_CHANNEL_2_SITE ADD CONSTRAINT PK_B_VOTE_CHANNEL_2_SITE PRIMARY KEY (CHANNEL_ID, SITE_ID)
GO

CREATE TABLE B_VOTE
(
	ID int NOT NULL IDENTITY (1, 1),
	CHANNEL_ID int NOT NULL,
	C_SORT int NULL,
	ACTIVE char(1) NOT NULL,
	NOTIFY char(1) NOT NULL,
	AUTHOR_ID int NULL,
	TIMESTAMP_X datetime NOT NULL,
	DATE_START datetime NOT NULL,
	DATE_END datetime NOT NULL,
	URL varchar(255) NULL,
	COUNTER int NOT NULL,
	TITLE varchar(255),
	DESCRIPTION varchar(5000),
	DESCRIPTION_TYPE varchar(4) NOT NULL,
	IMAGE_ID int NULL,
	EVENT1 varchar(255) NULL,
	EVENT2 varchar(255) NULL,
	EVENT3 varchar(255) NULL,
	UNIQUE_TYPE int NOT NULL,
	KEEP_IP_SEC int NULL,
	DELAY int NULL,
	DELAY_TYPE char(1) NULL,
	TEMPLATE varchar(255) NULL,
	RESULT_TEMPLATE varchar(255) NULL
)
GO
ALTER TABLE B_VOTE ADD CONSTRAINT PK_B_VOTE PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_VOTE_1 ON B_VOTE (CHANNEL_ID)
GO
ALTER TABLE B_VOTE ADD CONSTRAINT DF_B_VOTE_C_SORT DEFAULT '100' FOR C_SORT
GO
ALTER TABLE B_VOTE ADD CONSTRAINT DF_B_VOTE_ACTIVE DEFAULT 'Y' FOR ACTIVE
GO
ALTER TABLE B_VOTE ADD CONSTRAINT DF_B_VOTE_COUNTER DEFAULT '0' FOR COUNTER
GO
ALTER TABLE B_VOTE ADD CONSTRAINT DF_B_VOTE_DESCRIPTION_TYPE DEFAULT 'html' FOR DESCRIPTION_TYPE
GO
ALTER TABLE B_VOTE ADD CONSTRAINT DF_B_VOTE_UNIQUE_TYPE DEFAULT '2' FOR UNIQUE_TYPE
GO
ALTER TABLE B_VOTE ADD CONSTRAINT DF_B_VOTE_NOTIFY DEFAULT 'N' FOR NOTIFY
GO

create trigger B_VOTE_UPDATE on B_VOTE for update as
begin

	declare 
		@IMAGE_ID_OLD int, 
		@IMAGE_ID_NEW int

	declare cCursor cursor for
		SELECT
			D.IMAGE_ID					IMAGE_ID_OLD,
			I.IMAGE_ID					IMAGE_ID_NEW
		FROM
			INSERTED I,
			DELETED D
		WHERE 
			I.ID = D.ID
			
	open cCursor

	while 1=1
	begin
		fetch next from cCursor into 
			@IMAGE_ID_OLD,
			@IMAGE_ID_NEW

		if @@fetch_status<>0
			break

		exec DELFILE @IMAGE_ID_OLD, @IMAGE_ID_NEW

	end
	close cCursor
	deallocate cCursor
end

if @@error <>0 
begin
	raiserror ('Trigger B_VOTE_UPDATE Error',16,1)
end
GO

create trigger B_VOTE_DELETE on B_VOTE for delete as
declare 
	@IMAGE_ID int

declare cCursor cursor for
	SELECT
		D.IMAGE_ID
	FROM
		DELETED D

open cCursor

while 1=1
begin
	fetch next from cCursor into 
		@IMAGE_ID

	if @@fetch_status<>0
		break

	exec DELFILE @IMAGE_ID, null

end
close cCursor
deallocate cCursor

if @@error <>0 
begin
	raiserror ('Trigger B_VOTE_DELETE Error',16,1)
end
GO


CREATE TABLE B_VOTE_QUESTION
(
	ID int NOT NULL IDENTITY (1, 1),
	ACTIVE char(1) NOT NULL,
	TIMESTAMP_X datetime NOT NULL,
	VOTE_ID int NOT NULL,
	C_SORT int NULL,
	COUNTER int NOT NULL,
	QUESTION varchar(5000) NOT NULL,
	QUESTION_TYPE varchar(4) NOT NULL,
	IMAGE_ID int NULL,
	DIAGRAM char(1) NOT NULL,
	REQUIRED char(1) NOT NULL,
	DIAGRAM_TYPE VARCHAR(10) NOT NULL DEFAULT 'histogram',
	TEMPLATE varchar(255) NULL,
	TEMPLATE_NEW varchar(255) NULL
)
GO
ALTER TABLE B_VOTE_QUESTION ADD CONSTRAINT PK_B_VOTE_QUESTION PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_VOTE_QUESTION_1 ON B_VOTE_QUESTION (VOTE_ID)
GO
ALTER TABLE B_VOTE_QUESTION ADD CONSTRAINT DF_B_VOTE_QUESTION_ACTIVE DEFAULT 'Y' FOR ACTIVE
GO
ALTER TABLE B_VOTE_QUESTION ADD CONSTRAINT DF_B_VOTE_QUESTION_C_SORT DEFAULT '100' FOR C_SORT
GO
ALTER TABLE B_VOTE_QUESTION ADD CONSTRAINT DF_B_VOTE_QUESTION_COUNTER DEFAULT '0' FOR COUNTER
GO
ALTER TABLE B_VOTE_QUESTION ADD CONSTRAINT DF_B_VOTE_QUESTION_QUESTION_TYPE DEFAULT 'html' FOR QUESTION_TYPE
GO
ALTER TABLE B_VOTE_QUESTION ADD CONSTRAINT DF_B_VOTE_QUESTION_DIAGRAM DEFAULT 'Y' FOR DIAGRAM
GO
ALTER TABLE B_VOTE_QUESTION ADD CONSTRAINT DF_B_VOTE_QUESTION_REQUIRED DEFAULT 'N' FOR REQUIRED
GO
ALTER TABLE B_VOTE_QUESTION ADD CONSTRAINT FK_B_VOTE_QUESTION_B_VOTE FOREIGN KEY (VOTE_ID) REFERENCES B_VOTE(ID) ON DELETE CASCADE
GO

create trigger B_VOTE_QUESTION_UPDATE on B_VOTE_QUESTION for update as
begin

	declare 
		@IMAGE_ID_OLD int, 
		@IMAGE_ID_NEW int

	declare cCursor cursor for
		SELECT
			D.IMAGE_ID		IMAGE_ID_OLD,
			I.IMAGE_ID		IMAGE_ID_NEW
		FROM
			INSERTED I,
			DELETED D
		WHERE 
			I.ID = D.ID
			
	open cCursor

	while 1=1
	begin
		fetch next from cCursor into 
			@IMAGE_ID_OLD,
			@IMAGE_ID_NEW

		if @@fetch_status<>0
			break

		exec DELFILE @IMAGE_ID_OLD, @IMAGE_ID_NEW

	end
	close cCursor
	deallocate cCursor
end

if @@error <>0 
begin
	raiserror ('Trigger B_VOTE_QUESTION_UPDATE Error',16,1)
end
GO

create trigger B_VOTE_QUESTION_DELETE on B_VOTE_QUESTION for delete as
declare 
	@IMAGE_ID int

declare cCursor cursor for
	SELECT
		D.IMAGE_ID
	FROM
		DELETED D

open cCursor

while 1=1
begin
	fetch next from cCursor into 
		@IMAGE_ID

	if @@fetch_status<>0
		break

	exec DELFILE @IMAGE_ID, null

end
close cCursor
deallocate cCursor

if @@error <>0 
begin
	raiserror ('Trigger B_VOTE_QUESTION_DELETE Error',16,1)
end
GO


CREATE TABLE B_VOTE_ANSWER
(
	ID int NOT NULL IDENTITY (1, 1),
	ACTIVE char(1) NOT NULL,
	TIMESTAMP_X datetime NOT NULL,
	QUESTION_ID int NOT NULL,
	C_SORT int NULL,
	MESSAGE varchar(5000) NULL,
	COUNTER int NOT NULL,
	FIELD_TYPE tinyint NOT NULL,
	FIELD_WIDTH int NULL,
	FIELD_HEIGHT int NULL,
	FIELD_PARAM varchar(255) NULL,
	COLOR varchar(7) NULL
)
GO
ALTER TABLE B_VOTE_ANSWER ADD CONSTRAINT PK_B_VOTE_ANSWER PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_VOTE_ANSWER_1 ON B_VOTE_ANSWER (QUESTION_ID)
GO
ALTER TABLE B_VOTE_ANSWER ADD CONSTRAINT DF_B_VOTE_ANSWER_ACTIVE DEFAULT 'Y' FOR ACTIVE
GO
ALTER TABLE B_VOTE_ANSWER ADD CONSTRAINT DF_B_VOTE_ANSWER_C_SORT DEFAULT '100' FOR C_SORT
GO
ALTER TABLE B_VOTE_ANSWER ADD CONSTRAINT DF_B_VOTE_ANSWER_COUNTER DEFAULT '0' FOR COUNTER
GO
ALTER TABLE B_VOTE_ANSWER ADD CONSTRAINT DF_B_VOTE_ANSWER_FIELD_TYPE DEFAULT '0' FOR FIELD_TYPE
GO
ALTER TABLE B_VOTE_ANSWER ADD CONSTRAINT FK_B_VOTE_ANSWER_B_VOTE_QUESTION FOREIGN KEY (QUESTION_ID) REFERENCES B_VOTE_QUESTION(ID) ON DELETE CASCADE
GO


CREATE TABLE B_VOTE_EVENT
(
	ID int NOT NULL IDENTITY (1, 1),
	VOTE_ID int NOT NULL,
	VOTE_USER_ID int NOT NULL,
	DATE_VOTE datetime NOT NULL,
	STAT_SESSION_ID int NULL,
	IP varchar(15) NULL,
	VALID char(1) NOT NULL
)
GO
ALTER TABLE B_VOTE_EVENT ADD CONSTRAINT PK_B_VOTE_EVENT PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_VOTE_EVENT_1 ON B_VOTE_EVENT (VOTE_USER_ID)
GO
CREATE INDEX IX_B_VOTE_EVENT_2 ON B_VOTE_EVENT (VOTE_ID, IP)
GO
ALTER TABLE B_VOTE_EVENT ADD CONSTRAINT DF_B_VOTE_EVENT_VALID DEFAULT 'Y' FOR VALID
GO


CREATE TABLE B_VOTE_EVENT_QUESTION
(
	ID int NOT NULL IDENTITY (1, 1),
	EVENT_ID int NOT NULL,
	QUESTION_ID int NOT NULL
)
GO
ALTER TABLE B_VOTE_EVENT_QUESTION ADD CONSTRAINT PK_B_VOTE_EVENT_QUESTION PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_VOTE_EVENT_QUESTION_1 ON B_VOTE_EVENT_QUESTION (EVENT_ID)
GO
ALTER TABLE B_VOTE_EVENT_QUESTION ADD CONSTRAINT FK_B_VOTE_EVENT_QUESTION_B_VOTE_EVENT FOREIGN KEY (EVENT_ID) REFERENCES B_VOTE_EVENT(ID) ON DELETE CASCADE
GO


CREATE TABLE B_VOTE_EVENT_ANSWER
(
	ID int NOT NULL IDENTITY (1, 1),
	EVENT_QUESTION_ID int NOT NULL,
	ANSWER_ID int NOT NULL,
	MESSAGE varchar(8000) NULL
)
GO
ALTER TABLE B_VOTE_EVENT_ANSWER ADD CONSTRAINT PK_B_VOTE_EVENT_ANSWER PRIMARY KEY (ID)
GO
CREATE INDEX IX_B_VOTE_EVENT_ANSWER_1 ON B_VOTE_EVENT_ANSWER (EVENT_QUESTION_ID)
GO
ALTER TABLE B_VOTE_EVENT_ANSWER ADD CONSTRAINT FK_B_VOTE_EVENT_ANSWER_B_VOTE_EVENT_QUESTION FOREIGN KEY (EVENT_QUESTION_ID) REFERENCES B_VOTE_EVENT_QUESTION(ID) ON DELETE CASCADE
GO


CREATE TABLE B_VOTE_USER
(
	ID int NOT NULL IDENTITY (1, 1),
	STAT_GUEST_ID int NULL,
	AUTH_USER_ID int NULL,
	COUNTER int NOT NULL,
	DATE_FIRST datetime NOT NULL,
	DATE_LAST datetime NOT NULL,
	LAST_IP varchar(15) NULL
)
GO
ALTER TABLE B_VOTE_USER ADD CONSTRAINT PK_B_VOTE_USER PRIMARY KEY (ID)
GO
ALTER TABLE B_VOTE_USER ADD CONSTRAINT DF_B_VOTE_USER_COUNTER DEFAULT '0' FOR COUNTER
GO
