BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_SEARCH_USER_RIGHT CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_SEARCH_CUSTOM_RANK CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_SEARCH_CONTENT_FREQ CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_SEARCH_CONTENT_STEM CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_SEARCH_STEM CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP SEQUENCE SQ_B_SEARCH_STEM';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_SEARCH_CONTENT_TITLE CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_SEARCH_TAGS CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_SEARCH_CONTENT_PARAM CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_SEARCH_CONTENT_RIGHT CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_SEARCH_CONTENT_TEXT CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_SEARCH_CONTENT CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP SEQUENCE SQ_B_SEARCH_CONTENT';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP SEQUENCE SQ_B_SEARCH_CUSTOM_RANK';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_SEARCH_CONTENT_SITE CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'drop function F_STEM';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'drop function F_STRING_EXPLODE';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'drop type TT_STEM';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'drop type T_STEM';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'drop type T_STRING';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_SEARCH_SUGGEST CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/

BEGIN
	EXECUTE IMMEDIATE 'DROP SEQUENCE SQ_B_SEARCH_SUGGEST';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/