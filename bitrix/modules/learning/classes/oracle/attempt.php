<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/classes/general/attempt.php");

// 2012-04-14 Checked/modified for compatibility with new data model
class CTestAttempt extends CAllTestAttempt
{
	// 2012-04-13 Checked/modified for compatibility with new data model
	function DoInsert($arInsert, $arFields)
	{
		global $DB;

		if (strlen($arInsert[0]) <= 0 || strlen($arInsert[0])<= 0)		// BUG ?
			return false;

		$ID = $DB->NextID("sq_b_learn_attempt");

		$strSql =
			"INSERT INTO b_learn_attempt(ID, DATE_START, ".$arInsert[0].") ".
			"VALUES(".$ID.", ".$DB->CurrentTimeFunction().", ".$arInsert[1].")";

		$arBinds=Array(
			//""=>$arFields[""],
		);

		if($DB->QueryBind($strSql, $arBinds, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return $ID;

		return false;
	}


	// 2012-04-14 Checked/modified for compatibility with new data model
	public static function _CreateAttemptQuestionsSQLFormer($ATTEMPT_ID, $arTest, $clauseAllChildsLessons, $courseLessonId)
	{
		$strSql =
		"INSERT INTO b_learn_test_result (ATTEMPT_ID, QUESTION_ID) 
		SELECT " . ($ATTEMPT_ID + 0) . " ,Q.ID 
		FROM b_learn_lesson L 
		INNER JOIN b_learn_question Q ON L.ID = Q.LESSON_ID 
		WHERE (L.ID IN (" . $clauseAllChildsLessons . ") OR (L.ID = " . ($courseLessonId + 0) . ") ) 
		AND Q.ACTIVE = 'Y' "
		. ($arTest["QUESTIONS_AMOUNT"] > 0 ? "AND ROWNUM <= ".$arTest["QUESTIONS_AMOUNT"]." " :"").
		($arTest["INCLUDE_SELF_TEST"] != "Y" ? "AND Q.SELF = 'N' " : "").
		"ORDER BY ".($arTest["RANDOM_QUESTIONS"] == "Y" ? CTest::GetRandFunction() : "Q.SORT");

		return ($strSql);
	}


	// 2012-04-14 Checked/modified for compatibility with new data model
	public static function CreateAttemptQuestions($ATTEMPT_ID)
	{
		// This function generates database-specific SQL code
		$arCallbackSqlFormer = array ('CTestAttempt', '_CreateAttemptQuestionsSQLFormer');

		return (self::_CreateAttemptQuestions($arCallbackSqlFormer, $ATTEMPT_ID));
	}


	// 2012-04-13 Checked/modified for compatibility with new data model
	final protected static function _GetListSQLFormer ($sSelect, $obUserFieldsSql, $bCheckPerm, $USER, $arFilter, $strSqlSearch)
	{
		$oPermParser = new CLearnParsePermissionsFromFilter ($arFilter);
		
		$strSql =
		"SELECT ".
		$sSelect." ".
		$obUserFieldsSql->GetSelect()." ".
		"FROM b_learn_attempt A ".
		"INNER JOIN b_learn_test T ON A.TEST_ID = T.ID ".
		"INNER JOIN b_user U ON U.ID = A.STUDENT_ID ".
		"LEFT JOIN b_learn_course C ON C.ID = T.COURSE_ID ".
		"LEFT JOIN b_learn_test_mark TM ON A.TEST_ID = TM.TEST_ID ".
		$obUserFieldsSql->GetJoin("A.ID") .
		" WHERE 
			(TM.SCORE IS NULL 
			OR TM.SCORE = 
				(
				SELECT MIN(SCORE) 
				FROM b_learn_test_mark 
				WHERE SCORE >= 
					CASE WHEN A.STATUS = 'F' 
					THEN
						CASE WHEN A.MAX_SCORE > 0
						THEN
							1.0*A.SCORE/A.MAX_SCORE*100 
						ELSE
							0
						END
					ELSE
						0
					END
					AND TEST_ID = A.TEST_ID
				)
			) ";

		if ($oPermParser->IsNeedCheckPerm())
			$strSql .= " AND C.LINKED_LESSON_ID IN (" . $oPermParser->SQLForAccessibleLessons() . ") ";

		$strSql .= $strSqlSearch;

		return ($strSql);
	}
}
