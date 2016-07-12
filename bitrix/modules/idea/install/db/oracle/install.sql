CREATE TABLE b_idea_email_subscribe
(
	ID VARCHAR2(25 CHAR) NOT NULL,
	USER_ID NUMBER(18) NOT NULL
)
/

CREATE INDEX ix_idea_email_subscribe ON b_idea_email_subscribe(ID, USER_ID)
/