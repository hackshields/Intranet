INSERT INTO b_adv_contract (ID,	ACTIVE,	NAME, SORT, DESCRIPTION, EMAIL_COUNT, DATE_MODIFY) VALUES (1, 'Y', 'Default', '10000', 'all site without any restrictions', 1, sysdate)
/
DROP SEQUENCE SQ_B_ADV_CONTRACT
/
CREATE SEQUENCE SQ_B_ADV_CONTRACT START WITH 2 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

INSERT INTO b_adv_type (SID, ACTIVE, SORT, NAME, DATE_MODIFY) VALUES ('ALL', 'Y', 0, null, sysdate)
/
INSERT INTO b_adv_type (SID, ACTIVE, SORT, NAME, DATE_MODIFY) VALUES ('TOP', 'Y', 100, 'Top banner', sysdate)
/
INSERT INTO b_adv_type (SID, ACTIVE, SORT, NAME, DATE_MODIFY) VALUES ('LEFT', 'Y', 200, 'Left banner', sysdate)
/
INSERT INTO b_adv_type (SID, ACTIVE, SORT, NAME, DATE_MODIFY) VALUES ('BOTTOM', 'Y', 300, 'Bottom banner', sysdate)
/

INSERT INTO b_adv_contract_2_type (CONTRACT_ID, TYPE_SID) VALUES (1, 'ALL')
/

INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 0)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 1)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 2)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 3)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 4)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 5)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 6)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 7)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 8)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 9)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 10)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 11)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 12)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 13)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 14)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 15)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 16)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 17)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 18)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 19)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 20)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 21)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 22)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'FRIDAY', 23)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 0)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 1)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 2)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 3)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 4)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 5)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 6)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 7)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 8)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 9)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 10)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 11)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 12)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 13)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 14)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 15)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 16)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 17)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 18)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 19)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 20)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 21)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 22)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'MONDAY', 23)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 0)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 1)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 2)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 3)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 4)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 5)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 6)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 7)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 8)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 9)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 10)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 11)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 12)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 13)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 14)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 15)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 16)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 17)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 18)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 19)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 20)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 21)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 22)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SATURDAY', 23)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 0)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 1)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 2)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 3)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 4)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 5)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 6)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 7)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 8)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 9)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 10)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 11)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 12)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 13)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 14)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 15)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 16)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 17)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 18)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 19)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 20)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 21)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 22)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'SUNDAY', 23)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 0)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 1)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 2)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 3)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 4)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 5)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 6)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 7)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 8)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 9)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 10)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 11)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 12)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 13)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 14)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 15)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 16)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 17)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 18)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 19)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 20)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 21)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 22)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'THURSDAY', 23)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 0)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 1)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 2)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 3)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 4)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 5)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 6)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 7)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 8)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 9)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 10)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 11)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 12)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 13)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 14)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 15)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 16)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 17)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 18)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 19)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 20)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 21)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 22)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'TUESDAY', 23)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 0)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 1)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 2)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 3)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 4)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 5)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 6)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 7)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 8)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 9)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 10)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 11)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 12)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 13)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 14)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 15)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 16)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 17)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 18)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 19)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 20)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 21)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 22)
/
INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (1, 'WEDNESDAY', 23)
/