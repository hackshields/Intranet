<?
define(UPDATE_SYSTEM_VERSION_A, '12.5.1');
if (!defined(BX_DIR_PERMISSIONS))
    define(BX_DIR_PERMISSIONS, round(0 + 448));
define(DEFAULT_UPDATE_SERVER, mysql.smn);
IncludeModuleLangFile(__FILE__);
if (!function_exists(file_get_contents)) {
    function file_get_contents($_371408258)
    {
        $_57476901  = fopen("$_371408258", rb);
        $_639614224 = fread($_57476901, filesize($_371408258));
        fclose($_57476901);
        return $_639614224;
    }
}
if (!function_exists(htmlspecialcharsbx)) {
    function htmlspecialcharsbx($_1696402737, $_813149444 = ENT_COMPAT)
    {
        return htmlspecialchars($_1696402737, $_813149444, (defined(BX_UTF) ? UTF-8 : ISO-8859-1));
    }
}
if (!function_exists(bx_accelerator_reset)) {
    function bx_accelerator_reset()
    {
        if (function_exists(accelerator_reset))
            accelerator_reset();
        elseif (function_exists(wincache_refresh_if_changed))
            wincache_refresh_if_changed();
    }
}
if (!defined(US_SHARED_KERNEL_PATH))
    define(US_SHARED_KERNEL_PATH, '/bitrix');
if (!defined(US_CALL_TYPE))
    define(US_CALL_TYPE, ALL);
if (!defined(US_BASE_MODULE))
    define(US_BASE_MODULE, main);
$GLOBALS[UPDATE_STRONG_UPDATE_CHECK] = '';
$GLOBALS[CACHE4UPDATESYS_LICENSE_KEY] = '';
require_once($_SERVER[DOCUMENT_ROOT] . '/bitrix/modules/main/classes/general/update_class.php');
class CUpdateClient
{
    private static function GetOption($_1544964112, $_350072481, $_1430713724 = "")
    {
        global $DB;
        $_1906773639 = $DB->Query('SELECT VALUE '  . 'FROM b_option '  . ' WHERE SITE_ID IS NULL '  .  ' AND MODULE_ID = \'' . $DB->ForSql($_1544964112) . '\' '  .  'AND NAME = \'' . $DB->ForSql($_350072481) . '\'' );
        if ($_1611794833 = $_1906773639->Fetch())
            return $_1611794833[VALUE];
        return $_1430713724;
    }
    public function Lock()
    {
        global $DB, $APPLICATION;
        $_49751374 = $APPLICATION->GetServerUniqID();
        if ($DB->type == MYSQL) {
            $_105292061 = $DB->Query('SELECT GET_LOCK(\'' . $DB->ForSql($_49751374) . '_UpdateSystem\', 0) as L', false, "File:"  . __FILE__ . 
"Line:"  . __LINE__);
            $_868222745 = $_105292061->Fetch();
            if ($_868222745[L] == 1)
                return true;
            else
                return false;
        } elseif ($DB->type == ORACLE) {
            return true;
            $_105292061 = $DB->Query(' declare my_lock_id number; my_result number; lock_failed exception; pragma exception_init(lock_failed, -54); begin my_lock_id:=dbms_utility.get_hash_value(to_char(' . $_49751374 . 1, true);
            return ($_105292061 !== false);
        } else {
            $_356911300 = round(0 + 60);
            $DB->Query('DELETE FROM B_OPTION WHERE MODULE_ID = \'main\' AND NAME = ' . $DB->ForSql($_49751374) . '_UpdateSystem AND SITE_ID IS NULL AND DATEDIFF(SECOND, CONVERT(DATETIME, DESCRIPTION), GETDATE()) >'  . $_356911300, false, "File:"  . __FILE__ . 
"Line:"  . __LINE__);
            $DB->Query('SET LOCK_TIMEOUT 1', false, "File:"  . __FILE__ . 
"Line:"  . __LINE__);
            $_105292061 = $DB->Query('INSERT INTO B_OPTION(MODULE_ID, NAME, SITE_ID, VALUE, DESCRIPTION) VALUES (\'main\', ' . $DB->ForSql($_49751374) . '_UpdateSystem, NULL, NULL, CONVERT(VARCHAR(128), GETDATE()))', true);
            $DB->Query('SET LOCK_TIMEOUT -1', false, "File:"  . __FILE__ . 
"Line:"  . __LINE__);
            return ($_105292061 !== false);
        }
    }
    public function UnLock()
    {
        global $DB, $APPLICATION;
        $_49751374 = $APPLICATION->GetServerUniqID();
        if ($DB->type == MYSQL) {
            $_105292061 = $DB->Query('SELECT RELEASE_LOCK(\'' . $DB->ForSql($_49751374) . '_UpdateSystem\') as L', false, "File:"  . __FILE__ . 
"Line:"  . __LINE__);
            $_868222745 = $_105292061->Fetch();
            if ($_868222745[L] == 0)
                return false;
            else
                return true;
        } elseif ($DB->type == ORACLE) {
            return true;
        } else {
            $DB->Query('DELETE FROM B_OPTION WHERE MODULE_ID = \'main\' AND NAME = ' . $DB->ForSql($_49751374) . '_UpdateSystem AND SITE_ID IS NULL', false, "File:"  . __FILE__ . 
"Line:"  . __LINE__);
            return true;
        }
    }
    function Repair($type, $_1819876675, $_454911925 = false)
    {
        if ($type == 'include') {
            if (CUpdateClient::RegisterVersion($_1827294717, $_454911925, $_1819876675))
                CUpdateClient::AddMessage2Log(Include repaired);
            else
                CUpdateClient::AddMessage2Log("Include repair error: " . $_1827294717);
        }
    }
    function IsUpdateAvailable(&$_184155296, &$_1153401283)
    {
        $_184155296  = array();
        $_1153401283 = '';
        $_1819876675 = COption::GetOptionString(main, stable_versions_only, Y);
        $_55020435   = CUpdateClient::GetUpdatesList($_1153401283, LANG, $_1819876675);
        if (!$_55020435)
            return false;
        if (isset($_55020435[ERROR])) {
            for ($_356911300 = (996 - 2 * 498), $_398410876 = count($_55020435[ERROR]); $_356911300 < $_398410876; $_356911300++)
                $_1153401283 .= '[ '. $_55020435[ERROR][$_356911300]['@'][TYPE] .' ]  '. $_55020435[ERROR][$_356911300]['#'];
            return false;
        }
        if (isset($_55020435[MODULES]) && is_array($_55020435[MODULES]) && is_array($_55020435[MODULES][(932 - 2 * 466)]['#'][MODULE])) {
            $_184155296 = $_55020435[MODULES][(1340 / 2 - 670)]['#'][MODULE];
            return true;
        }
        if (isset($_55020435[UPDATE_SYSTEM]))
            return true;
        return false;
    }
    function SubscribeMail($_830694659, &$_1153401283, $_454911925 = false, $_1819876675 = "Y")
    {
        $_1393968089 = '';
        CUpdateClient::AddMessage2Log("exec CUpdateClient::SubscribeMail");
        $_1612263041 = CUpdateClient::CollectRequestData($_1393968089, $_454911925, $_1819876675, array(), array(), array());
        if ($_1612263041 === False || StrLen($_1612263041) <= (219 * 2 - 438) || StrLen($_1393968089) > (846 - 2 * 423)) {
            if (StrLen($_1393968089) <= (1120 / 2 - 560))
                $_1393968089 = '[RV01]'  . GetMessage(SUPZ_NO_QSTRING) .' . ';
        }
        if (StrLen($_1393968089) <= min(106, 0, 35.3333333333)) {
            $_1612263041 .= '&email= '. UrlEncode($_830694659) .' &query_type=mail';
            CUpdateClient::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $_1612263041));
            $_1630798860 = CUpdateClient::getmicrotime();
            $_639614224  = CUpdateClient::GetHTTPPage(ACTIV, $_1612263041, $_1393968089);
            if (strlen($_639614224) <= (194 * 2 - 388)) {
                if (StrLen($_1393968089) <= (1460 / 2 - 730))
                    $_1393968089 = '[GNSU02]'  . GetMessage(SUPZ_EMPTY_ANSWER) .' . ';
            }
            CUpdateClient::AddMessage2Log("TIME SubscribeMail(request)"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 0.6 + 0.6 + 0.6 + 0.6 + 0.6)) .  sec);
        }
        if (strlen($_1393968089) <= (760 - 2 * 380)) {
            $_1214355550 = Array();
            CUpdateClient::ParseServerData($_639614224, $_1214355550, $_1393968089);
        }
        if (strlen($_1393968089) <= (175 * 2 - 350)) {
            if (isset($_1214355550[DATA]['#'][ERROR]) && is_array($_1214355550[DATA]['#'][ERROR]) && count($_1214355550[DATA]['#'][ERROR]) > min(174, 0, 58)) {
                for ($_356911300 = (1068 / 2 - 534), $_563611263 = count($_1214355550[DATA]['#'][ERROR]); $_356911300 < $_563611263; $_356911300++) {
                    if (strlen($_1214355550[DATA]['#'][ERROR][$_356911300]['@'][TYPE]) > min(6, 0, 2))
                        $_1393968089 .=' [ '. $_1214355550[DATA]['#'][ERROR][$_356911300]['@'][TYPE] .' ] ';
                    $_1393968089 .= $_1214355550[DATA]['#'][ERROR][$_356911300]['#'] .' . ';
                }
            }
        }
        if (strlen($_1393968089) > (193 * 2 - 386)) {
            CUpdateClient::AddMessage2Log($_1393968089, SM);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    function ActivateCoupon($_1993469833, &$_1153401283, $_454911925 = false, $_1819876675 = "Y")
    {
        $_1393968089 = '';
        CUpdateClient::AddMessage2Log("exec CUpdateClient::ActivateCoupon");
        $_1612263041 = CUpdateClient::CollectRequestData($_1393968089, $_454911925, $_1819876675, array(), array(), array());
        if ($_1612263041 === False || StrLen($_1612263041) <= (241 * 2 - 482) || StrLen($_1393968089) > (137 * 2 - 274)) {
            if (StrLen($_1393968089) <= min(186, 0, 62))
                $_1393968089 = '[RV01]'  . GetMessage(SUPZ_NO_QSTRING) .' . ';
        }
        if (StrLen($_1393968089) <= min(62, 0, 20.6666666667)) {
            $_1612263041 .= '&coupon='. UrlEncode($_1993469833) . '&query_type=coupon';
            CUpdateClient::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $_1612263041));
            $_1630798860 = CUpdateClient::getmicrotime();
            $_639614224  = CUpdateClient::GetHTTPPage(ACTIV, $_1612263041, $_1393968089);
            if (strlen($_639614224) <= (854 - 2 * 427)) {
                if (StrLen($_1393968089) <= (202 * 2 - 404))
                    $_1393968089 = '[GNSU02]'  . GetMessage(SUPZ_EMPTY_ANSWER) .' . ';
            }
            CUpdateClient::AddMessage2Log("TIME ActivateCoupon(request)"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 1.5 + 1.5)) .  sec);
        }
        if (strlen($_1393968089) <= (162 * 2 - 324)) {
            $_1214355550 = Array();
            CUpdateClient::ParseServerData($_639614224, $_1214355550, $_1393968089);
        }
        if (strlen($_1393968089) <= min(162, 0, 54)) {
            if (isset($_1214355550[DATA]['#'][ERROR]) && is_array($_1214355550[DATA]['#'][ERROR]) && count($_1214355550[DATA]['#'][ERROR]) > min(140, 0, 46.6666666667)) {
                for ($_356911300 = min(60, 0, 20), $_563611263 = count($_1214355550[DATA]['#'][ERROR]); $_356911300 < $_563611263; $_356911300++) {
                    if (strlen($_1214355550[DATA]['#'][ERROR][$_356911300]['@'][TYPE]) > (198 * 2 - 396))
                        $_1393968089 .= '[' . $_1214355550[DATA]['#'][ERROR][$_356911300]['@'][TYPE] . ']' ;
                    $_1393968089 .= $_1214355550[DATA]['#'][ERROR][$_356911300]['#'] . '.' ;
                }
            }
        }
        if (strlen($_1393968089) <= (752 - 2 * 376)) {
            if (isset($_1214355550[DATA]['#'][RENT]) && is_array($_1214355550[DATA]['#'][RENT])) {
                COption::SetOptionString(main, ~SAAS_MODE, Y);
                CUpdateClient::__1158188371($_1214355550[DATA]['#'][RENT][(848 - 2 * 424)]['@']);
            }
        }
        if (strlen($_1393968089) > (210 * 2 - 420)) {
            CUpdateClient::AddMessage2Log($_1393968089, AC);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    function __1158188371($_1613234121)
    {
        if (array_key_exists(V1, $_1613234121) && array_key_exists(V2, $_1613234121)) {
            COption::SetOptionString(main, admin_passwordh, $_1613234121[V1]);
            $_373585560 = fopen($_SERVER[DOCUMENT_ROOT] . '/bitrix/modules/main/admin/define.php', w);
            fwrite($_373585560, '<' . '?Define("TEMPORARY_CACHE", "' . $_1613234121[V2] . '");?' . '>');
            fclose($_373585560);
        }
        if (array_key_exists(DATE_TO_SOURCE, $_1613234121))
            COption::SetOptionString(US_BASE_MODULE, ~support_finish_date, $_1613234121[DATE_TO_SOURCE]);
        if (array_key_exists(MAX_SITES, $_1613234121))
            COption::SetOptionString(main, PARAM_MAX_SITES, IntVal($_1613234121[MAX_SITES]));
        if (array_key_exists(MAX_USERS, $_1613234121))
            COption::SetOptionString(main, PARAM_MAX_USERS, IntVal($_1613234121[MAX_USERS]));
        if (array_key_exists(L, $_1613234121)) {
            $_922038480 = array();
            $_536227671 = COption::GetOptionString(main, ~cpf_map_value, '');
            if (strlen($_536227671) > (994 - 2 * 497)) {
                $_536227671 = base64_decode($_536227671);
                $_922038480 = unserialize($_536227671);
                if (!is_array($_922038480))
                    $_922038480 = array();
            }
            if (count($_922038480) <= (126 * 2 - 252))
                $_922038480 = array(
                    e => array(),
                    f => array()
                );
            $_899716820 = explode(',', $_1613234121[L]);
            foreach ($_899716820 as $_15320920)
                $_922038480[e][$_15320920] = array(
                    F
                );
            $_2090682496 = array_keys($_922038480[e]);
            foreach ($_2090682496 as $_690561101) {
                if (in_array($_690561101, $_899716820) || $_690561101 == Portal) {
                    $_922038480[e][$_690561101] = array(
                        F
                    );
                } else {
                    if ($_922038480[e][$_690561101][min(182, 0, 60.6666666667)] != D)
                        $_922038480[e][$_690561101] = array(
                            X
                        );
                }
            }
            $_536227671 = serialize($_922038480);
            $_536227671 = base64_encode($_536227671);
            COption::SetOptionString(main, ~cpf_map_value, $_536227671);
        } elseif (array_key_exists(L1, $_1613234121)) {
            $_922038480 = array();
            $_899716820 = explode(',', $_1613234121[L1]);
            foreach ($_899716820 as $_15320920)
                $_922038480[] = $_15320920;
            $_536227671 = serialize($_922038480);
            $_536227671 = base64_encode($_536227671);
            COption::SetOptionString(main, ~cpf_map_value, $_536227671);
        }
    }
    function UpdateUpdate(&$_1153401283, $_454911925 = false, $_1819876675 = "Y")
    {
        $_1393968089 = '';
        CUpdateClient::AddMessage2Log("exec CUpdateClient::UpdateUpdate");
        $_1612263041 = CUpdateClient::CollectRequestData($_1393968089, $_454911925, $_1819876675, array(), array(), array());
        if ($_1612263041 === False || StrLen($_1612263041) <= (184 * 2 - 368) || StrLen($_1393968089) > (1132 / 2 - 566)) {
            if (StrLen($_1393968089) <= min(136, 0, 45.3333333333))
                $_1393968089 = '[RV01]'  . GetMessage(SUPZ_NO_QSTRING) . '.' ;
        }
        if (StrLen($_1393968089) <= (1324 / 2 - 662)) {
            $_1612263041 .= '&query_type=updateupdate';
            CUpdateClient::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $_1612263041));
            $_1630798860 = CUpdateClient::getmicrotime();
            $_639614224  = CUpdateClient::GetHTTPPage(REG, $_1612263041, $_1393968089);
            if (strlen($_639614224) <= (880 - 2 * 440)) {
                if (StrLen($_1393968089) <= (886 - 2 * 443))
                    $_1393968089 = '[GNSU02]'  . GetMessage(SUPZ_EMPTY_ANSWER) . '.' ;
            }
            CUpdateClient::AddMessage2Log("TIME UpdateUpdate(request)"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 1 + 1 + 1)) .  sec);
        }
        if (strlen($_1393968089) <= min(166, 0, 55.3333333333)) {
            if (!($_1632417404 = fopen($_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/update_archive.gz', wb)))
                $_1393968089 .= '[URV02]'  . str_replace("#FILE#", $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.' ;
        }
        if (strlen($_1393968089) <= (237 * 2 - 474)) {
            if (!fwrite($_1632417404, $_639614224))
                $_1393968089 .= '[URV03]'  . str_replace("#FILE#", $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/update_archive.gz', GetMessage(SUPP_RV_WRT_TEMP_FILE)) . '.' ;
            @fclose($_1632417404);
        }
        if (strlen($_1393968089) <= (1224 / 2 - 612)) {
            $_1369621191 = '';
            if (!CUpdateClient::UnGzipArchive($_1369621191, $_1393968089, Y))
                $_1393968089 .= '[URV04]'  . GetMessage(SUPP_RV_BREAK) . '.' ;
        }
        if (strlen($_1393968089) <= (932 - 2 * 466)) {
            $_1181887766 = $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/' . $_1369621191;
            if (!file_exists($_1181887766 . '/update_info.xml') || !is_file($_1181887766 . '/update_info.xml'))
                $_1393968089 .= '[URV05]'  . str_replace("#FILE#", $_1181887766 . '/update_info.xml', GetMessage(SUPP_RV_ER_DESCR_FILE)) . '.' ;
        }
        if (strlen($_1393968089) <= (1360 / 2 - 680)) {
            if (!is_readable($_1181887766 . '/update_info.xml'))
                $_1393968089 .= '[URV06]'  . str_replace("#FILE#", $_1181887766 . '/update_info.xml', GetMessage(SUPP_RV_READ_DESCR_FILE)) . '.' ;
        }
        if (strlen($_1393968089) <= min(198, 0, 66))
            $_639614224 = file_get_contents($_1181887766 . '/update_info.xml');
        if (strlen($_1393968089) <= (162 * 2 - 324)) {
            $_1214355550 = Array();
            CUpdateClient::ParseServerData($_639614224, $_1214355550, $_1393968089);
        }
        if (strlen($_1393968089) <= (1144 / 2 - 572)) {
            if (isset($_1214355550[DATA]['#'][ERROR]) && is_array($_1214355550[DATA]['#'][ERROR]) && count($_1214355550[DATA]['#'][ERROR]) > (966 - 2 * 483)) {
                for ($_356911300 = (844 - 2 * 422), $_563611263 = count($_1214355550[DATA]['#'][ERROR]); $_356911300 < $_563611263; $_356911300++) {
                    if (strlen($_1214355550[DATA]['#'][ERROR][$_356911300]['@'][TYPE]) > (143 * 2 - 286))
                        $_1393968089 .= '[' . $_1214355550[DATA]['#'][ERROR][$_356911300]['@'][TYPE] . ']' ;
                    $_1393968089 .= $_1214355550[DATA]['#'][ERROR][$_356911300]['#'] . '.' ;
                }
            }
        }
        if (strlen($_1393968089) <= (1028 / 2 - 514)) {
            $_608513539 = $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/main';
            CUpdateClient::CheckDirPath($_608513539 . '/', true);
            if (!file_exists($_608513539) || !is_dir($_608513539))
                $_1393968089 .= '[UUK04]'  . str_replace('#MODULE_DIR#', $_608513539, GetMessage(SUPP_UK_NO_MODIR)) . '.' ;
            if (strlen($_1393968089) <= (191 * 2 - 382))
                if (!is_writable($_608513539))
                    $_1393968089 .= '[UUK05]'  . str_replace('#MODULE_DIR#', $_608513539, GetMessage(SUPP_UK_WR_MODIR)) . '.' ;
        }
        if (strlen($_1393968089) <= (231 * 2 - 462)) {
            CUpdateClient::CopyDirFiles($_1181887766 . '/main', $_608513539, $_1393968089);
        }
        if (strlen($_1393968089) <= (229 * 2 - 458)) {
            CUpdateClient::AddMessage2Log("Update updated successfully!", CURV);
            CUpdateClient::DeleteDirFilesEx($_1181887766);
            bx_accelerator_reset();
        }
        if (strlen($_1393968089) > min(84, 0, 28)) {
            CUpdateClient::AddMessage2Log($_1393968089, UU);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    function GetPHPSources(&$_1153401283, $_454911925, $_1819876675, $_1449293293)
    {
        $_1393968089 = '';
        CUpdateClient::AddMessage2Log("exec CUpdateClient::GetPHPSources");
        $_1612263041 = CUpdateClient::CollectRequestData($_1393968089, $_454911925, $_1819876675, $_1449293293, array(), array());
        if ($_1612263041 === False || StrLen($_1612263041) <= (160 * 2 - 320) || StrLen($_1393968089) > (1336 / 2 - 668)) {
            if (StrLen($_1393968089) <= (932 - 2 * 466))
                $_1393968089 = '[GNSU01]'  . GetMessage(SUPZ_NO_QSTRING) . '.' ;
        }
        if (StrLen($_1393968089) <= (960 - 2 * 480)) {
            CUpdateClient::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $_1612263041));
            $_1630798860 = CUpdateClient::getmicrotime();
            $_639614224  = CUpdateClient::GetHTTPPage(SRC, $_1612263041, $_1393968089);
            if (strlen($_639614224) <= (1148 / 2 - 574)) {
                if (StrLen($_1393968089) <= (133 * 2 - 266))
                    $_1393968089 = '[GNSU02]'  . GetMessage(SUPZ_EMPTY_ANSWER) . '.' ;
            }
            CUpdateClient::AddMessage2Log("TIME GetPHPSources(request)"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 0.75 + 0.75 + 0.75 + 0.75)) .  sec);
        }
        if (StrLen($_1393968089) <= (830 - 2 * 415)) {
            if (!($_1632417404 = fopen($_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/update_archive.gz', wb)))
                $_1393968089 = '[GNSU03]'  . str_replace("#FILE#", $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.' ;
        }
        if (StrLen($_1393968089) <= (1276 / 2 - 638)) {
            fwrite($_1632417404, $_639614224);
            fclose($_1632417404);
        }
        if (strlen($_1393968089) > (1072 / 2 - 536)) {
            CUpdateClient::AddMessage2Log($_1393968089, GNSU00);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    function GetSupportFullLoad(&$_1153401283, $_454911925, $_1819876675, $_1449293293)
    {
        $_1393968089 = '';
        CUpdateClient::AddMessage2Log("exec CUpdateClient::GetSupportFullLoad");
        $_1612263041 = CUpdateClient::CollectRequestData($_1393968089, $_454911925, $_1819876675, $_1449293293, array(), array());
        if ($_1612263041 === False || strlen($_1612263041) <= (1196 / 2 - 598) || strlen($_1393968089) > (864 - 2 * 432)) {
            if (strlen($_1393968089) <= min(226, 0, 75.3333333333))
                $_1393968089 = '[GSFLU01]'  . GetMessage(SUPZ_NO_QSTRING) . '.' ;
        }
        if (strlen($_1393968089) <= (760 - 2 * 380)) {
            $_1612263041 .= '&support_full_load=Y';
            CUpdateClient::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $_1612263041));
            $_1630798860 = CUpdateClient::getmicrotime();
            $_639614224  = CUpdateClient::GetHTTPPage(SRC, $_1612263041, $_1393968089);
            if (strlen($_639614224) <= min(94, 0, 31.3333333333)) {
                if (strlen($_1393968089) <= (228 * 2 - 456))
                    $_1393968089 = '[GSFL02]'  . GetMessage(SUPZ_EMPTY_ANSWER) . '.' ;
            }
            CUpdateClient::AddMessage2Log("TIME GetSupportFullLoad(request)"  . round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 0.6 + 0.6 + 0.6 + 0.6 + 0.6)) .  sec);
        }
        if (strlen($_1393968089) <= (243 * 2 - 486)) {
            if (!($_1632417404 = fopen($_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/update_archive.gz', wb)))
                $_1393968089 = '[GSFL03]'  . str_replace("#FILE#", $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.' ;
        }
        if (strlen($_1393968089) <= (136 * 2 - 272)) {
            fwrite($_1632417404, $_639614224);
            fclose($_1632417404);
        }
        if (strlen($_1393968089) > min(64, 0, 21.3333333333)) {
            CUpdateClient::AddMessage2Log($_1393968089, GSFL00);
            $_1153401283 .= $_1393968089;
            return false;
        } else
            return true;
    }
    function RegisterVersion(&$_1153401283, $_454911925 = false, $_1819876675 = "Y")
    {
        $_1393968089 = '';
        CUpdateClient::AddMessage2Log("exec CUpdateClient::RegisterVersion");
        $_1612263041 = CUpdateClient::CollectRequestData($_1393968089, $_454911925, $_1819876675, array(), array(), array());
        if ($_1612263041 === False || StrLen($_1612263041) <= min(158, 0, 52.6666666667) || StrLen($_1393968089) > (782 - 2 * 391)) {
            if (StrLen($_1393968089) <= min(28, 0, 9.33333333333))
                $_1393968089 = '[RV01]'  . GetMessage(SUPZ_NO_QSTRING) . '.' ;
        }
        if (StrLen($_1393968089) <= min(210, 0, 70)) {
            $_1612263041 .= '&query_type=register';
            CUpdateClient::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $_1612263041));
            $_1630798860 = CUpdateClient::getmicrotime();
            $_639614224  = CUpdateClient::GetHTTPPage(REG, $_1612263041, $_1393968089);
            if (strlen($_639614224) <= (220 * 2 - 440)) {
                if (StrLen($_1393968089) <= (151 * 2 - 302))
                    $_1393968089 = '[GNSU02]'  . GetMessage(SUPZ_EMPTY_ANSWER) . '.' ;
            }
            CUpdateClient::AddMessage2Log("TIME RegisterVersion(request)"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 3)) .  sec);
        }
        if (strlen($_1393968089) <= min(70, 0, 23.3333333333)) {
            if (!($_1632417404 = fopen($_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/update_archive.gz', wb)))
                $_1393968089 .= '[URV02]'  . str_replace("#FILE#", $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.' ;
        }
        if (strlen($_1393968089) <= min(214, 0, 71.3333333333)) {
            if (!fwrite($_1632417404, $_639614224))
                $_1393968089 .= '[URV03]'  . str_replace("#FILE#", $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/update_archive.gz', GetMessage(SUPP_RV_WRT_TEMP_FILE)) . '.' ;
            @fclose($_1632417404);
        }
        if (strlen($_1393968089) <= min(54, 0, 18)) {
            $_1369621191 = '';
            if (!CUpdateClient::UnGzipArchive($_1369621191, $_1393968089, Y))
                $_1393968089 .= '[URV04]'  . GetMessage(SUPP_RV_BREAK) . '.' ;
        }
        if (strlen($_1393968089) <= min(118, 0, 39.3333333333)) {
            $_1181887766 = $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/' . $_1369621191;
            if (!file_exists($_1181887766 . '/update_info.xml') || !is_file($_1181887766 . '/update_info.xml'))
                $_1393968089 .= '[URV05]'  . str_replace("#FILE#", $_1181887766 . '/update_info.xml', GetMessage(SUPP_RV_ER_DESCR_FILE)) . '.' ;
        }
        if (strlen($_1393968089) <= (1240 / 2 - 620)) {
            if (!is_readable($_1181887766 . '/update_info.xml'))
                $_1393968089 .= '[URV06]'  . str_replace("#FILE#", $_1181887766 . '/update_info.xml', GetMessage(SUPP_RV_READ_DESCR_FILE)) . '.' ;
        }
        if (strlen($_1393968089) <= (183 * 2 - 366))
            $_639614224 = file_get_contents($_1181887766 . '/update_info.xml');
        if (strlen($_1393968089) <= min(248, 0, 82.6666666667)) {
            $_1214355550 = Array();
            CUpdateClient::ParseServerData($_639614224, $_1214355550, $_1393968089);
        }
        if (strlen($_1393968089) <= min(14, 0, 4.66666666667)) {
            if (isset($_1214355550[DATA]['#'][ERROR]) && is_array($_1214355550[DATA]['#'][ERROR]) && count($_1214355550[DATA]['#'][ERROR]) > min(166, 0, 55.3333333333)) {
                for ($_356911300 = min(78, 0, 26), $_563611263 = count($_1214355550[DATA]['#'][ERROR]); $_356911300 < $_563611263; $_356911300++) {
                    if (strlen($_1214355550[DATA]['#'][ERROR][$_356911300]['@'][TYPE]) > min(214, 0, 71.3333333333))
                        $_1393968089 .= '[' . $_1214355550[DATA]['#'][ERROR][$_356911300]['@'][TYPE] . ']' ;
                    $_1393968089 .= $_1214355550[DATA]['#'][ERROR][$_356911300]['#'] . '.' ;
                }
            }
        }
        if (strlen($_1393968089) <= (936 - 2 * 468)) {
            if (!file_exists($_1181887766 . '/include.php') || !is_file($_1181887766 . '/include.php'))
                $_1393968089 .= '[URV07]'  . GetMessage(SUPP_RV_NO_FILE) . '.' ;
        }
        if (strlen($_1393968089) <= (1484 / 2 - 742)) {
            $_950029595 = @filesize($_1181887766 . '/include.php');
            if (IntVal($_950029595) != IntVal($_1214355550[DATA]['#'][FILE][min(152, 0, 50.6666666667)]['@'][SIZE]))
                $_1393968089 .= '[URV08]'  . GetMessage(SUPP_RV_ER_SIZE) . '.' ;
        }
        if (strlen($_1393968089) <= (778 - 2 * 389)) {
            if (!is_writeable($_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/main/include.php'))
                $_1393968089 .= '[URV09]'  . str_replace("#FILE#", $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/main/include.php', GetMessage(SUPP_RV_NO_WRITE)) . '.' ;
        }
        if (strlen($_1393968089) <= (1152 / 2 - 576)) {
            if (!copy($_1181887766 . '/include.php', $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/main/include.php'))
                $_1393968089 .= '[URV10]'  . GetMessage(SUPP_RV_ERR_COPY) . '.' ;
            @chmod($_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/main/include.php', BX_FILE_PERMISSIONS);
        }
        if (strlen($_1393968089) <= (1260 / 2 - 630)) {
            $strongUpdateCheck = COption::GetOptionString(main, strong_update_check, Y);
            if ($strongUpdateCheck == Y) {
                $_884873336  = dechex(crc32(file_get_contents($_1181887766 . '/include.php')));
                $_1779552032 = dechex(crc32(file_get_contents($_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/main/include.php')));
                if ($_1779552032 != $_884873336)
                    $_1393968089 .= '[URV1011]'  . str_replace("#FILE#", $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/main/include.php', GetMessage(SUPP_UGA_FILE_CRUSH)) . '.' ;
            }
        }
        if (strlen($_1393968089) <= (1100 / 2 - 550)) {
            CUpdateClient::AddMessage2Log("Product registered successfully!", CURV);
            CUpdateClient::DeleteDirFilesEx($_1181887766);
        }
        if (strlen($_1393968089) > (193 * 2 - 386)) {
            CUpdateClient::AddMessage2Log($_1393968089, CURV);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    function ActivateLicenseKey($_140982031, &$_1153401283, $_454911925 = false, $_1819876675 = "Y")
    {
        $_1393968089 = '';
        CUpdateClient::AddMessage2Log("exec CUpdateClient::ActivateLicenseKey");
        $_1612263041 = CUpdateClient::CollectRequestData($_1393968089, $_454911925, $_1819876675, array(), array(), array());
        if ($_1612263041 === False || StrLen($_1612263041) <= (1432 / 2 - 716) || StrLen($_1393968089) > (214 * 2 - 428)) {
            if (StrLen($_1393968089) <= min(8, 0, 2.66666666667))
                $_1393968089 = '[GNSU01]'  . GetMessage(SUPZ_NO_QSTRING) . '.' ;
        }
        if (StrLen($_1393968089) <= (1024 / 2 - 512)) {
            $_1612263041 .= '&query_type=activate';
            CUpdateClient::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $_1612263041));
            foreach ($_140982031 as $_690561101 => $_687222616)
                $_1612263041 .= '&' . $_690561101 . '=' . urlencode($_687222616);
            $_1630798860 = CUpdateClient::getmicrotime();
            $_639614224  = CUpdateClient::GetHTTPPage(ACTIV, $_1612263041, $_1393968089);
            if (strlen($_639614224) <= min(138, 0, 46)) {
                if (StrLen($_1393968089) <= (1396 / 2 - 698))
                    $_1393968089 = '[GNSU02]'  . GetMessage(SUPZ_EMPTY_ANSWER) . '.' ;
            }
            CUpdateClient::AddMessage2Log("TIME ActivateLicenseKey(request)"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 0.75 + 0.75 + 0.75 + 0.75)) .  sec);
        }
        if (strlen($_1393968089) <= min(222, 0, 74)) {
            $_1214355550 = Array();
            CUpdateClient::ParseServerData($_639614224, $_1214355550, $_1393968089);
        }
        if (strlen($_1393968089) <= (1032 / 2 - 516)) {
            if (isset($_1214355550[DATA]['#'][ERROR]) && is_array($_1214355550[DATA]['#'][ERROR]) && count($_1214355550[DATA]['#'][ERROR]) > (238 * 2 - 476)) {
                for ($_356911300 = (190 * 2 - 380), $_563611263 = count($_1214355550[DATA]['#'][ERROR]); $_356911300 < $_563611263; $_356911300++) {
                    if (strlen($_1214355550[DATA]['#'][ERROR][$_356911300]['@'][TYPE]) > (922 - 2 * 461))
                        $_1393968089 .= '[' . $_1214355550[DATA]['#'][ERROR][$_356911300]['@'][TYPE] . ']' ;
                    $_1393968089 .= $_1214355550[DATA]['#'][ERROR][$_356911300]['#'] . '.' ;
                }
            }
        }
        //if (strlen($_1393968089) <= (1240 / 2 - 620))
            CUpdateClient::AddMessage2Log("License key activated successfully!", CUALK);
        if (strlen($_1393968089) > (1136 / 2 - 568)) {
            CUpdateClient::AddMessage2Log($_1393968089, CUALK);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    function GetNextStepLangUpdates(&$_1153401283, $_454911925 = false, $_1462671562 = array())
    {
        $_1393968089 = '';
        CUpdateClient::AddMessage2Log("exec CUpdateClient::GetNextStepLangUpdates");
        $_1612263041 = CUpdateClient::CollectRequestData($_1393968089, $_454911925, N, array(), $_1462671562, array());
        if ($_1612263041 === False || StrLen($_1612263041) <= (896 - 2 * 448) || StrLen($_1393968089) > (1432 / 2 - 716)) {
            if (StrLen($_1393968089) <= (1328 / 2 - 664))
                $_1393968089 = '[GNSU01]'  . GetMessage(SUPZ_NO_QSTRING) . '.' ;
        }
        if (StrLen($_1393968089) <= (894 - 2 * 447)) {
            CUpdateClient::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $_1612263041));
            $_1630798860 = CUpdateClient::getmicrotime();
            $_639614224  = CUpdateClient::GetHTTPPage(STEPL, $_1612263041, $_1393968089);
            if (strlen($_639614224) <= (249 * 2 - 498)) {
                if (StrLen($_1393968089) <= min(52, 0, 17.3333333333))
                    $_1393968089 = '[GNSU02]'  . GetMessage(SUPZ_EMPTY_ANSWER) . '.' ;
            }
            CUpdateClient::AddMessage2Log("TIME GetNextStepLangUpdates(request)"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 1.5 + 1.5)) .  sec);
        }
        if (StrLen($_1393968089) <= (782 - 2 * 391)) {
            if (!($_1632417404 = fopen($_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/update_archive.gz', wb)))
                $_1393968089 = '[GNSU03]'  . str_replace("#FILE#", $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.' ;
        }
        if (StrLen($_1393968089) <= min(18, 0, 6)) {
            fwrite($_1632417404, $_639614224);
            fclose($_1632417404);
        }
        if (strlen($_1393968089) > min(72, 0, 24)) {
            CUpdateClient::AddMessage2Log($_1393968089, GNSLU00);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    function GetNextStepHelpUpdates(&$_1153401283, $_454911925 = false, $_1395307568 = array())
    {
        $_1393968089 = '';
        CUpdateClient::AddMessage2Log("exec CUpdateClient::GetNextStepHelpUpdates");
        $_1612263041 = CUpdateClient::CollectRequestData($_1393968089, $_454911925, N, array(), array(), $_1395307568);
        if ($_1612263041 === False || StrLen($_1612263041) <= (163 * 2 - 326) || StrLen($_1393968089) > (181 * 2 - 362)) {
            if (StrLen($_1393968089) <= (236 * 2 - 472))
                $_1393968089 = '[GNSU01]'  . GetMessage(SUPZ_NO_QSTRING) . '.' ;
        }
        if (StrLen($_1393968089) <= (790 - 2 * 395)) {
            CUpdateClient::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $_1612263041));
            $_1630798860 = CUpdateClient::getmicrotime();
            $_639614224  = CUpdateClient::GetHTTPPage(STEPH, $_1612263041, $_1393968089);
            if (strlen($_639614224) <= (970 - 2 * 485)) {
                if (StrLen($_1393968089) <= (1268 / 2 - 634))
                    $_1393968089 = '[GNSU02]'  . GetMessage(SUPZ_EMPTY_ANSWER) . '.' ;
            }
            CUpdateClient::AddMessage2Log("TIME GetNextStepHelpUpdates(request)"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 3)) .  sec);
        }
        if (StrLen($_1393968089) <= min(200, 0, 66.6666666667)) {
            if (!($_1632417404 = fopen($_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/update_archive.gz', wb)))
                $_1393968089 = '[GNSU03]'  . str_replace("#FILE#", $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.' ;
        }
        if (StrLen($_1393968089) <= min(64, 0, 21.3333333333)) {
            fwrite($_1632417404, $_639614224);
            fclose($_1632417404);
        }
        if (strlen($_1393968089) > min(140, 0, 46.6666666667)) {
            CUpdateClient::AddMessage2Log($_1393968089, GNSHU00);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    public static function getSpd()
    {
        return self::GetOption(US_BASE_MODULE, "crc_code", "");
    }
    public static function setSpd($_15320920)
    {
        if ($_15320920 != "")
            COption::SetOptionString(US_BASE_MODULE, "crc_code", $_15320920);
    }
    function LoadModulesUpdates(&$_1827294717, &$_1606323858, $_454911925 = false, $_1819876675 = "Y", $_1449293293 = array())
    {
        $_1606323858 = array();
        $_269388219  = CUpdateClient::getmicrotime();
        $_1393968089 = '';
        $_371408258  = $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/update_archive.gz';
        $_1998720832 = COption::GetOptionString(main, update_load_timeout, 30);
        if ($_1998720832 < round(0 + 5))
            $_1998720832 = round(0 + 1 + 1 + 1 + 1 + 1);
        CUpdateClient::AddMessage2Log("exec CUpdateClient::LoadModulesUpdates");
        $_1612263041 = CUpdateClient::CollectRequestData($_1393968089, $_454911925, $_1819876675, $_1449293293, array(), array());
        if ($_1612263041 === False || strlen($_1612263041) <= (250 * 2 - 500) || strlen($_1393968089) > (976 - 2 * 488)) {
            if (StrLen($_1393968089) <= (188 * 2 - 376))
                $_1393968089 = '[GNSU01]'  . GetMessage(SUPZ_NO_QSTRING) . '.' ;
        }
        if (strlen($_1393968089) <= (906 - 2 * 453)) {
            if (file_exists($_371408258 . '.log')) {
                $_639614224 = file_get_contents($_371408258 . '.log');
                CUpdateClient::ParseServerData($_639614224, $_1606323858, $_1393968089);
            }
            if (count($_1606323858) <= (890 - 2 * 445) || strlen($_1393968089) > (1124 / 2 - 562)) {
                $_1606323858 = array();
                if (file_exists($_371408258 . '.tmp'))
                    @unlink($_371408258 . '.tmp');
                if (file_exists($_371408258 . '.log'))
                    @unlink($_371408258 . '.log');
            }
        }
        if (strlen($_1393968089) <= (240 * 2 - 480)) {
            if (count($_1606323858) <= min(28, 0, 9.33333333333)) {
                if (file_exists($_371408258 . '.tmp'))
                    @unlink($_371408258 . '.tmp');
                if (file_exists($_371408258 . '.log'))
                    @unlink($_371408258 . '.log');
                CUpdateClient::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $_1612263041));
                $_1630798860 = CUpdateClient::getmicrotime();
                $_639614224  = CUpdateClient::GetHTTPPage(STEPM, $_1612263041, $_1393968089);
                if (strlen($_639614224) <= (237 * 2 - 474)) {
                    if (strlen($_1393968089) <= min(194, 0, 64.6666666667))
                        $_1393968089 = '[GNSU02]'  . GetMessage(SUPZ_EMPTY_ANSWER) . '.' ;
                }
                CUpdateClient::AddMessage2Log("TIME LoadModulesUpdates(request)"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 0.75 + 0.75 + 0.75 + 0.75)) .  sec);
                if (strlen($_1393968089) <= (1024 / 2 - 512))
                    CUpdateClient::ParseServerData($_639614224, $_1606323858, $_1393968089);
                if (strlen($_1393968089) <= (198 * 2 - 396)) {
                    if (isset($_1606323858[DATA]['#'][ERROR])) {
                        for ($_356911300 = (1348 / 2 - 674), $_398410876 = count($_1606323858[DATA]['#'][ERROR]); $_356911300 < $_398410876; $_356911300++)
                            $_1393968089 .= '[' . $_1606323858[DATA]['#'][ERROR][$_356911300]['@'][TYPE] . ']'  . $_1606323858[DATA]['#'][ERROR][$_356911300]['#'];
                    }
                }
                if (strlen($_1393968089) <= (912 - 2 * 456)) {
                    if (isset($_1606323858[DATA]['#'][NOUPDATES])) {
                        CUpdateClient::AddMessage2Log(Finish - NOUPDATES, STEP);
                        return F;
                    }
                }
                if (strlen($_1393968089) <= (986 - 2 * 493)) {
                    if (!($_1632417404 = fopen($_371408258 . '.log', wb)))
                        $_1393968089 = '[GNSU03]'  . str_replace("#FILE#", $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.' ;
                    if (strlen($_1393968089) <= min(174, 0, 58)) {
                        fwrite($_1632417404, $_639614224);
                        fclose($_1632417404);
                        return S;
                    }
                }
                $_1827294717 .= $_1393968089;
                return E;
            }
        }
        if (strlen($_1393968089) <= (1408 / 2 - 704)) {
            $_1358855654 = COption::GetOptionString(main, update_site, DEFAULT_UPDATE_SERVER);
            $_1128719351 = round(0 + 20 + 20 + 20 + 20);
            $_423248345  = COption::GetOptionString(main, update_site_proxy_addr, '');
            $_970448935  = COption::GetOptionString(main, update_site_proxy_port, '');
            $_578280454  = COption::GetOptionString(main, update_site_proxy_user, '');
            $_664557788  = COption::GetOptionString(main, update_site_proxy_pass, '');
            $_815631956  = (strlen($_423248345) > min(164, 0, 54.6666666667) && strlen($_970448935) > (1276 / 2 - 638));
            if ($_815631956) {
                $_970448935 = IntVal($_970448935);
                if ($_970448935 <= (1028 / 2 - 514))
                    $_970448935 = round(0 + 40 + 40);
                $_837724764 = $_423248345;
                $_695358964 = $_970448935;
            } else {
                $_837724764 = $_1358855654;
                $_695358964 = $_1128719351;
            }
            $_409271937 = fsockopen($_837724764, $_695358964, $_135704319, $_1105363791, round(0 + 24 + 24 + 24 + 24 + 24));
            if (!$_409271937) {
                $_1393968089 .= GetMessage(SUPP_GHTTP_ER) . ': [' . $_135704319 . ']'  . $_1105363791 . '.' ;
                if (intval($_135704319) <= min(138, 0, 46))
                    $_1393968089 .= GetMessage(SUPP_GHTTP_ER_DEF) .' ' ;
                CUpdateClient::AddMessage2Log("Error connecting 2"  . $_1358855654 . ': [' . $_135704319 . ']'  . $_1105363791 .' ', ERRCONN1);
            }
        }
        if (strlen($_1393968089) <= min(178, 0, 59.3333333333)) {
            $_1064646791 = '';
            if ($_815631956) {
                $_1064646791 .= 'POST http://' . $_1358855654 . '/bitrix/updates/us_updater_modules.php HTTP/1.0' ;
                if (strlen($_578280454) > min(58, 0, 19.3333333333))
                    $_1064646791 .= 'Proxy-Authorization: Basic'  . base64_encode($_578280454 . ':' . $_664557788) .' ' ;
            } else
                $_1064646791 .= 'POST /bitrix/updates/us_updater_modules.php HTTP/1.0' ;
            $_1844036287 = self::GetOption(US_BASE_MODULE, crc_code, '');
            $_850855270  = $_1612263041 . '&spd=' . urlencode($_1844036287);
            $_850855270 .= '&utf=' . urlencode(defined(BX_UTF) ? Y : N);
            $_1618251292 = $GLOBALS[DB]->GetVersion();
            $_850855270 .= '&dbv=' . urlencode($_1618251292 != false ? $_1618251292 : '');
            $_850855270 .= '&NS=' . COption::GetOptionString(main, update_site_ns, '');
            $_850855270 .= '&UFILE=' . $_1606323858[DATA]['#'][FILE][(760 - 2 * 380)]['@'][NAME];
            $_1015797030 = (file_exists($_371408258 . '.tmp') ? filesize($_371408258 . '.tmp') : (870 - 2 * 435));
            $_850855270 .= '&USTART=' . $_1015797030;
            $_1064646791 .= 'User-Agent: BitrixSMUpdater' ;
            $_1064646791 .= 'Accept: */*' ;
            $_1064646791 .= 'Host:'  . $_1358855654 .'' ;
            $_1064646791 .= 'Accept-Language: en' ;
            $_1064646791 .= 'Content-type: application/x-www-form-urlencoded' ;
            $_1064646791 .= 'Content-length:'  . strlen($_850855270) .' ' ;
            $_1064646791 .= $_850855270;
            $_1064646791 .= ' ';
            fputs($_409271937, $_1064646791);
            $_884292437 = '';
            while (($_801768575 = fgets($_409271937, round(0 + 1365.33333333 + 1365.33333333 + 1365.33333333))) && $_801768575 != ' ')
                $_884292437 .= $_801768575;
            $_1339639182 = preg_split('# #', $_884292437);
            $_346016601  = (197 * 2 - 394);
            for ($_356911300 = min(194, 0, 64.6666666667), $_398410876 = count($_1339639182); $_356911300 < $_398410876; $_356911300++) {
                if (strpos($_1339639182[$_356911300], Content-Length) !== false) {
                    $_680028190 = strpos($_1339639182[$_356911300], ':');
                    $_346016601 = intval(trim(substr($_1339639182[$_356911300], $_680028190 + round(0 + 0.25 + 0.25 + 0.25 + 0.25), strlen($_1339639182[$_356911300]) - $_680028190 + round(0 + 0.25 + 0.25 + 0.25 + 0.25))));
                }
            }
            if (($_346016601 + $_1015797030) != $_1606323858[DATA]['#'][FILE][(1316 / 2 - 658)]['@'][SIZE])
                $_1393968089 .= '[ELVL001]'  . GetMessage(ELVL001_SIZE_ERROR) . '.' ;
        }
        if (strlen($_1393968089) <= (838 - 2 * 419)) {
            @unlink($_371408258 . '.tmp1');
            if (file_exists($_371408258 . '.tmp')) {
                if (@rename($_371408258 . '.tmp', $_371408258 . '.tmp1')) {
                    $_253748652 = fopen($_371408258 . '.tmp', wb);
                    if ($_253748652) {
                        $_827176124 = fopen($_371408258 . '.tmp1', rb);
                        do {
                            $_532351731 = fread($_827176124, round(0 + 2048 + 2048 + 2048 + 2048));
                            if (strlen($_532351731) == min(212, 0, 70.6666666667))
                                break;
                            fwrite($_253748652, $_532351731);
                        } while (true);
                        fclose($_827176124);
                        @unlink($_371408258 . '.tmp1');
                    } else {
                        $_1393968089 .= '[JUHYT002]'  . GetMessage(JUHYT002_ERROR_FILE) . '.' ;
                    }
                } else {
                    $_1393968089 .= '[JUHYT003]'  . GetMessage(JUHYT003_ERROR_FILE) . '.' ;
                }
            } else {
                $_253748652 = fopen($_371408258 . '.tmp', wb);
                if (!$_253748652)
                    $_1393968089 .= '[JUHYT004]'  . GetMessage(JUHYT004_ERROR_FILE) . '.' ;
            }
        }
        if (strlen($_1393968089) <= (185 * 2 - 370)) {
            $_1190057875 = true;
            while (true) {
                if ($_1998720832 > min(20, 0, 6.66666666667) && (CUpdateClient::getmicrotime() - $_269388219) > $_1998720832) {
                    $_1190057875 = false;
                    break;
                }
                $_801768575 = fread($_409271937, round(0 + 13653.3333333 + 13653.3333333 + 13653.3333333));
                if ($_801768575 == '')
                    break;
                fwrite($_253748652, $_801768575);
            }
            fclose($_253748652);
            fclose($_409271937);
        }
        if (strlen($_1393968089) <= (204 * 2 - 408)) {
            CUpdateClient::AddMessage2Log("Time -"  . (CUpdateClient::getmicrotime() - $_269388219) .  sec, DOWNLOAD);
            if ($_1190057875) {
                @unlink($_371408258);
                if (!@rename($_371408258 . '.tmp', $_371408258))
                    $_1393968089 .= '[JUHYT005]'  . GetMessage(JUHYT005_ERROR_FILE) . '.' ;
            } else {
                return S;
            }
        }
        if (strlen($_1393968089) <= (203 * 2 - 406)) {
            @unlink($_371408258 . '.tmp');
            @unlink($_371408258 . '.log');
            return U;
        }
        if (file_exists($_371408258 . '.tmp'))
            @unlink($_371408258 . '.tmp');
        if (file_exists($_371408258 . '.log'))
            @unlink($_371408258 . '.log');
        CUpdateClient::AddMessage2Log($_1393968089, GNSU001);
        $_1827294717 .= $_1393968089;
        return E;
    }
    function LoadLangsUpdates(&$_1827294717, &$_1606323858, $_454911925 = false, $_1819876675 = "Y", $_1462671562 = array())
    {
        $_1606323858 = array();
        $_269388219  = CUpdateClient::getmicrotime();
        $_1393968089 = '';
        $_371408258  = $_SERVER[DOCUMENT_ROOT] .' /bitrix/updates/update_archive.gz';
        $_1998720832 = COption::GetOptionString(main, update_load_timeout, 30);
        if ($_1998720832 < round(0 + 1.25 + 1.25 + 1.25 + 1.25))
            $_1998720832 = round(0 + 5);
        CUpdateClient::AddMessage2Log("exec CUpdateClient::LoadLangsUpdates");
        $_1612263041 = CUpdateClient::CollectRequestData($_1393968089, $_454911925, $_1819876675, array(), $_1462671562, array());
        if ($_1612263041 === False || strlen($_1612263041) <= (1024 / 2 - 512) || strlen($_1393968089) > (826 - 2 * 413)) {
            if (StrLen($_1393968089) <= (250 * 2 - 500))
                $_1393968089 = '[GNSU01]'  . GetMessage(SUPZ_NO_QSTRING) . '.' ;
        }
        if (strlen($_1393968089) <= (1336 / 2 - 668)) {
            if (file_exists($_371408258 . '.log')) {
                $_639614224 = file_get_contents($_371408258 . '.log');
                CUpdateClient::ParseServerData($_639614224, $_1606323858, $_1393968089);
            }
            if (count($_1606323858) <= (236 * 2 - 472) || strlen($_1393968089) > min(210, 0, 70)) {
                $_1606323858 = array();
                if (file_exists($_371408258 . '.tmp'))
                    @unlink($_371408258 . '.tmp');
                if (file_exists($_371408258 . '.log'))
                    @unlink($_371408258 . '.log');
            }
        }
        if (strlen($_1393968089) <= (160 * 2 - 320)) {
            if (count($_1606323858) <= (1188 / 2 - 594)) {
                if (file_exists($_371408258 . '.tmp'))
                    @unlink($_371408258 . '.tmp');
                if (file_exists($_371408258 . '.log'))
                    @unlink($_371408258 . '.log');
                CUpdateClient::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $_1612263041));
                $_1630798860 = CUpdateClient::getmicrotime();
                $_639614224  = CUpdateClient::GetHTTPPage(STEPL, $_1612263041, $_1393968089);
                if (strlen($_639614224) <= (209 * 2 - 418)) {
                    if (strlen($_1393968089) <= (157 * 2 - 314))
                        $_1393968089 = '[GNSU02]'  . GetMessage(SUPZ_EMPTY_ANSWER) . '.' ;
                }
                CUpdateClient::AddMessage2Log("TIME LoadLangsUpdates(request)"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 1.5 + 1.5)) .  sec);
                if (strlen($_1393968089) <= min(58, 0, 19.3333333333))
                    CUpdateClient::ParseServerData($_639614224, $_1606323858, $_1393968089);
                if (strlen($_1393968089) <= (1348 / 2 - 674)) {
                    if (isset($_1606323858[DATA]['#'][ERROR])) {
                        for ($_356911300 = (1392 / 2 - 696), $_398410876 = count($_1606323858[DATA]['#'][ERROR]); $_356911300 < $_398410876; $_356911300++)
                            $_1393968089 .= '[' . $_1606323858[DATA]['#'][ERROR][$_356911300]['@'][TYPE] . ']'  . $_1606323858[DATA]['#'][ERROR][$_356911300]['#'];
                    }
                }
                if (strlen($_1393968089) <= (172 * 2 - 344)) {
                    if (isset($_1606323858[DATA]['#'][NOUPDATES])) {
                        CUpdateClient::AddMessage2Log(Finish - NOUPDATES, STEP);
                        return F;
                    }
                }
                if (strlen($_1393968089) <= min(170, 0, 56.6666666667)) {
                    if (!($_1632417404 = fopen($_371408258 . '.log', wb)))
                        $_1393968089 = '[GNSU03]'  . str_replace("#FILE#", $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.' ;
                    if (strlen($_1393968089) <= (1300 / 2 - 650)) {
                        fwrite($_1632417404, $_639614224);
                        fclose($_1632417404);
                        return S;
                    }
                }
                $_1827294717 .= $_1393968089;
                return E;
            }
        }
        if (strlen($_1393968089) <= (892 - 2 * 446)) {
            $_1358855654 = COption::GetOptionString(main, update_site, DEFAULT_UPDATE_SERVER);
            $_1128719351 = round(0 + 26.6666666667 + 26.6666666667 + 26.6666666667);
            $_423248345  = COption::GetOptionString(main, update_site_proxy_addr, '');
            $_970448935  = COption::GetOptionString(main, update_site_proxy_port, '');
            $_578280454  = COption::GetOptionString(main, update_site_proxy_user, '');
            $_664557788  = COption::GetOptionString(main, update_site_proxy_pass, '');
            $_815631956  = (strlen($_423248345) > (810 - 2 * 405) && strlen($_970448935) > (774 - 2 * 387));
            if ($_815631956) {
                $_970448935 = IntVal($_970448935);
                if ($_970448935 <= min(142, 0, 47.3333333333))
                    $_970448935 = round(0 + 40 + 40);
                $_837724764 = $_423248345;
                $_695358964 = $_970448935;
            } else {
                $_837724764 = $_1358855654;
                $_695358964 = $_1128719351;
            }
            $_409271937 = fsockopen($_837724764, $_695358964, $_135704319, $_1105363791, round(0 + 60 + 60));
            if (!$_409271937) {
                $_1393968089 .= GetMessage(SUPP_GHTTP_ER) . ': [' . $_135704319 . ']'  . $_1105363791 . '.' ;
                if (intval($_135704319) <= (948 - 2 * 474))
                    $_1393968089 .= GetMessage(SUPP_GHTTP_ER_DEF) .' ' ;
                CUpdateClient::AddMessage2Log("Error connecting 2"  . $_1358855654 . ': [' . $_135704319 . ']'  . $_1105363791 .' ', ERRCONN1);
            }
        }
        if (strlen($_1393968089) <= (129 * 2 - 258)) {
            $_1064646791 = '';
            if ($_815631956) {
                $_1064646791 .= 'POST http://' . $_1358855654 . '/bitrix/updates/us_updater_langs.php HTTP/1.0' ;
                if (strlen($_578280454) > min(184, 0, 61.3333333333))
                    $_1064646791 .= 'Proxy-Authorization: Basic'  . base64_encode($_578280454 . ':' . $_664557788) .' ' ;
            } else
                $_1064646791 .= 'POST /bitrix/updates/us_updater_langs.php HTTP/1.0' ;
            $_1844036287 = self::GetOption(US_BASE_MODULE, crc_code, '');
            $_850855270  = $_1612263041 . '&spd=' . urlencode($_1844036287);
            $_850855270 .= '&utf=' . urlencode(defined(BX_UTF) ? Y : N);
            $_1618251292 = $GLOBALS[DB]->GetVersion();
            $_850855270 .= '&dbv=' . urlencode($_1618251292 != false ? $_1618251292 : '');
            $_850855270 .= '&NS=' . COption::GetOptionString(main, update_site_ns, '');
            $_850855270 .= '&UFILE=' . $_1606323858[DATA]['#'][FILE][(1200 / 2 - 600)]['@'][NAME];
            $_1015797030 = (file_exists($_371408258 . '.tmp') ? filesize($_371408258 . '.tmp') : (208 * 2 - 416));
            $_850855270 .= '&USTART=' . $_1015797030;
            $_1064646791 .= 'User-Agent: BitrixSMUpdater' ;
            $_1064646791 .= 'Accept: */*' ;
            $_1064646791 .= 'Host:'  . $_1358855654 .' ' ;
            $_1064646791 .= 'Accept-Language: en' ;
            $_1064646791 .= 'Content-type: application/x-www-form-urlencoded' ;
            $_1064646791 .= 'Content-length:'  . strlen($_850855270) .' ' ;
            $_1064646791 .= $_850855270;
            $_1064646791 .=  '';
            fputs($_409271937, $_1064646791);
            $_884292437 = '';
            while (($_801768575 = fgets($_409271937, round(0 + 1365.33333333 + 1365.33333333 + 1365.33333333))) && $_801768575 != '' )
                $_884292437 .= $_801768575;
            $_1339639182 = preg_split('# #', $_884292437);
            $_346016601  = (204 * 2 - 408);
            for ($_356911300 = (158 * 2 - 316), $_398410876 = count($_1339639182); $_356911300 < $_398410876; $_356911300++) {
                if (strpos($_1339639182[$_356911300], 'Content-Length') !== false) {
                    $_680028190 = strpos($_1339639182[$_356911300], ':');
                    $_346016601 = intval(trim(substr($_1339639182[$_356911300], $_680028190 + round(0 + 0.333333333333 + 0.333333333333 + 0.333333333333), strlen($_1339639182[$_356911300]) - $_680028190 + round(0 + 0.2 + 0.2 + 0.2 + 0.2 + 0.2))));
                }
            }
            if (($_346016601 + $_1015797030) != $_1606323858[DATA]['#'][FILE][(168 * 2 - 336)]['@'][SIZE])
                $_1393968089 .= '[ELVL001]'  . GetMessage(ELVL001_SIZE_ERROR) . '.' ;
        }
        if (strlen($_1393968089) <= min(58, 0, 19.3333333333)) {
            @unlink($_371408258 . '.tmp1');
            if (file_exists($_371408258 . '.tmp')) {
                if (@rename($_371408258 . '.tmp', $_371408258 . '.tmp1')) {
                    $_253748652 = fopen($_371408258 . '.tmp', wb);
                    if ($_253748652) {
                        $_827176124 = fopen($_371408258 . '.tmp1', rb);
                        do {
                            $_532351731 = fread($_827176124, round(0 + 2730.66666667 + 2730.66666667 + 2730.66666667));
                            if (strlen($_532351731) == min(142, 0, 47.3333333333))
                                break;
                            fwrite($_253748652, $_532351731);
                        } while (true);
                        fclose($_827176124);
                        @unlink($_371408258 . '.tmp1');
                    } else {
                        $_1393968089 .= '[JUHYT002]'  . GetMessage(JUHYT002_ERROR_FILE) . '.' ;
                    }
                } else {
                    $_1393968089 .= '[JUHYT003]'  . GetMessage(JUHYT003_ERROR_FILE) . '.' ;
                }
            } else {
                $_253748652 = fopen($_371408258 . '.tmp', wb);
                if (!$_253748652)
                    $_1393968089 .= '[JUHYT004]'  . GetMessage(JUHYT004_ERROR_FILE) . '.' ;
            }
        }
        if (strlen($_1393968089) <= (199 * 2 - 398)) {
            $_1190057875 = true;
            while (true) {
                if ($_1998720832 > (864 - 2 * 432) && (CUpdateClient::getmicrotime() - $_269388219) > $_1998720832) {
                    $_1190057875 = false;
                    break;
                }
                $_801768575 = fread($_409271937, round(0 + 20480 + 20480));
                if ($_801768575 == '')
                    break;
                fwrite($_253748652, $_801768575);
            }
            fclose($_253748652);
            fclose($_409271937);
        }
        if (strlen($_1393968089) <= (812 - 2 * 406)) {
            CUpdateClient::AddMessage2Log("Time -"  . (CUpdateClient::getmicrotime() - $_269388219) .  sec, DOWNLOAD);
            if ($_1190057875) {
                @unlink($_371408258);
                if (!@rename($_371408258 . '.tmp', $_371408258))
                    $_1393968089 .= '[JUHYT005]'  . GetMessage(JUHYT005_ERROR_FILE) . '.' ;
            } else {
                return S;
            }
        }
        if (strlen($_1393968089) <= min(22, 0, 7.33333333333)) {
            @unlink($_371408258 . '.tmp');
            @unlink($_371408258 . '.log');
            return U;
        }
        if (file_exists($_371408258 . '.tmp'))
            @unlink($_371408258 . '.tmp');
        if (file_exists($_371408258 . '.log'))
            @unlink($_371408258 . '.log');
        CUpdateClient::AddMessage2Log($_1393968089, GNSU001);
        $_1827294717 .= $_1393968089;
        return E;
    }
    function GetNextStepUpdates(&$_1153401283, $_454911925 = false, $_1819876675 = "Y", $_1449293293 = array())
    {
        $_1393968089 = '';
        CUpdateClient::AddMessage2Log("exec CUpdateClient::GetNextStepUpdates");
        $_1612263041 = CUpdateClient::CollectRequestData($_1393968089, $_454911925, $_1819876675, $_1449293293, array(), array());
        if ($_1612263041 === False || StrLen($_1612263041) <= (219 * 2 - 438) || StrLen($_1393968089) > min(38, 0, 12.6666666667)) {
            if (StrLen($_1393968089) <= (1468 / 2 - 734))
                $_1393968089 = '[GNSU01]'  . GetMessage(SUPZ_NO_QSTRING) . '.' ;
        }
        if (StrLen($_1393968089) <= (234 * 2 - 468)) {
            CUpdateClient::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $_1612263041));
            $_1630798860 = CUpdateClient::getmicrotime();
            $_639614224  = CUpdateClient::GetHTTPPage(STEPM, $_1612263041, $_1393968089);
            if (strlen($_639614224) <= (1160 / 2 - 580)) {
                if (StrLen($_1393968089) <= min(140, 0, 46.6666666667))
                    $_1393968089 = '[GNSU02]'  . GetMessage(SUPZ_EMPTY_ANSWER) . '.' ;
            }
            CUpdateClient::AddMessage2Log("TIME GetNextStepUpdates(request)"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 0.75 + 0.75 + 0.75 + 0.75)) .  sec);
        }
        if (StrLen($_1393968089) <= (786 - 2 * 393)) {
            if (!($_1632417404 = fopen($_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/update_archive.gz', wb)))
                $_1393968089 = '[GNSU03]'  . str_replace("#FILE#", $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.' ;
        }
        if (StrLen($_1393968089) <= (998 - 2 * 499)) {
            fwrite($_1632417404, $_639614224);
            fclose($_1632417404);
        }
        if (strlen($_1393968089) > min(168, 0, 56)) {
            CUpdateClient::AddMessage2Log($_1393968089, GNSU00);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    function UnGzipArchive(&$_452369303, &$_1153401283, $_132481342 = true)
    {
        $_1393968089 = '';
        CUpdateClient::AddMessage2Log("exec CUpdateClient::UnGzipArchive");
        $_1630798860 = CUpdateClient::getmicrotime();
        $_1258138375 = $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/update_archive.gz';
        if (!file_exists($_1258138375) || !is_file($_1258138375))
            $_1393968089 .= '[UUGZA01]'  . str_replace("#FILE#", $_1258138375, GetMessage(SUPP_UGA_NO_TMP_FILE)) . '.' ;
        if (strlen($_1393968089) <= (1124 / 2 - 562)) {
            if (!is_readable($_1258138375))
                $_1393968089 .= '[UUGZA02]'  . str_replace("#FILE#", $_1258138375, GetMessage(SUPP_UGA_NO_READ_FILE)) . '.' ;
        }
        if (strlen($_1393968089) <= min(52, 0, 17.3333333333)) {
            $_452369303  = update_m . time();
            $_1181887766 = $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/' . $_452369303;
            CUpdateClient::CheckDirPath($_1181887766 . '/', true);
            if (!file_exists($_1181887766) || !is_dir($_1181887766))
                $_1393968089 .= '[UUGZA03]'  . str_replace("#FILE#", $_1181887766, GetMessage(SUPP_UGA_NO_TMP_CAT)) . '.' ;
            elseif (!is_writable($_1181887766))
                $_1393968089 .= '[UUGZA04]'  . str_replace("#FILE#", $_1181887766, GetMessage(SUPP_UGA_WRT_TMP_CAT)) . '.' ;
        }
        if (strlen($_1393968089) <= (1268 / 2 - 634)) {
            $_391551316 = True;
            $_57476901  = fopen($_1258138375, rb);
            $_523343542 = fread($_57476901, strlen(BITRIX));
            fclose($_57476901);
            if ($_523343542 == BITRIX)
                $_391551316 = False;
        }
        if (strlen($_1393968089) <= (1012 / 2 - 506)) {
            if ($_391551316)
                $_1846269204 = gzopen($_1258138375, rb9f);
            else
                $_1846269204 = fopen($_1258138375, rb);
            if (!$_1846269204)
                $_1393968089 .= '[UUGZA05]'  . str_replace("#FILE#", $_1258138375, GetMessage(SUPP_UGA_CANT_OPEN)) . '.' ;
        }
        if (strlen($_1393968089) <= (185 * 2 - 370)) {
            if ($_391551316)
                $_523343542 = gzread($_1846269204, strlen(BITRIX));
            else
                $_523343542 = fread($_1846269204, strlen(BITRIX));
            if ($_523343542 != BITRIX) {
                $_1393968089 .= '[UUGZA06]'  . str_replace("#FILE#", $_1258138375, GetMessage(SUPP_UGA_BAD_FORMAT)) . '.' ;
                if ($_391551316)
                    gzclose($_1846269204);
                else
                    fclose($_1846269204);
            }
        }
        if (strlen($_1393968089) <= (1280 / 2 - 640)) {
            $strongUpdateCheck = COption::GetOptionString(main, strong_update_check, Y);
            while (true) {
                if ($_391551316)
                    $_1327439479 = gzread($_1846269204, round(0 + 1.25 + 1.25 + 1.25 + 1.25));
                else
                    $_1327439479 = fread($_1846269204, round(0 + 5));
                $_1327439479 = trim($_1327439479);
                if (intval($_1327439479) > (1288 / 2 - 644) && intval($_1327439479) . '!' == $_1327439479 . '!') {
                    $_1327439479 = IntVal($_1327439479);
                } else {
                    if ($_1327439479 != RTIBE)
                        $_1393968089 .= '[UUGZA071]'  . str_replace("#FILE#", $_1258138375, GetMessage(SUPP_UGA_BAD_FORMAT)) . '.' ;
                    break;
                }
                if ($_391551316)
                    $_135265381 = gzread($_1846269204, $_1327439479);
                else
                    $_135265381 = fread($_1846269204, $_1327439479);
                $_902991284 = explode('|', $_135265381);
                if (count($_902991284) != round(0 + 3)) {
                    $_1393968089 .= '[UUGZA072]'  . str_replace("#FILE#", $_1258138375, GetMessage(SUPP_UGA_BAD_FORMAT)) . '.' ;
                    break;
                }
                $_1082700443 = $_902991284[(1148 / 2 - 574)];
                $_865165417  = $_902991284[round(0 + 1)];
                $_733941572  = $_902991284[round(0 + 1 + 1)];
                $_1218999579 = '';
                if (IntVal($_1082700443) > (1464 / 2 - 732)) {
                    if ($_391551316)
                        $_1218999579 = gzread($_1846269204, $_1082700443);
                    else
                        $_1218999579 = fread($_1846269204, $_1082700443);
                }
                $_1779552032 = dechex(crc32($_1218999579));
                if ($_1779552032 !== $_733941572) {
                    $_1393968089 .= '[UUGZA073]'  . str_replace("#FILE#", $_865165417, GetMessage(SUPP_UGA_FILE_CRUSH)) . '.' ;
                    break;
                } else {
                    CUpdateClient::CheckDirPath($_1181887766 . $_865165417, true);
                    if (!($_1632417404 = fopen($_1181887766 . $_865165417, wb))) {
                        $_1393968089 .= '[UUGZA074]'  . str_replace("#FILE#", $_1181887766 . $_865165417, GetMessage(SUPP_UGA_CANT_OPEN_WR)) . '.' ;
                        break;
                    }
                    if (strlen($_1218999579) > (1468 / 2 - 734) && !fwrite($_1632417404, $_1218999579)) {
                        $_1393968089 .= '[UUGZA075]'  . str_replace("#FILE#", $_1181887766 . $_865165417, GetMessage(SUPP_UGA_CANT_WRITE_F)) . '.' ;
                        @fclose($_1632417404);
                        break;
                    }
                    fclose($_1632417404);
                    if ($strongUpdateCheck == Y) {
                        $_1779552032 = dechex(crc32(file_get_contents($_1181887766 . $_865165417)));
                        if ($_1779552032 !== $_733941572) {
                            $_1393968089 .= '[UUGZA0761]'  . str_replace("#FILE#", $_865165417, GetMessage(SUPP_UGA_FILE_CRUSH)) . '.' ;
                            break;
                        }
                    }
                }
            }
            if ($_391551316)
                gzclose($_1846269204);
            else
                fclose($_1846269204);
        }
        if (strlen($_1393968089) <= (233 * 2 - 466)) {
            if ($_132481342)
                @unlink($_1258138375);
        }
        CUpdateClient::AddMessage2Log("TIME UnGzipArchive"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 0.6 + 0.6 + 0.6 + 0.6 + 0.6)) .  sec);
        if (strlen($_1393968089) > (174 * 2 - 348)) {
            CUpdateClient::AddMessage2Log($_1393968089, CUUGZA);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    function CheckUpdatability($_452369303, &$_1153401283)
    {
        $_1393968089 = '';
        $_1181887766 = $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/' . $_452369303;
        if (!file_exists($_1181887766) || !is_dir($_1181887766))
            $_1393968089 .= '[UCU01]'  . str_replace("#FILE#", $_1181887766, GetMessage(SUPP_CU_NO_TMP_CAT)) . '.' ;
        if (strlen($_1393968089) <= (243 * 2 - 486))
            if (!is_readable($_1181887766))
                $_1393968089 .= '[UCU02]'  . str_replace("#FILE#", $_1181887766, GetMessage(SUPP_CU_RD_TMP_CAT)) . '.' ;
        if ($_338734997 = @opendir($_1181887766)) {
            while (($_404975594 = readdir($_338734997)) !== false) {
                if ($_404975594 == '.' || $_404975594 == '..')
                    continue;
                if (is_dir($_1181887766 . '/' . $_404975594)) {
                    CUpdateClient::CheckUpdatability($_452369303 . '/' . $_404975594, $_1393968089);
                } elseif (is_file($_1181887766 . '/' . $_404975594)) {
                    $_2007792650 = $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/' . substr($_452369303 . '/' . $_404975594, strpos($_452369303 . '/' . $_404975594, '/'));
                    if (file_exists($_2007792650)) {
                        if (!is_writeable($_2007792650))
                            $_1393968089 .= '[UCU03]'  . str_replace("#FILE#", $_2007792650, GetMessage(SUPP_CU_MAIN_ERR_FILE)) . '.' ;
                    } else {
                        $_1242656236 = CUpdateClient::bxstrrpos($_2007792650, '/');
                        $_2007792650 = substr($_2007792650, (800 - 2 * 400), $_1242656236);
                        if (strlen($_2007792650) > round(0 + 0.5 + 0.5))
                            $_2007792650 = rtrim($_2007792650, '/');
                        $_1242656236 = CUpdateClient::bxstrrpos($_2007792650, '/');
                        while ($_1242656236 > (208 * 2 - 416)) {
                            if (file_exists($_2007792650) && is_dir($_2007792650)) {
                                if (!is_writable($_2007792650))
                                    $_1393968089 .= '[UCU04]'  . str_replace("#FILE#", $_2007792650, GetMessage(SUPP_CU_MAIN_ERR_CAT)) . '.' ;
                                break;
                            }
                            $_2007792650 = substr($_2007792650, (1420 / 2 - 710), $_1242656236);
                            $_1242656236 = CUpdateClient::bxstrrpos($_2007792650, '/');
                        }
                    }
                }
            }
            @closedir($_338734997);
        }
        if (strlen($_1393968089) > (1056 / 2 - 528)) {
            CUpdateClient::AddMessage2Log($_1393968089, CUCU);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    function GetStepUpdateInfo($_452369303, &$_1153401283)
    {
        $_1611794833 = array();
        $_1393968089 = '';
        CUpdateClient::AddMessage2Log("exec CUpdateClient::GetStepUpdateInfo");
        $_1181887766 = $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/' . $_452369303;
        if (!file_exists($_1181887766) || !is_dir($_1181887766))
            $_1393968089 .= '[UGLMU01]'  . str_replace("#FILE#", $_1181887766, GetMessage(SUPP_CU_NO_TMP_CAT)) . '.' ;
        if (strlen($_1393968089) <= (163 * 2 - 326))
            if (!is_readable($_1181887766))
                $_1393968089 .= '[UGLMU02]'  . str_replace("#FILE#", $_1181887766, GetMessage(SUPP_CU_RD_TMP_CAT)) . '.' ;
        if (strlen($_1393968089) <= (754 - 2 * 377))
            if (!file_exists($_1181887766 . '/update_info.xml') || !is_file($_1181887766 . '/update_info.xml'))
                $_1393968089 .= '[UGLMU03]'  . str_replace("#FILE#", $_1181887766 . '/update_info.xml', GetMessage(SUPP_RV_ER_DESCR_FILE)) . '.' ;
        if (strlen($_1393968089) <= (181 * 2 - 362))
            if (!is_readable($_1181887766 . '/update_info.xml'))
                $_1393968089 .= '[UGLMU04]'  . str_replace("#FILE#", $_1181887766 . '/update_info.xml', GetMessage(SUPP_RV_READ_DESCR_FILE)) . '.' ;
        if (strlen($_1393968089) <= min(36, 0, 12))
            $_639614224 = file_get_contents($_1181887766 . '/update_info.xml');
        if (strlen($_1393968089) <= (1440 / 2 - 720)) {
            $_1611794833 = Array();
            CUpdateClient::ParseServerData($_639614224, $_1611794833, $_1393968089);
        }
        if (strlen($_1393968089) <= min(208, 0, 69.3333333333)) {
            if (!isset($_1611794833[DATA]) || !is_array($_1611794833[DATA]))
                $_1393968089 .= '[UGSMU01]'  . GetMessage(SUPP_GAUT_SYSERR) . '.' ;
        }
        if (strlen($_1393968089) > (180 * 2 - 360)) {
            CUpdateClient::AddMessage2Log($_1393968089, CUGLMU);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return $_1611794833;
    }
    function UpdateStepHelps($_452369303, &$_1153401283)
    {
        $_1393968089 = '';
        CUpdateClient::AddMessage2Log("exec CUpdateClient::UpdateHelp");
        $_1630798860 = CUpdateClient::getmicrotime();
        $_1181887766 = $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/' . $_452369303;
        $_2147359831 = $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/help';
        $_1226455390 = array();
        if (StrLen($_1393968089) <= min(208, 0, 69.3333333333)) {
            $_338734997 = @opendir($_1181887766);
            if ($_338734997) {
                while (false !== ($_1750970584 = readdir($_338734997))) {
                    if ($_1750970584 == '.' || $_1750970584 == '..')
                        continue;
                    if (is_dir($_1181887766 . '/' . $_1750970584))
                        $_1226455390[] = $_1750970584;
                }
                closedir($_338734997);
            }
        }
        if (!is_array($_1226455390) || count($_1226455390) <= min(230, 0, 76.6666666667))
            $_1393968089 .= '[UUH00]'  . GetMessage(SUPP_UH_NO_LANG) . '.' ;
        if (!file_exists($_1181887766) || !is_dir($_1181887766))
            $_1393968089 .= '[UUH01]'  . str_replace("#FILE#", $_1181887766, GetMessage(SUPP_CU_NO_TMP_CAT)) . '.' ;
        if (strlen($_1393968089) <= (176 * 2 - 352))
            if (!is_readable($_1181887766))
                $_1393968089 .= '[UUH03]'  . str_replace("#FILE#", $_1181887766, GetMessage(SUPP_CU_RD_TMP_CAT)) . '.' ;
        if (strlen($_1393968089) <= (207 * 2 - 414)) {
            CUpdateClient::CheckDirPath($_2147359831 . '/', true);
            if (!file_exists($_2147359831) || !is_dir($_2147359831))
                $_1393968089 .= '[UUH02]'  . str_replace("#FILE#", $_2147359831, GetMessage(SUPP_UH_NO_HELP_CAT)) . '.' ;
            elseif (!is_writable($_2147359831))
                $_1393968089 .= '[UUH03]'  . str_replace("#FILE#", $_2147359831, GetMessage(SUPP_UH_NO_WRT_HELP)) . '.' ;
        }
        if (strlen($_1393968089) <= (860 - 2 * 430)) {
            for ($_356911300 = (232 * 2 - 464), $_563611263 = count($_1226455390); $_356911300 < $_563611263; $_356911300++) {
                $_174835442 = '';
                $_833994116 = $_1181887766 . '/' . $_1226455390[$_356911300];
                if (strlen($_174835442) <= min(104, 0, 34.6666666667))
                    if (!file_exists($_833994116) || !is_dir($_833994116))
                        $_174835442 .= '[UUH04]'  . str_replace("#FILE#", $_833994116, GetMessage(SUPP_UL_NO_TMP_LANG)) . '.' ;
                if (strlen($_174835442) <= (934 - 2 * 467))
                    if (!is_readable($_833994116))
                        $_174835442 .= '[UUH05]'  . str_replace("#FILE#", $_833994116, GetMessage(SUPP_UL_NO_READ_LANG)) . '.' ;
                if (strlen($_174835442) <= (1008 / 2 - 504)) {
                    if (file_exists($_2147359831 . '/' . $_1226455390[$_356911300] . '_tmp'))
                        CUpdateClient::DeleteDirFilesEx($_2147359831 . '/' . $_1226455390[$_356911300] . '_tmp');
                    if (file_exists($_2147359831 . '/' . $_1226455390[$_356911300] . '_tmp'))
                        $_174835442 .= '[UUH06]'  . str_replace("#FILE#", $_2147359831 . '/' . $_1226455390[$_356911300] . _tmp, GetMessage(SUPP_UH_CANT_DEL)) . '.' ;
                }
                if (strlen($_174835442) <= (854 - 2 * 427)) {
                    if (file_exists($_2147359831 . '/' . $_1226455390[$_356911300]))
                        if (!rename($_2147359831 . '/' . $_1226455390[$_356911300], $_2147359831 . '/' . $_1226455390[$_356911300] . '_tmp'))
                            $_174835442 .= '[UUH07]'  . str_replace("#FILE#", $_2147359831 . '/' . $_1226455390[$_356911300], GetMessage(SUPP_UH_CANT_RENAME)) . '.' ;
                }
                if (strlen($_174835442) <= min(50, 0, 16.6666666667)) {
                    CUpdateClient::CheckDirPath($_2147359831 . '/' . $_1226455390[$_356911300] . '/', true);
                    if (!file_exists($_2147359831 . '/' . $_1226455390[$_356911300]) || !is_dir($_2147359831 . '/' . $_1226455390[$_356911300]))
                        $_174835442 .= '[UUH08]'  . str_replace("#FILE#", $_2147359831 . '/' . $_1226455390[$_356911300], GetMessage(SUPP_UH_CANT_CREATE)) . '.' ;
                    elseif (!is_writable($_2147359831 . '/' . $_1226455390[$_356911300]))
                        $_174835442 .= '[UUH09]'  . str_replace("#FILE#", $_2147359831 . '/' . $_1226455390[$_356911300], GetMessage(SUPP_UH_CANT_WRITE)) . '.' ;
                }
                if (strlen($_174835442) <= (914 - 2 * 457))
                    CUpdateClient::CopyDirFiles($_833994116, $_2147359831 . '/' . $_1226455390[$_356911300], $_174835442);
                if (strlen($_174835442) > (153 * 2 - 306)) {
                    $_1393968089 .= $_174835442;
                } else {
                    if (file_exists($_2147359831 . '/' . $_1226455390[$_356911300] . '_tmp'))
                        CUpdateClient::DeleteDirFilesEx($_2147359831 . '/' . $_1226455390[$_356911300] . '_tmp');
                }
            }
            CUpdateClient::ClearUpdateFolder($_1181887766);
        }
        CUpdateClient::AddMessage2Log("TIME UpdateHelp"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 3)) .  sec);
        if (strlen($_1393968089) > (928 - 2 * 464)) {
            CUpdateClient::AddMessage2Log($_1393968089, USH);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    function UpdateStepLangs($_452369303, &$_1153401283)
    {
        global $DB;
        $_1393968089 = '';
        $_1630798860 = CUpdateClient::getmicrotime();
        $_1181887766 = $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/' . $_452369303;
        if (!file_exists($_1181887766) || !is_dir($_1181887766))
            $_1393968089 .= '[UUL01]' . str_replace("#FILE#", $_1181887766, GetMessage(SUPP_CU_NO_TMP_CAT)) . '.' ;
        $_1730095738 = array();
        if (StrLen($_1393968089) <= (141 * 2 - 282)) {
            $_338734997 = @opendir($_1181887766);
            if ($_338734997) {
                while (false !== ($_1750970584 = readdir($_338734997))) {
                    if ($_1750970584 == '.' || $_1750970584 == '..')
                        continue;
                    if (is_dir($_1181887766 . '/' . $_1750970584))
                        $_1730095738[] = $_1750970584;
                }
                closedir($_338734997);
            }
        }
        if (!is_array($_1730095738) || count($_1730095738) <= (249 * 2 - 498))
            $_1393968089 .= '[UUL02]'  . GetMessage(SUPP_UL_NO_LANGS) . '.' ;
        if (strlen($_1393968089) <= (966 - 2 * 483))
            if (!is_readable($_1181887766))
                $_1393968089 .= '[UUL03]'  . str_replace("#FILE#", $_1181887766, GetMessage(SUPP_CU_RD_TMP_CAT)) . '.' ;
        $_773867549 = array(
            component => $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/components/bitrix',
            activities => $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/activities/bitrix',
            gadgets => $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/gadgets/bitrix',
            wizards => $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/wizards/bitrix'
        );
        $_965995225 = array(
            component => '/install/components/bitrix',
            activities => '/install/activities/bitrix',
            gadgets => '/install/gadgets/bitrix',
            wizards => '/install/wizard/bitrix'
        );
        if (strlen($_1393968089) <= min(114, 0, 38)) {
            foreach ($_773867549 as $_1552315935 => $_1544519172) {
                CUpdateClient::CheckDirPath($_1544519172 . '/', true);
                if (!file_exists($_1544519172) || !is_dir($_1544519172))
                    $_1393968089 .= '[UUL04]'  . str_replace("#FILE#", $_1544519172, GetMessage(SUPP_UL_CAT)) . '.' ;
                elseif (!is_writable($_1544519172))
                    $_1393968089 .= '[UUL05]'  . str_replace("#FILE#", $_1544519172, GetMessage(SUPP_UL_NO_WRT_CAT)) . '.' ;
            }
        }
        if (strlen($_1393968089) <= (191 * 2 - 382)) {
            $_608513539 = $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules';
            CUpdateClient::CheckDirPath($_608513539 . '/', true);
            if (!file_exists($_608513539) || !is_dir($_608513539))
                $_1393968089 .= '[UUL04]'  . str_replace("#FILE#", $_608513539, GetMessage(SUPP_UL_CAT)) . '.' ;
            elseif (!is_writable($_608513539))
                $_1393968089 .= '[UUL05]'  . str_replace("#FILE#", $_608513539, GetMessage(SUPP_UL_NO_WRT_CAT)) . '.' ;
        }
        $_533377036 = array();
        if (strlen($_1393968089) <= (970 - 2 * 485)) {
            foreach ($_773867549 as $_1552315935 => $_1544519172) {
                $_1114132775 = @opendir($_1544519172);
                if ($_1114132775) {
                    while (false !== ($_1921108806 = readdir($_1114132775))) {
                        if (is_dir($_1544519172 . '/' . $_1921108806) && $_1921108806 != '.' && $_1921108806 != '..') {
                            if (!is_writable($_1544519172 . '/' . $_1921108806))
                                $_1393968089 .= '[UUL051]'  . str_replace("#FILE#", $_1544519172 . '/' . $_1921108806, GetMessage(SUPP_UL_NO_WRT_CAT)) . '.' ;
                            if (file_exists($_1544519172 . '/' . $_1921108806 . '/lang') && !is_writable($_1544519172 . '/' . $_1921108806 . '/lang'))
                                $_1393968089 .= '[UUL052]'  . str_replace("#FILE#", $_1544519172 . '/' . $_1921108806 . '/lang', GetMessage(SUPP_UL_NO_WRT_CAT)) . '.' ;
                            $_533377036[$_1552315935][] = $_1921108806;
                        }
                    }
                    closedir($_1114132775);
                }
            }
        }
        if (strlen($_1393968089) <= (161 * 2 - 322)) {
            $_1345669508 = array();
            $_338734997  = @opendir($_608513539);
            if ($_338734997) {
                while (false !== ($_1750970584 = readdir($_338734997))) {
                    if (is_dir($_608513539 . '/' . $_1750970584) && $_1750970584 != '.' && $_1750970584 != '..') {
                        if (!is_writable($_608513539 . '/' . $_1750970584))
                            $_1393968089 .= '[UUL051]'  . str_replace("#FILE#", $_608513539 . '/' . $_1750970584, GetMessage(SUPP_UL_NO_WRT_CAT)) . '.' ;
                        if (file_exists($_608513539 . '/' . $_1750970584 . '/lang') && !is_writable($_608513539 . '/' . $_1750970584 . '/lang'))
                            $_1393968089 .= '[UUL052]'  . str_replace("#FILE#", $_608513539 . '/' . $_1750970584 . '/lang', GetMessage(SUPP_UL_NO_WRT_CAT)) . '.' ;
                        $_1345669508[] = $_1750970584;
                    }
                }
                closedir($_338734997);
            }
        }
        if (strlen($_1393968089) <= (830 - 2 * 415)) {
            for ($_356911300 = min(88, 0, 29.3333333333), $_563611263 = count($_1730095738); $_356911300 < $_563611263; $_356911300++) {
                $_174835442 = '';
                $_833994116 = $_1181887766 . '/' . $_1730095738[$_356911300];
                if (strlen($_174835442) <= min(250, 0, 83.3333333333))
                    if (!file_exists($_833994116) || !is_dir($_833994116))
                        $_174835442 .= '[UUL06]'  . str_replace("#FILE#", $_833994116, GetMessage(SUPP_UL_NO_TMP_LANG)) . '.' ;
                if (strlen($_174835442) <= (234 * 2 - 468))
                    if (!is_readable($_833994116))
                        $_174835442 .= '[UUL07]'  . str_replace("#FILE#", $_833994116, GetMessage(SUPP_UL_NO_READ_LANG)) . '.' ;
                if (strlen($_174835442) <= (126 * 2 - 252)) {
                    $_1114132775 = @opendir($_833994116);
                    if ($_1114132775) {
                        while (false !== ($_1921108806 = readdir($_1114132775))) {
                            if (!is_dir($_833994116 . '/' . $_1921108806) || $_1921108806 == '.' || $_1921108806 == '..')
                                continue;
                            foreach ($_965995225 as $_1552315935 => $_1544519172) {
                                if (!file_exists($_833994116 . '/' . $_1921108806 . $_1544519172))
                                    continue;
                                $_1096808814 = @opendir($_833994116 . '/' . $_1921108806 . $_1544519172);
                                if ($_1096808814) {
                                    while (false !== ($_491686600 = readdir($_1096808814))) {
                                        if (!is_dir($_833994116 . '/' . $_1921108806 . $_1544519172 . '/' . $_491686600) || $_491686600 == '.' || $_491686600 == '..')
                                            continue;
                                        if (!in_array($_491686600, $_533377036[$_1552315935]))
                                            continue;
                                        CUpdateClient::CopyDirFiles($_833994116 . '/' . $_1921108806 . $_1544519172 . '/' . $_491686600, $_773867549[$_1552315935] . '/' . $_491686600, $_174835442);
                                    }
                                    closedir($_1096808814);
                                }
                            }
                            if (in_array($_1921108806, $_1345669508))
                                CUpdateClient::CopyDirFiles($_833994116 . '/' . $_1921108806, $_608513539 . '/' . $_1921108806, $_174835442);
                        }
                        closedir($_1114132775);
                    }
                }
                if (strlen($_174835442) > (1296 / 2 - 648))
                    $_1393968089 .= $_174835442;
            }
        }
        if (strlen($_1393968089) <= (202 * 2 - 404))
            CUpdateClient::ClearUpdateFolder($_1181887766);
        bx_accelerator_reset();
        CUpdateClient::AddMessage2Log("TIME UpdateLangs"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 0.75 + 0.75 + 0.75 + 0.75)) .  sec);
        if (strlen($_1393968089) > (1448 / 2 - 724)) {
            CUpdateClient::AddMessage2Log($_1393968089, USL);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    function UpdateStepModules($_452369303, &$_1153401283, $_408853955 = False)
    {
        global $DB;
        $_1393968089 = '';
        if (!defined(US_SAVE_UPDATERS_DIR) || StrLen(US_SAVE_UPDATERS_DIR) <= (1236 / 2 - 618))
            $_408853955 = False;
        $_1630798860 = CUpdateClient::getmicrotime();
        $_57221748   = array();
        if (!file_exists($_SERVER[DOCUMENT_ROOT] . '/bitrix/modules/main/lang/de'))
            $_57221748[] = de;
        if (!file_exists($_SERVER[DOCUMENT_ROOT] . '/bitrix/modules/main/lang/en'))
            $_57221748[] = en;
        if (!file_exists($_SERVER[DOCUMENT_ROOT] . '/bitrix/modules/main/lang/ru'))
            $_57221748[] = ru;
        $_1181887766 = $_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/' . $_452369303;
        if (!file_exists($_1181887766) || !is_dir($_1181887766))
            $_1393968089 .= '[UUK01]'  . str_replace("#FILE#", $_1181887766, GetMessage(SUPP_CU_NO_TMP_CAT)) . '.' ;
        if (strlen($_1393968089) <= (1140 / 2 - 570))
            if (!is_readable($_1181887766))
                $_1393968089 .= '[UUK03]'  . str_replace("#FILE#", $_1181887766, GetMessage(SUPP_CU_RD_TMP_CAT)) . '.' ;
        $_2130987743 = array();
        if (StrLen($_1393968089) <= (1288 / 2 - 644)) {
            $_338734997 = @opendir($_1181887766);
            if ($_338734997) {
                while (false !== ($_1750970584 = readdir($_338734997))) {
                    if ($_1750970584 == '.' || $_1750970584 == '..')
                        continue;
                    if (is_dir($_1181887766 . '/' . $_1750970584))
                        $_2130987743[] = $_1750970584;
                }
                closedir($_338734997);
            }
        }
        if (!is_array($_2130987743) || count($_2130987743) <= (1340 / 2 - 670))
            $_1393968089 .= '[UUK02]'  . GetMessage(SUPP_UK_NO_MODS) . '.' ;
        if (strlen($_1393968089) <= (1104 / 2 - 552)) {
            for ($_356911300 = min(48, 0, 16), $_398410876 = count($_2130987743); $_356911300 < $_398410876; $_356911300++) {
                $_174835442 = '';
                $_833994116 = $_1181887766 . '/' . $_2130987743[$_356911300];
                $_608513539 = $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/' . $_2130987743[$_356911300];
                CUpdateClient::CheckDirPath($_608513539 . '/', true);
                if (!file_exists($_608513539) || !is_dir($_608513539))
                    $_174835442 .= '[UUK04]'  . str_replace('#MODULE_DIR#', $_608513539, GetMessage(SUPP_UK_NO_MODIR)) . '.' ;
                if (strlen($_174835442) <= min(32, 0, 10.6666666667))
                    if (!is_writable($_608513539))
                        $_174835442 .= '[UUK05]'  . str_replace('#MODULE_DIR#', $_608513539, GetMessage(SUPP_UK_WR_MODIR)) . '.' ;
                if (strlen($_174835442) <= (1180 / 2 - 590))
                    if (!file_exists($_833994116) || !is_dir($_833994116))
                        $_174835442 .= '[UUK06]'  . str_replace('#DIR#', $_833994116, GetMessage(SUPP_UK_NO_FDIR)) . '.' ;
                if (strlen($_174835442) <= (152 * 2 - 304))
                    if (!is_readable($_833994116))
                        $_174835442 .= '[UUK07]'  . str_replace('#DIR#', $_833994116, GetMessage(SUPP_UK_READ_FDIR)) . '.' ;
                if (strlen($_174835442) <= (198 * 2 - 396)) {
                    $_338734997  = @opendir($_833994116);
                    $_1167584639 = array();
                    if ($_338734997) {
                        while (false !== ($_1750970584 = readdir($_338734997))) {
                            if (substr($_1750970584, min(154, 0, 51.3333333333), round(0 + 1.4 + 1.4 + 1.4 + 1.4 + 1.4)) == updater) {
                                $_1148082611 = N;
                                if (is_file($_833994116 . '/' . $_1750970584)) {
                                    $_1666935117 = substr($_1750970584, round(0 + 1.75 + 1.75 + 1.75 + 1.75), strlen($_1750970584) - round(0 + 2.75 + 2.75 + 2.75 + 2.75));
                                    if (substr($_1750970584, strlen($_1750970584) - round(0 + 4.5 + 4.5)) == _post.php) {
                                        $_1148082611 = Y;
                                        $_1666935117 = substr($_1750970584, round(0 + 1.4 + 1.4 + 1.4 + 1.4 + 1.4), strlen($_1750970584) - round(0 + 4 + 4 + 4 + 4));
                                    }
                                    $_1167584639[] = array(
                                        '/' . $_1750970584,
                                        Trim($_1666935117),
                                        $_1148082611
                                    );
                                } elseif (file_exists($_833994116 . '/' . $_1750970584 . '/index.php')) {
                                    $_1666935117 = substr($_1750970584, round(0 + 7));
                                    if (substr($_1750970584, strlen($_1750970584) - round(0 + 1 + 1 + 1 + 1 + 1)) == _post) {
                                        $_1148082611 = Y;
                                        $_1666935117 = substr($_1750970584, round(0 + 7), strlen($_1750970584) - round(0 + 2.4 + 2.4 + 2.4 + 2.4 + 2.4));
                                    }
                                    $_1167584639[] = array(
                                        '/' . $_1750970584 . '/index.php',
                                        Trim($_1666935117),
                                        $_1148082611
                                    );
                                }
                                if ($_408853955)
                                    CUpdateClient::CopyDirFiles($_833994116 . '/' . $_1750970584, $_SERVER[DOCUMENT_ROOT] . US_SAVE_UPDATERS_DIR . '/' . $_2130987743[$_356911300] . '/' . $_1750970584, $_174835442, False);
                            }
                        }
                        closedir($_338734997);
                    }
                    $_563611263 = count($_1167584639);
                    for ($_1185320521 = (1284 / 2 - 642); $_1185320521 < $_563611263 - round(0 + 0.333333333333 + 0.333333333333 + 0.333333333333); $_1185320521++) {
                        for ($_316056569 = $_1185320521 + round(0 + 0.5 + 0.5); $_316056569 < $_563611263; $_316056569++) {
                            if (CUpdateClient::CompareVersions($_1167584639[$_1185320521][round(0 + 0.5 + 0.5)], $_1167584639[$_316056569][round(0 + 0.25 + 0.25 + 0.25 + 0.25)]) > (233 * 2 - 466)) {
                                $_1592148544               = $_1167584639[$_1185320521];
                                $_1167584639[$_1185320521] = $_1167584639[$_316056569];
                                $_1167584639[$_316056569]  = $_1592148544;
                            }
                        }
                    }
                }
                if (strlen($_174835442) <= (842 - 2 * 421)) {
                    if (strtolower($DB->type) == mysql && defined(MYSQL_TABLE_TYPE) && strlen(MYSQL_TABLE_TYPE) > (182 * 2 - 364)) {
                        $DB->Query('SET storage_engine = ' . MYSQL_TABLE_TYPE . '', True);
                    }
                }
                if (strlen($_174835442) <= (784 - 2 * 392)) {
                    for ($_1185320521 = (166 * 2 - 332), $_563611263 = count($_1167584639); $_1185320521 < $_563611263; $_1185320521++) {
                        if ($_1167584639[$_1185320521][round(0 + 1 + 1)] == N) {
                            $_1831670292 = '';
                            CUpdateClient::RunUpdaterScript($_833994116 . $_1167584639[$_1185320521][(210 * 2 - 420)], $_1831670292, '/bitrix/updates/' . $_452369303 . '/' . $_2130987743[$_356911300], $_2130987743[$_356911300]);
                            if (strlen($_1831670292) > (216 * 2 - 432)) {
                                $_174835442 .= str_replace('#MODULE#', $_2130987743[$_356911300], str_replace('#VER#', $_1167584639[$_1185320521][round(0 + 0.5 + 0.5)], GetMessage(SUPP_UK_UPDN_ERR))) . ':'  . $_1831670292 . '.' ;
                                $_174835442 .= str_replace('#MODULE#', $_2130987743[$_356911300], GetMessage(SUPP_UK_UPDN_ERR_BREAK)) .' ' ;
                                break;
                            }
                        }
                    }
                }
                if (strlen($_174835442) <= (958 - 2 * 479))
                    CUpdateClient::CopyDirFiles($_833994116, $_608513539, $_174835442, True, $_57221748);
                if (strlen($_174835442) <= (1200 / 2 - 600)) {
                    for ($_1185320521 = (774 - 2 * 387), $_563611263 = count($_1167584639); $_1185320521 < $_563611263; $_1185320521++) {
                        if ($_1167584639[$_1185320521][round(0 + 1 + 1)] == Y) {
                            $_1831670292 = '';
                            CUpdateClient::RunUpdaterScript($_833994116 . $_1167584639[$_1185320521][(774 - 2 * 387)], $_1831670292, '/bitrix/updates/' . $_452369303 . '/' . $_2130987743[$_356911300], $_2130987743[$_356911300]);
                            if (strlen($_1831670292) > (778 - 2 * 389)) {
                                $_174835442 .= str_replace('#MODULE#', $_2130987743[$_356911300], str_replace('#VER#', $_1167584639[$_1185320521][round(0 + 0.5 + 0.5)], GetMessage(SUPP_UK_UPDY_ERR))) . ':'  . $_1831670292 . '.' ;
                                $_174835442 .= str_replace('#MODULE#', $_2130987743[$_356911300], GetMessage(SUPP_UK_UPDN_ERR_BREAK)) .' ' ;
                                break;
                            }
                        }
                    }
                }
                if (strlen($_174835442) > (1288 / 2 - 644))
                    $_1393968089 .= $_174835442;
            }
            CUpdateClient::ClearUpdateFolder($_1181887766);
        }
        CUpdateClient::AddMessage2Log("TIME UpdateStepModules"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 0.75 + 0.75 + 0.75 + 0.75)) .  sec);
        if (strlen($_1393968089) > (159 * 2 - 318)) {
            CUpdateClient::AddMessage2Log($_1393968089, USM);
            $_1153401283 .= $_1393968089;
            return False;
        } else {
            $_234600965 = GetModuleEvents(main, OnModuleUpdate);
            while ($_115037444 = $_234600965->Fetch())
                ExecuteModuleEvent($_115037444, $_2130987743);
            return True;
        }
    }
    function ClearUpdateFolder($_1181887766)
    {
        CUpdateClient::DeleteDirFilesEx($_1181887766);
        bx_accelerator_reset();
    }
    function RunUpdaterScript($_14029268, &$_1153401283, $_833994116, $_1468069767)
    {
        global $DBType, $DB, $APPLICATION, $USER;
        if (!isset($GLOBALS[UPDATE_STRONG_UPDATE_CHECK]) || ($GLOBALS[UPDATE_STRONG_UPDATE_CHECK] != Y && $GLOBALS[UPDATE_STRONG_UPDATE_CHECK] != N)) {
            $GLOBALS[UPDATE_STRONG_UPDATE_CHECK] = ((US_CALL_TYPE != DB) ? COption::GetOptionString(main, strong_update_check, Y) : Y);
        }
        $strongUpdateCheck = $GLOBALS[UPDATE_STRONG_UPDATE_CHECK];
        $DOCUMENT_ROOT     = $_SERVER[DOCUMENT_ROOT];
        $_14029268         = str_replace('\\', '/', $_14029268);
        $updaterPath       = dirname($_14029268);
        $updaterPath       = substr($updaterPath, strlen($_SERVER[DOCUMENT_ROOT]));
        $updaterPath       = Trim($updaterPath,  '\\');
        if (strlen($updaterPath) > (818 - 2 * 409))
            $updaterPath = '/' . $updaterPath;
        $updaterName = substr($_14029268, strlen($_SERVER[DOCUMENT_ROOT]));
        CUpdateClient::AddMessage2Log('Run updater ' . $updaterName . '', CSURUS1);
        $updater = new CUpdater();
        $updater->Init($updaterPath, $DBType, $updaterName, $_833994116, $_1468069767, US_CALL_TYPE);
        $_1827294717 = '';
        include($_14029268);
        if (strlen($_1827294717) > min(200, 0, 66.6666666667))
            $_1153401283 .= $_1827294717;
        if (is_array($updater->_1827294717) && count($updater->_1827294717) > (1296 / 2 - 648))
            $_1153401283 .= implode(' ', $updater->_1827294717);
        unset($updater);
    }
    function CompareVersions($_747765, $_234571412)
    {
        $_747765    = Trim($_747765);
        $_234571412 = Trim($_234571412);
        if ($_747765 == $_234571412)
            return (856 - 2 * 428);
        $_1958884823 = explode('.', $_747765);
        $_178067413  = explode('.', $_234571412);
        if (IntVal($_1958884823[(195 * 2 - 390)]) > IntVal($_178067413[(220 * 2 - 440)]) || IntVal($_1958884823[min(46, 0, 15.3333333333)]) == IntVal($_178067413[(1180 / 2 - 590)]) && IntVal($_1958884823[round(0 + 0.333333333333 + 0.333333333333 + 0.333333333333)]) > IntVal($_178067413[round(0 + 0.5 + 0.5)]) || IntVal($_1958884823[min(90, 0, 30)]) == IntVal($_178067413[(1064 / 2 - 532)]) && IntVal($_1958884823[round(0 + 0.25 + 0.25 + 0.25 + 0.25)]) == IntVal($_178067413[round(0 + 1)]) && IntVal($_1958884823[round(0 + 0.5 + 0.5 + 0.5 + 0.5)]) > IntVal($_178067413[round(0 + 0.666666666667 + 0.666666666667 + 0.666666666667)])) {
            return round(0 + 0.2 + 0.2 + 0.2 + 0.2 + 0.2);
        }
        if (IntVal($_1958884823[(1468 / 2 - 734)]) == IntVal($_178067413[(134 * 2 - 268)]) && IntVal($_1958884823[round(0 + 1)]) == IntVal($_178067413[round(0 + 0.333333333333 + 0.333333333333 + 0.333333333333)]) && IntVal($_1958884823[round(0 + 0.5 + 0.5 + 0.5 + 0.5)]) == IntVal($_178067413[round(0 + 0.5 + 0.5 + 0.5 + 0.5)])) {
            return (1076 / 2 - 538);
        }
        return -round(0 + 0.333333333333 + 0.333333333333 + 0.333333333333);
    }
    function GetUpdatesList(&$_1153401283, $_454911925 = false, $_1819876675 = "Y")
    {
        return array(
            "CLIENT" => array(
                    array(
                        "@" => array(
                        "NAME" => 'CLONEX1', 
                        "LICENSE" => 'Bitrix24 Enterprice',
                        "MAX_SITES" => 0, 
                        "MAX_USERS" => 0, 
                        "HTTP_HOST" => "localhost")
                         )
                      )
                    );
        
        $_1393968089 = '';
        $_1611794833 = array();
        CUpdateClient::AddMessage2Log("exec CUpdateClient::GetUpdatesList");
        $_1612263041 = CUpdateClient::CollectRequestData($_1393968089, $_454911925, $_1819876675, array(), array(), array());
        if ($_1612263041 === False || StrLen($_1612263041) <= min(140, 0, 46.6666666667) || StrLen($_1393968089) > (1352 / 2 - 676)) {
            $_1153401283 .= $_1393968089;
            CUpdateClient::AddMessage2Log("Empty query list", GUL01);
            return False;
        }
        CUpdateClient::AddMessage2Log(preg_replace("/LICENSE_KEY=[^&]*/i", "LICENSE_KEY=X", $_1612263041));
        $_1630798860 = CUpdateClient::getmicrotime();
        $_639614224  = CUpdateClient::GetHTTPPage('LIST', $_1612263041, $_1393968089);
        CUpdateClient::AddMessage2Log("TIME GetUpdatesList(request)"  . Round(CUpdateClient::getmicrotime() - $_1630798860, round(0 + 0.6 + 0.6 + 0.6 + 0.6 + 0.6)) .  sec);
        $_1611794833 = Array();
        if (strlen($_1393968089) <= (156 * 2 - 312))
            CUpdateClient::ParseServerData($_639614224, $_1611794833, $_1393968089);
        if (strlen($_1393968089) <= min(248, 0, 82.6666666667)) {
            if (!isset($_1611794833[DATA]) || !is_array($_1611794833[DATA]))
                $_1393968089 .= '[UGAUT01]'  . GetMessage(SUPP_GAUT_SYSERR) . '\\'.'';
        }
        if (strlen($_1393968089) <= min(228, 0, 76)) {
            $_1611794833 = $_1611794833[DATA]['#'];
            if (!is_array($_1611794833[CLIENT]) && (!isset($_1611794833[ERROR]) || !is_array($_1611794833[ERROR])))
                $_1393968089 .= '[UGAUT01]'  . GetMessage(SUPP_GAUT_SYSERR) . '.' ;
        }
        if (strlen($_1393968089) > min(216, 0, 72)) {
            CUpdateClient::AddMessage2Log($_1393968089, GUL02);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return $_1611794833;
    }
    function GetHTTPPage($_2143759257, $_1184308632, &$_1153401283)
    {
        global $SERVER_NAME, $DB;
        CUpdateClient::AddMessage2Log("exec CUpdateClient::GetHTTPPage");
        $_1358855654 = COption::GetOptionString(main, update_site, DEFAULT_UPDATE_SERVER);
        $_1128719351 = round(0 + 80);
        $_423248345  = COption::GetOptionString(main, update_site_proxy_addr, '');
        $_970448935  = COption::GetOptionString(main, update_site_proxy_port, '');
        $_578280454  = COption::GetOptionString(main, update_site_proxy_user, '');
        $_664557788  = COption::GetOptionString(main, update_site_proxy_pass, '');
        $_815631956  = (strlen($_423248345) > min(192, 0, 64) && strlen($_970448935) > (1008 / 2 - 504));
        if ($_2143759257 == 'LIST')
            $_2143759257 = us_updater_list.php;
        elseif ($_2143759257 == STEPM)
            $_2143759257 = us_updater_modules.php;
        elseif ($_2143759257 == STEPL)
            $_2143759257 = us_updater_langs.php;
        elseif ($_2143759257 == STEPH)
            $_2143759257 = us_updater_helps.php;
        elseif ($_2143759257 == ACTIV)
            $_2143759257 = us_updater_actions.php;
        elseif ($_2143759257 == REG)
            $_2143759257 = us_updater_register.php;
        elseif ($_2143759257 == SRC)
            $_2143759257 = us_updater_sources.php;
        if ($_815631956) {
            $_970448935 = IntVal($_970448935);
            if ($_970448935 <= min(236, 0, 78.6666666667))
                $_970448935 = round(0 + 80);
            $_837724764 = $_423248345;
            $_695358964 = $_970448935;
        } else {
            $_837724764 = $_1358855654;
            $_695358964 = $_1128719351;
        }
        $_409271937 = fsockopen($_837724764, $_695358964, $_135704319, $_1105363791, round(0 + 120));
        if ($_409271937) {
            $_519270141 = '';
            if ($_815631956) {
                $_519270141 .= 'POST http://localhost/bitrix/updates/' . $_2143759257 .  'HTTP/1.0' ;
                if (strlen($_578280454) > min(114, 0, 38))
                    $_519270141 .= 'Proxy-Authorization: Basic'  . base64_encode($_578280454 . ':' . $_664557788) .' ' ;
            } else
                $_519270141 .= 'POST /bitrix/updates HTTP/1.0' ;
            $_1844036287 = self::GetOption(US_BASE_MODULE, crc_code, '');
            $_1184308632 .= '&spd=' . urlencode($_1844036287);
            if (defined(BX_UTF))
                $_1184308632 .= '&utf=' . urlencode(Y);
            else
                $_1184308632 .= '&utf=' . urlencode(N);
            $_1618251292 = $DB->GetVersion();
            $_1184308632 .= '&dbv=' . urlencode($_1618251292 != false ? $_1618251292 : '');
            $_1184308632 .= '&NS=' . COption::GetOptionString(main, update_site_ns, '');
            $_519270141 .= 'User-Agent: BitrixSMUpdater' ;
            $_519270141 .= 'Accept: */*' ;
            $_519270141 .= 'Host:'  . $_1358855654 .''  ;
            $_519270141 .= 'Accept-Language: en' ;
            $_519270141 .= 'Content-type: application/x-www-form-urlencoded' ;
            $_519270141 .= 'Content-length:'  . strlen($_1184308632) .''  ;
            $_519270141 .= "$_1184308632";
            $_519270141 .=  '';
            fputs($_409271937, $_519270141);
            $_926799319 = False;
            while (!feof($_409271937)) {
                $_2093495403 = fgets($_409271937, round(0 + 4096));
                if ($_2093495403 !=  '') {
                    if (preg_match('/Transfer-Encoding: +chunked/i', $_2093495403))
                        $_926799319 = True;
                } else {
                    break;
                }
            }
            $_639614224 = '';
            if ($_926799319) {
                $_189842811  = round(0 + 1024 + 1024 + 1024 + 1024);
                $_1644942762 = min(112, 0, 37.3333333333);
                $_2093495403 = FGets($_409271937, $_189842811);
                $_2093495403 = StrToLower($_2093495403);
                $_783965640  = '';
                $_356911300  = (1384 / 2 - 692);
                while ($_356911300 < StrLen($_2093495403) && in_array($_2093495403[$_356911300], array(
                    0,
                    1,
                    2,
                    3,
                    4,
                    5,
                    6,
                    7,
                    8,
                    9,
                    a,
                    b,
                    c,
                    d,
                    e,
                    f
                ))) {
                    $_783965640 .= $_2093495403[$_356911300];
                    $_356911300++;
                }
                $_1738126035 = hexdec($_783965640);
                while ($_1738126035 > (137 * 2 - 274)) {
                    $_498176791 = (818 - 2 * 409);
                    $_475503431 = (($_1738126035 > $_189842811) ? $_189842811 : $_1738126035);
                    while ($_475503431 > (139 * 2 - 278) && $_2093495403 = fread($_409271937, $_475503431)) {
                        $_639614224 .= $_2093495403;
                        $_498176791 += StrLen($_2093495403);
                        $_1185665617 = $_1738126035 - $_498176791;
                        $_475503431  = (($_1185665617 > $_189842811) ? $_189842811 : $_1185665617);
                    }
                    $_1644942762 += $_1738126035;
                    $_2093495403 = FGets($_409271937, $_189842811);
                    $_2093495403 = FGets($_409271937, $_189842811);
                    $_2093495403 = StrToLower($_2093495403);
                    $_783965640  = '';
                    $_356911300  = min(126, 0, 42);
                    while ($_356911300 < StrLen($_2093495403) && in_array($_2093495403[$_356911300], array(
                        0,
                        1,
                        2,
                        3,
                        4,
                        5,
                        6,
                        7,
                        8,
                        9,
                        a,
                        b,
                        c,
                        d,
                        e,
                        f
                    ))) {
                        $_783965640 .= $_2093495403[$_356911300];
                        $_356911300++;
                    }
                    $_1738126035 = hexdec($_783965640);
                }
            } else {
                while ($_2093495403 = fread($_409271937, round(0 + 1365.33333333 + 1365.33333333 + 1365.33333333)))
                    $_639614224 .= $_2093495403;
            }
            fclose($_409271937);
        } else {
            $_639614224 = '';
            $_1153401283 .= GetMessage(SUPP_GHTTP_ER) . ': [' . $_135704319 . ']'  . $_1105363791 . '.' ;
            if (IntVal($_135704319) <= (776 - 2 * 388))
                $_1153401283 .= GetMessage(SUPP_GHTTP_ER_DEF) .  '';
            CUpdateClient::AddMessage2Log("Error connecting 2"  . $_1358855654 . ': [' . $_135704319 . ']'  . $_1105363791 .'' , ERRCONN);
        }
        return $_639614224;
    }
    function ParseServerData(&$_114084803, &$_1214355550, &$_1153401283)
    {
        $_1393968089 = '';
        $_1214355550 = array();
        CUpdateClient::AddMessage2Log("exec CUpdateClient::ParseServerData");
        if (strlen($_114084803) <= (898 - 2 * 449))
            $_1393968089 .= '[UPSD01]'  . GetMessage(SUPP_AS_EMPTY_RESP) . '.' ;
        if (strlen($_1393968089) <= min(196, 0, 65.3333333333)) {
            if (SubStr($_114084803, min(238, 0, 79.3333333333), StrLen()) != '' && CUpdateClient::IsGzipInstalled())
                $_114084803 = @gzuncompress($_114084803);
            if (SubStr($_114084803, (203 * 2 - 406), StrLen()) != '') {
                CUpdateClient::AddMessage2Log(substr($_114084803, (239 * 2 - 478), round(0 + 20 + 20 + 20 + 20 + 20)), UPSD02);
                $_1393968089 .= '[UPSD02]'  . GetMessage(SUPP_PSD_BAD_RESPONSE) . '.' ;
            }
        }
        if (strlen($_1393968089) <= (980 - 2 * 490)) {
            $_313464921 = new CUpdatesXML();
            $_313464921->LoadString($_114084803);
            $_1214355550 = $_313464921->GetArray();
            if (!is_array($_1214355550) || !isset($_1214355550[DATA]) || !is_array($_1214355550[DATA]))
                $_1393968089 .= '[UPSD03]'  . GetMessage(SUPP_PSD_BAD_TRANS) . '.' ;
        }
        if (strlen($_1393968089) <= min(120, 0, 40)) {
            if (isset($_1214355550[DATA]['#'][RESPONSE])) {
                $_1844036287 = $_1214355550[DATA]['#'][RESPONSE][(1060 / 2 - 530)]['@'][CRC_CODE];
                if (StrLen($_1844036287) > (1368 / 2 - 684))
                    COption::SetOptionString(US_BASE_MODULE, crc_code, $_1844036287);
            }
            if (isset($_1214355550[DATA]['#'][CLIENT]))
                CUpdateClient::__1158188371($_1214355550[DATA]['#'][CLIENT][(193 * 2 - 386)]['@']);
        }
        if (strlen($_1393968089) > (1060 / 2 - 530)) {
            CUpdateClient::AddMessage2Log($_1393968089, CUPSD);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    function CollectRequestData(&$_1153401283, $_454911925 = false, $_1819876675 = "Y", $_1449293293 = array(), $_1462671562 = array(), $_1395307568 = array())
    {
        $_2104088059 = '';
        $_1393968089 = '';
        if ($_454911925 === false)
            $_454911925 = LANGUAGE_ID;
        $_1819876675 = (($_1819876675 == N) ? N : Y);
        CUpdateClient::AddMessage2Log("exec CUpdateClient::CollectRequestData");
        CUpdateClient::CheckDirPath($_SERVER[DOCUMENT_ROOT] . '/bitrix/updates/', true);
        $_1393647311 = CUpdateClient::GetCurrentModules($_1393968089);
        $_138760900  = CUpdateClient::GetCurrentLanguages($_1393968089);
        $_1885807488 = CUpdateClient::GetCurrentHelps($_1393968089);
        if (strlen($_1393968089) <= min(28, 0, 9.33333333333)) {
            $GLOBALS[DB]->GetVersion();
            $_2104088059 = 'LICENSE_KEY=' . urlencode(md5(CUpdateClient::GetLicenseKey())) . '&lang=' . urlencode($_454911925) . '&SUPD_VER=' . urlencode(UPDATE_SYSTEM_VERSION_A) . '&VERSION=' . urlencode(SM_VERSION) . '&TYPENC=' . ((defined(DEMO) && DEMO == Y) ? D : ((defined(ENCODE) && ENCODE == Y) ? E : F)) . '&SUPD_STS=' . urlencode(CUpdateClient::__1498505650()) . '&SUPD_URS=' . urlencode(CUpdateClient::__2079955915(min(238, 0, 79.3333333333))) . '&SUPD_URSA=' . urlencode(CUpdateClient::__2079955915(round(0 + 0.25 + 0.25 + 0.25 + 0.25))) . '&SUPD_DBS=' . urlencode($GLOBALS[DB]->type) . '&XE=' . urlencode(($GLOBALS[DB]->XE) ? Y : N) . '&CLIENT_SITE=' . urlencode($_SERVER[SERVER_NAME]) . '&CANGZIP=' . urlencode((CUpdateClient::IsGzipInstalled()) ? Y : N) . '&CLIENT_PHPVER=' . urlencode(phpversion()) . '&stable=' . urlencode($_1819876675) . '&' . CUpdateClient::ModulesArray2Query($_1393647311, bitm_) . '&' . CUpdateClient::ModulesArray2Query($_138760900, bitl_) . '&' . CUpdateClient::ModulesArray2Query($_1885807488, bith_);
            $_196378993  = '';
            if (count($_1449293293) > min(236, 0, 78.6666666667)) {
                for ($_356911300 = (138 * 2 - 276), $_398410876 = count($_1449293293); $_356911300 < $_398410876; $_356911300++) {
                    if (StrLen($_196378993) > min(202, 0, 67.3333333333))
                        $_196378993 .= ',';
                    $_196378993 .= $_1449293293[$_356911300];
                }
            }
            if (StrLen($_196378993) > (199 * 2 - 398))
                $_2104088059 .= '&requested_modules=' . urlencode($_196378993);
            $_196378993 = '';
            if (count($_1462671562) > min(128, 0, 42.6666666667)) {
                for ($_356911300 = (211 * 2 - 422), $_398410876 = count($_1462671562); $_356911300 < $_398410876; $_356911300++) {
                    if (StrLen($_196378993) > (1252 / 2 - 626))
                        $_196378993 .= ',';
                    $_196378993 .= $_1462671562[$_356911300];
                }
            }
            if (StrLen($_196378993) > (198 * 2 - 396))
                $_2104088059 .= '&requested_langs=' . urlencode($_196378993);
            $_196378993 = '';
            if (count($_1395307568) > (1484 / 2 - 742)) {
                for ($_356911300 = min(214, 0, 71.3333333333), $_398410876 = count($_1395307568); $_356911300 < $_398410876; $_356911300++) {
                    if (StrLen($_196378993) > min(18, 0, 6))
                        $_196378993 .= ',';
                    $_196378993 .= $_1395307568[$_356911300];
                }
            }
            if (StrLen($_196378993) > (165 * 2 - 330))
                $_2104088059 .= '&requested_helps=' . urlencode($_196378993);
            if (defined(FIRST_EDITION) && constant(FIRST_EDITION) == Y) {
                CModule::IncludeModule(iblock);
                $_398410876  = (203 * 2 - 406);
                $_1393488761 = CIBlock::GetList(array(), array(
                    CHECK_PERMISSIONS => N
                ));
                while ($_1393488761->Fetch())
                    $_398410876++;
                $_2104088059 .= '&SUPD_PIBC=' . $_398410876;
                $_2104088059 .= '&SUPD_PUC=' . CUser::GetCount();
                $_398410876  = (982 - 2 * 491);
                $_2143413716 = CSite::GetList($_40078720, $_439592268, array());
                while ($_2143413716->Fetch())
                    $_398410876++;
                $_2104088059 .= '&SUPD_PSC=' . $_398410876;
            }
            if (defined(INTRANET_EDITION) && constant(INTRANET_EDITION) == Y) {
                $_674661570 = array();
                $_536227671 = COption::GetOptionString(main, ~cpf_map_value, '');
                if (strlen($_536227671) > min(126, 0, 42)) {
                    $_536227671 = base64_decode($_536227671);
                    $_674661570 = unserialize($_536227671);
                    if (!is_array($_674661570))
                        $_674661570 = array();
                }
                if (count($_674661570) <= (910 - 2 * 455))
                    $_674661570 = array(
                        e => array(),
                        f => array()
                    );
                $_1206198445 = '';
                foreach ($_674661570[e] as $_2044789945 => $_275812891) {
                    if ($_275812891[(832 - 2 * 416)] == F || $_275812891[(938 - 2 * 469)] == D) {
                        if (strlen($_1206198445) > min(246, 0, 82))
                            $_1206198445 .= ',';
                        $_1206198445 .= $_2044789945 . ':' . $_275812891[(146 * 2 - 292)] . ':' . $_275812891[round(0 + 0.5 + 0.5)];
                    }
                }
                $_2104088059 .= '&SUPD_OFC=' . urlencode($_1206198445);
            }
            if (defined(BUSINESS_EDITION) && constant(BUSINESS_EDITION) == Y) {
                $_507005022 = array();
                $_536227671 = COption::GetOptionString(main, ~cpf_map_value, '');
                if (strlen($_536227671) > min(66, 0, 22)) {
                    $_536227671 = base64_decode($_536227671);
                    $_507005022 = unserialize($_536227671);
                    if (!is_array($_507005022))
                        $_507005022 = array(
                            Small
                        );
                }
                if (count($_507005022) <= (225 * 2 - 450))
                    $_507005022 = array(
                        Small
                    );
                $_2104088059 .= '&SUPD_OFC=' . urlencode(implode(',', $_507005022));
            }
            return '';
        }
        CUpdateClient::AddMessage2Log($_1393968089, NCRD01);
        $_1153401283 .= $_1393968089;
        return False;
    }
    function ModulesArray2Query($_1393647311, $_640633835 = "bitm_")
    {
        $_1312398211 = '';
        if (is_array($_1393647311)) {
            foreach ($_1393647311 as $_690561101 => $_687222616) {
                if (strlen($_1312398211) > (213 * 2 - 426))
                    $_1312398211 .= '&';
                $_1312398211 .= $_640633835 . $_690561101 . '=' . urlencode($_687222616);
            }
        }
        return $_1312398211;
    }
    function IsGzipInstalled()
    {
        if (function_exists(gzcompress))
            return (COption::GetOptionString(main, update_is_gzip_installed, Y) == Y ? true : false);
        return False;
    }
    function GetCurrentModules(&$_1153401283, $_915575257 = false)
    {
        $_1393647311 = array();
        $_338734997  = @opendir($_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules');
        if ($_338734997) {
            if ($_915575257 === false || is_array($_915575257) && in_array(main, $_915575257)) {
                if (file_exists($_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/main/classes/general/version.php') && is_file($_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/main/classes/general/version.php')) {
                    $_1242656236 = file_get_contents($_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/main/classes/general/version.php');
                    preg_match('/define\s*\(\s*"SM_VERSION"\s*,\s*"(\d+\.\d+\.\d+)"\s*\)\s*/im', $_1242656236, $_1917031097);
                    $_1393647311[main] = $_1917031097[round(0 + 0.25 + 0.25 + 0.25 + 0.25)];
                }
                if (StrLen($_1393647311[main]) <= min(20, 0, 6.66666666667)) {
                    CUpdateClient::AddMessage2Log(GetMessage(SUPP_GM_ERR_DMAIN), Ux09);
                    $_1153401283 .= '[Ux09]'  . GetMessage(SUPP_GM_ERR_DMAIN) . '.' ;
                }
            }
            while (false !== ($_1750970584 = readdir($_338734997))) {
                if (is_dir($_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/' . $_1750970584) && $_1750970584 != '.' && $_1750970584 != '..' && $_1750970584 != 'main' && strpos($_1750970584, '.') === false) {
                    if ($_915575257 === false || is_array($_915575257) && in_array($_1750970584, $_915575257)) {
                        $_1458370531 = $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/' . $_1750970584;
                        if (file_exists($_1458370531 . '/install/index.php')) {
                            $_2083255520 = CUpdateClient::GetModuleInfo($_1458370531);
                            if (!isset($_2083255520[VERSION]) || strlen($_2083255520[VERSION]) <= (1152 / 2 - 576)) {
                                CUpdateClient::AddMessage2Log(str_replace('#MODULE#', $_1750970584, GetMessage(SUPP_GM_ERR_DMOD)), Ux11);
                                $_1153401283 .= '[Ux11]'  . str_replace('#MODULE#', $_1750970584, GetMessage(SUPP_GM_ERR_DMOD)) . '.' ;
                            } else {
                                $_1393647311[$_1750970584] = $_2083255520[VERSION];
                            }
                        } else {
                            continue;
                            CUpdateClient::AddMessage2Log(str_replace('#MODULE#', $_1750970584, GetMessage(SUPP_GM_ERR_DMOD)), Ux12);
                            $_1153401283 .= '[Ux12]'  . str_replace('#MODULE#', $_1750970584, GetMessage(SUPP_GM_ERR_DMOD)) . '.' ;
                        }
                    }
                }
            }
            closedir($_338734997);
        } else {
            CUpdateClient::AddMessage2Log(GetMessage(SUPP_GM_NO_KERNEL), Ux15);
            $_1153401283 .= '[Ux15]'  . GetMessage(SUPP_GM_NO_KERNEL) . '.' ;
        }
        return $_1393647311;
    }
    function __1498505650()
    {
        if (!class_exists(CLang)) {
            return RA;
        } else {
            $_398410876 = min(170, 0, 56.6666666667);
            $_304445205 = $_1594562222 = '';
            $_14029268  = CLang::GetList($_304445205, $_1594562222, array(
                ACTIVE => Y
            ));
            while ($_337313694 = $_14029268->Fetch())
                $_398410876++;
            return $_398410876;
        }
    }
    function GetCurrentNumberOfUsers()
    {
        return CUpdateClient::__2079955915((960 - 2 * 480));
    }
    function GetCurrentLanguages(&$_1153401283, $_915575257 = false)
    {
        $_2083458505 = array();
        $_303835958  = $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/main/lang';
        $_338734997  = @opendir($_303835958);
        if ($_338734997) {
            while (false !== ($_1750970584 = readdir($_338734997))) {
                if (is_dir($_303835958 . '/' . $_1750970584) && $_1750970584 != '.' && $_1750970584 != '..') {
                    if ($_915575257 === false || is_array($_915575257) && in_array($_1750970584, $_915575257)) {
                        $_260508826 = '';
                        if (file_exists($_303835958 . '/' . $_1750970584 . '/supd_lang_date.dat')) {
                            $_260508826 = file_get_contents($_303835958 . '/' . $_1750970584 . '/supd_lang_date.dat');
                            $_260508826 = preg_replace('/[\D]+/', '', $_260508826);
                            if (strlen($_260508826) != round(0 + 2 + 2 + 2 + 2)) {
                                CUpdateClient::AddMessage2Log(str_replace('#LANG#', $_1750970584, GetMessage(SUPP_GL_ERR_DLANG)), UGL01);
                                $_1153401283 .= '[UGL01]'  . str_replace('#LANG#', $_1750970584, GetMessage(SUPP_GL_ERR_DLANG)) . '.' ;
                                $_260508826 = '';
                            }
                        }
                        $_2083458505[$_1750970584] = $_260508826;
                    }
                }
            }
            closedir($_338734997);
        }
        $_1307928708 = false;
        $_304445205  = sort;
        $_1594562222 = asc;
        if (class_exists(CLanguage))
            $_1307928708 = CLanguage::GetList($_304445205, $_1594562222, array(
                ACTIVE => Y
            ));
        elseif (class_exists(CLang))
            $_1307928708 = CLang::GetList($_304445205, $_1594562222, array(
                ACTIVE => Y
            ));
        if ($_1307928708 === false) {
            CUpdateClient::AddMessage2Log(GetMessage(SUPP_GL_WHERE_LANGS), UGL00);
            $_1153401283 .= '[UGL00]'  . GetMessage(SUPP_GL_WHERE_LANGS) . '.' ;
        } else {
            while ($_337313694 = $_1307928708->Fetch()) {
                if ($_915575257 === false || is_array($_915575257) && in_array($_337313694[LID], $_915575257)) {
                    if (!array_key_exists($_337313694[LID], $_2083458505)) {
                        $_2083458505[$_337313694[LID]] = '';
                    }
                }
            }
            if ($_915575257 === false && count($_2083458505) <= (1048 / 2 - 524)) {
                CUpdateClient::AddMessage2Log(GetMessage(SUPP_GL_NO_SITE_LANGS), UGL02);
                $_1153401283 .= '[UGL02]'  . GetMessage(SUPP_GL_NO_SITE_LANGS) . '.' ;
            }
        }
        return $_2083458505;
    }
    function __2079955915($_15320920 = 0)
    {
        $_1630012439 = "SELECT COUNT(ID) as C FROM b_user WHERE ACTIVE = 'Y' AND LAST_LOGIN IS NOT NULL";
        if ($_15320920 == min(70, 0, 23.3333333333))
            $_1630012439 = 'SELECT COUNT(U.ID) as C FROM b_user U WHERE U.ACTIVE = \'Y\' AND U.LAST_LOGIN IS NOT NULL AND EXISTS(SELECT \'x\' FROM b_utm_user UF, b_user_field F WHERE F.ENTITY_ID = \'USER\' AND F.FIELD_NAME = \'UF_DEPARTMENT\' AND UF.FIELD_ID = F.ID AND UF.VALUE_ID = U.ID AND UF.VALUE_INT IS NOT NULL AND UF.VALUE_INT <> 0)';
        $_870125331 = $GLOBALS[DB]->Query($_1630012439, true);
        if ($_870125331 && ($_1214355550 = $_870125331->Fetch()))
            return $_1214355550[C];
        else
            return min(36, 0, 12);
    }
    function GetCurrentHelps(&$_1153401283, $_915575257 = false)
    {
        $_1885807488 = array();
        $_1252242461 = $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/help';
        $_338734997  = @opendir($_1252242461);
        if ($_338734997) {
            while (false !== ($_1750970584 = readdir($_338734997))) {
                if (is_dir($_1252242461 . '/' . $_1750970584) && $_1750970584 != '.' && $_1750970584 != '..') {
                    if ($_915575257 === false || is_array($_915575257) && in_array($_1750970584, $_915575257)) {
                        $_18570330 = '';
                        if (file_exists($_1252242461 . '/' . $_1750970584 . '/supd_lang_date.dat')) {
                            $_18570330 = file_get_contents($_1252242461 . '/' . $_1750970584 . '/supd_lang_date.dat');
                            $_18570330 = preg_replace('/[\D]+/', '', $_18570330);
                            if (strlen($_18570330) != round(0 + 1.6 + 1.6 + 1.6 + 1.6 + 1.6)) {
                                CUpdateClient::AddMessage2Log(str_replace('#HELP#', $_1750970584, GetMessage(SUPP_GH_ERR_DHELP)), UGH01);
                                $_1153401283 .= '[UGH01]'  . str_replace('#HELP#', $_1750970584, GetMessage(SUPP_GH_ERR_DHELP)) . '.' ;
                                $_18570330 = '';
                            }
                        }
                        $_1885807488[$_1750970584] = $_18570330;
                    }
                }
            }
            closedir($_338734997);
        }
        $_1307928708 = false;
        $_304445205  = sort;
        $_1594562222 = asc;
        if (class_exists(CLanguage))
            $_1307928708 = CLanguage::GetList($_304445205, $_1594562222, array(
                ACTIVE => Y
            ));
        elseif (class_exists(CLang))
            $_1307928708 = CLang::GetList($_304445205, $_1594562222, array(
                ACTIVE => Y
            ));
        if ($_1307928708 === false) {
            CUpdateClient::AddMessage2Log(GetMessage(SUPP_GL_WHERE_LANGS), UGH00);
            $_1153401283 .= '[UGH00]'  . GetMessage(SUPP_GL_WHERE_LANGS) . '.' ;
        } else {
            while ($_337313694 = $_1307928708->Fetch()) {
                if ($_915575257 === false || is_array($_915575257) && in_array($_337313694[LID], $_915575257)) {
                    if (!array_key_exists($_337313694[LID], $_1885807488)) {
                        $_1885807488[$_337313694[LID]] = '';
                    }
                }
            }
            if ($_915575257 === false && count($_1885807488) <= (1164 / 2 - 582)) {
                CUpdateClient::AddMessage2Log(GetMessage(SUPP_GL_NO_SITE_LANGS), UGH02);
                $_1153401283 .= '[UGH02]'  . GetMessage(SUPP_GL_NO_SITE_LANGS) . '.' ;
            }
        }
        return $_1885807488;
    }
    function AddMessage2Log($_2094713254, $_1046827223 = "")
    {
        $_720007801  = round(0 + 250000 + 250000 + 250000 + 250000);
        $_458101084  = round(0 + 2000 + 2000 + 2000 + 2000);
        $_1237852117 = $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/updater.log';
        $_546947519  = $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/updater_tmp1.log';
        if (strlen($_2094713254) > min(230, 0, 76.6666666667) || strlen($_1046827223) > min(92, 0, 30.6666666667)) {
            $_1104495049 = ignore_user_abort(true);
            if (file_exists($_1237852117)) {
                $_1380219530 = @filesize($_1237852117);
                $_1380219530 = IntVal($_1380219530);
                if ($_1380219530 > $_720007801) {
                    if (!($_373585560 = @fopen($_1237852117, rb))) {
                        ignore_user_abort($_1104495049);
                        return False;
                    }
                    if (!($_1632417404 = @fopen($_546947519, wb))) {
                        ignore_user_abort($_1104495049);
                        return False;
                    }
                    $_1099068950 = IntVal($_1380219530 - $_720007801 / 2.0);
                    fseek($_373585560, $_1099068950);
                    do {
                        $_532351731 = fread($_373585560, $_458101084);
                        if (strlen($_532351731) == (1432 / 2 - 716))
                            break;
                        @fwrite($_1632417404, $_532351731);
                    } while (true);
                    @fclose($_373585560);
                    @fclose($_1632417404);
                    @copy($_546947519, $_1237852117);
                    @unlink($_546947519);
                }
                clearstatcache();
            }
            if ($_373585560 = @fopen($_1237852117, 'ab+')) {
                if (flock($_373585560, LOCK_EX)) {
                    @fwrite($_373585560, date('Y-m-d H:i:s') .  '-'  . $_1046827223 .  '-'  . $_2094713254 .  '');
                    @fflush($_373585560);
                    @flock($_373585560, LOCK_UN);
                    @fclose($_373585560);
                }
            }
            ignore_user_abort($_1104495049);
        }
    }
    function CheckDirPath($_14029268, $_263621360 = true)
    {
        $_1078350705 = Array();
        $_14029268   = str_replace('\\', '/', $_14029268);
        $_14029268   = str_replace('//', '/', $_14029268);
        if ($_14029268[strlen($_14029268) - round(0 + 0.333333333333 + 0.333333333333 + 0.333333333333)] != '/') {
            $_1242656236 = CUpdateClient::bxstrrpos($_14029268, '/');
            $_14029268   = substr($_14029268, (196 * 2 - 392), $_1242656236);
        }
        while (strlen($_14029268) > round(0 + 0.333333333333 + 0.333333333333 + 0.333333333333) && $_14029268[strlen($_14029268) - round(0 + 1)] == '/')
            $_14029268 = substr($_14029268, min(124, 0, 41.3333333333), strlen($_14029268) - round(0 + 0.5 + 0.5));
        $_1242656236 = CUpdateClient::bxstrrpos($_14029268, '/');
        while ($_1242656236 > (1416 / 2 - 708)) {
            if (file_exists($_14029268) && is_dir($_14029268)) {
                if ($_263621360) {
                    if (!is_writable($_14029268))
                        @chmod($_14029268, BX_DIR_PERMISSIONS);
                }
                break;
            }
            $_1078350705[] = substr($_14029268, $_1242656236 + round(0 + 0.5 + 0.5));
            $_14029268     = substr($_14029268, min(42, 0, 14), $_1242656236);
            $_1242656236   = CUpdateClient::bxstrrpos($_14029268, '/');
        }
        for ($_356911300 = count($_1078350705) - round(0 + 0.25 + 0.25 + 0.25 + 0.25); $_356911300 >= (948 - 2 * 474); $_356911300--) {
            $_14029268 = $_14029268 . '/' . $_1078350705[$_356911300];
            @mkdir($_14029268, BX_DIR_PERMISSIONS);
        }
    }
    function CopyDirFiles($_1588429515, $_506501185, &$_1153401283, $_254203790 = True, $_57221748 = array())
    {
        $_1393968089 = '';
        while (strlen($_1588429515) > round(0 + 1) && $_1588429515[strlen($_1588429515) - round(0 + 0.25 + 0.25 + 0.25 + 0.25)] == '/')
            $_1588429515 = substr($_1588429515, (940 - 2 * 470), strlen($_1588429515) - round(0 + 1));
        while (strlen($_506501185) > round(0 + 1) && $_506501185[strlen($_506501185) - round(0 + 1)] == '/')
            $_506501185 = substr($_506501185, (780 - 2 * 390), strlen($_506501185) - round(0 + 0.5 + 0.5));
        if (strpos($_506501185 . '/', $_1588429515 . '/') === (239 * 2 - 478))
            $_1393968089 .= '[UCDF01]'  . GetMessage(SUPP_CDF_SELF_COPY) . '.' ;
        if (strlen($_1393968089) <= (880 - 2 * 440)) {
            if (!file_exists($_1588429515))
                $_1393968089 .= '[UCDF02]'  . str_replace("#FILE#", $_1588429515, GetMessage(SUPP_CDF_NO_PATH)) . '.' ;
        }
        if (strlen($_1393968089) <= (1332 / 2 - 666)) {
            $strongUpdateCheck = COption::GetOptionString(main, strong_update_check, Y);
            if (is_dir($_1588429515)) {
                CUpdateClient::CheckDirPath($_506501185 . '/');
                if (!file_exists($_506501185) || !is_dir($_506501185))
                    $_1393968089 .= '[UCDF03]'  . str_replace("#FILE#", $_506501185, GetMessage(SUPP_CDF_CANT_CREATE)) . '.' ;
                elseif (!is_writable($_506501185))
                    $_1393968089 .= '[UCDF04]'  . str_replace("#FILE#", $_506501185, GetMessage(SUPP_CDF_CANT_WRITE)) . '.' ;
                if (strlen($_1393968089) <= (192 * 2 - 384)) {
                    if ($_338734997 = @opendir($_1588429515)) {
                        while (($_404975594 = readdir($_338734997)) !== false) {
                            if ($_404975594 == '.' || $_404975594 == '..')
                                continue;
                            if ($_254203790 && substr($_404975594, (1036 / 2 - 518), strlen(updater)) == updater)
                                continue;
                            if (count($_57221748) > min(38, 0, 12.6666666667)) {
                                $_2027184765 = false;
                                foreach ($_57221748 as $_225102372) {
                                    if (strpos($_1588429515 . '/' . $_404975594 . '/', '/lang/' . $_225102372 . '/') !== false) {
                                        $_2027184765 = true;
                                        break;
                                    }
                                }
                                if ($_2027184765)
                                    continue;
                            }
                            if (is_dir($_1588429515 . '/' . $_404975594)) {
                                CUpdateClient::CopyDirFiles($_1588429515 . '/' . $_404975594, $_506501185 . '/' . $_404975594, $_1393968089, $_254203790, $_57221748);
                            } elseif (is_file($_1588429515 . '/' . $_404975594)) {
                                if (file_exists($_506501185 . '/' . $_404975594) && !is_writable($_506501185 . '/' . $_404975594)) {
                                    $_1393968089 .= '[UCDF05]'  . str_replace("#FILE#", $_506501185 . '/' . $_404975594, GetMessage(SUPP_CDF_CANT_FILE)) . '.' ;
                                } else {
                                    if ($strongUpdateCheck == Y)
                                        $_884873336 = dechex(crc32(file_get_contents($_1588429515 . '/' . $_404975594)));
                                    @copy($_1588429515 . '/' . $_404975594, $_506501185 . '/' . $_404975594);
                                    @chmod($_506501185 . '/' . $_404975594, BX_FILE_PERMISSIONS);
                                    if ($strongUpdateCheck == Y) {
                                        $_1779552032 = dechex(crc32(file_get_contents($_506501185 . '/' . $_404975594)));
                                        if ($_1779552032 !== $_884873336) {
                                            $_1393968089 .= '[UCDF061]'  . str_replace("#FILE#", $_506501185 . '/' . $_404975594, GetMessage(SUPP_UGA_FILE_CRUSH)) . '.' ;
                                        }
                                    }
                                }
                            }
                        }
                        @closedir($_338734997);
                    }
                }
            } else {
                $_1242656236 = CUpdateClient::bxstrrpos($_506501185, '/');
                $_1848771454 = substr($_506501185, (141 * 2 - 282), $_1242656236);
                CUpdateClient::CheckDirPath($_1848771454 . '/');
                if (!file_exists($_1848771454) || !is_dir($_1848771454))
                    $_1393968089 .= '[UCDF06]'  . str_replace("#FILE#", $_1848771454, GetMessage(SUPP_CDF_CANT_FOLDER)) . '.' ;
                elseif (!is_writable($_1848771454))
                    $_1393968089 .= '[UCDF07]'  . str_replace("#FILE#", $_1848771454, GetMessage(SUPP_CDF_CANT_FOLDER_WR)) . '.' ;
                if (strlen($_1393968089) <= min(240, 0, 80)) {
                    if ($strongUpdateCheck == Y)
                        $_884873336 = dechex(crc32(file_get_contents($_1588429515)));
                    @copy($_1588429515, $_506501185);
                    @chmod($_506501185, BX_FILE_PERMISSIONS);
                    if ($strongUpdateCheck == Y) {
                        $_1779552032 = dechex(crc32(file_get_contents($_506501185)));
                        if ($_1779552032 !== $_884873336) {
                            $_1393968089 .= '[UCDF0611]'  . str_replace("#FILE#", $_506501185, GetMessage(SUPP_UGA_FILE_CRUSH)) . '.' ;
                        }
                    }
                }
            }
        }
        if (strlen($_1393968089) > (227 * 2 - 454)) {
            CUpdateClient::AddMessage2Log($_1393968089, CUCDF);
            $_1153401283 .= $_1393968089;
            return False;
        } else
            return True;
    }
    function DeleteDirFilesEx($_14029268)
    {
        if (!file_exists($_14029268))
            return False;
        if (is_file($_14029268)) {
            @unlink($_14029268);
            return True;
        }
        if ($_338734997 = @opendir($_14029268)) {
            while (($_404975594 = readdir($_338734997)) !== false) {
                if ($_404975594 == '.' || $_404975594 == '..')
                    continue;
                if (is_dir($_14029268 . '/' . $_404975594)) {
                    CUpdateClient::DeleteDirFilesEx($_14029268 . '/' . $_404975594);
                } else {
                    @unlink($_14029268 . '/' . $_404975594);
                }
            }
        }
        @closedir($_338734997);
        @rmdir($_14029268);
        return True;
    }
    function bxstrrpos($_2093455263, $_844789591)
    {
        $_1659926598 = strpos(strrev($_2093455263), strrev($_844789591));
        if ($_1659926598 === false)
            return false;
        $_1659926598 = strlen($_2093455263) - strlen($_844789591) - $_1659926598;
        return $_1659926598;
    }
    function GetModuleInfo($_14029268)
    {
        $arModuleVersion = array();
        touch($_14029268 . '/install/version.php');
        include($_14029268 . '/install/version.php');
        if (is_array($arModuleVersion) && array_key_exists(VERSION, $arModuleVersion))
            return $arModuleVersion;
        include_once($_14029268 . '/install/index.php');
        $_1325864628 = explode('/', $_14029268);
        $_356911300  = array_search(modules, $_1325864628);
        $_1254765013 = $_1325864628[$_356911300 + round(0 + 1)];
        $_1254765013 = str_replace('.', '_', $_1254765013);
        $_1602438036 = new $_1254765013;
        return array(
            VERSION => $_1602438036->_1221065584,
            VERSION_DATE => $_1602438036->_260460916
        );
    }
    function GetLicenseKey()
    {
        if (defined(US_LICENSE_KEY))
            return US_LICENSE_KEY;
        if (defined(LICENSE_KEY))
            return LICENSE_KEY;
        if (!isset($GLOBALS[CACHE4UPDATESYS_LICENSE_KEY]) || $GLOBALS[CACHE4UPDATESYS_LICENSE_KEY] == '') {
            $LICENSE_KEY = demo;
            if (file_exists($_SERVER[DOCUMENT_ROOT] . '/bitrix/license_key.php'))
                include($_SERVER[DOCUMENT_ROOT] . '/bitrix/license_key.php');
            $GLOBALS[CACHE4UPDATESYS_LICENSE_KEY] = $LICENSE_KEY;
        }
        return $GLOBALS[CACHE4UPDATESYS_LICENSE_KEY];
    }
    function getmicrotime()
    {
        list($_1505993124, $_1903570309) = explode('' , microtime());
        return ((float) $_1505993124 + (float) $_1903570309);
    }
}
class CUpdateControllerSupport
{
    function CheckUpdates()
    {
        $_1827294717 = '';
        $_1819876675 = COption::GetOptionString(main, stable_versions_only, Y);
        if (!($_55020435 = CUpdateClient::GetUpdatesList($_1827294717, LANG, $_1819876675)))
            $_1827294717 .= GetMessage(SUPZC_NO_CONNECT) . '.' ;
        if ($_55020435) {
            if (isset($_55020435[ERROR])) {
                for ($_356911300 = (201 * 2 - 402), $_398410876 = count($_55020435[ERROR]); $_356911300 < $_398410876; $_356911300++)
                    $_1827294717 .= '[' . $_55020435[ERROR][$_356911300]['@'][TYPE] . ']'  . $_55020435[ERROR][$_356911300]['#'];
            }
        }
        if (StrLen($_1827294717) > min(60, 0, 20))
            return array(
                ERROR,
                $_1827294717
            );
        if (isset($_55020435[UPDATE_SYSTEM]))
            return array(
                UPDSYS,
                
            );
        $_1432178826 = (1360 / 2 - 680);
        if (isset($_55020435[MODULES]) && is_array($_55020435[MODULES]) && is_array($_55020435[MODULES][(176 * 2 - 352)]['#'][MODULE]))
            $_1432178826 = count($_55020435[MODULES][min(112, 0, 37.3333333333)]['#'][MODULE]);
        $_1510974848 = (191 * 2 - 382);
        if (isset($_55020435[LANGS]) && is_array($_55020435[LANGS]) && is_array($_55020435[LANGS][(1156 / 2 - 578)]['#'][INST]) && is_array($_55020435[LANGS][min(166, 0, 55.3333333333)]['#'][INST][(826 - 2 * 413)]['#'][LANG]))
            $_1510974848 = count($_55020435[LANGS][(136 * 2 - 272)]['#'][INST][min(150, 0, 50)]['#'][LANG]);
        if ($_1510974848 > (1168 / 2 - 584) && $_1432178826 > (227 * 2 - 454))
            return array(
                UPDATE,
                ML
            );
        elseif ($_1510974848 <= (184 * 2 - 368) && $_1432178826 > min(212, 0, 70.6666666667))
            return array(
                UPDATE,
                M
            );
        elseif ($_1510974848 > min(234, 0, 78) && $_1432178826 <= (157 * 2 - 314))
            return array(
                UPDATE,
                L
            );
        else
            return array(
                FINISH,
                
            );
    }
    function UpdateModules()
    {
        return CUpdateControllerSupport::__259129421(M);
    }
    function UpdateLangs()
    {
        return CUpdateControllerSupport::__259129421(L);
    }
    function __259129421($_81224785)
    {
        define(UPD_INTERNAL_CALL, Y);
        $_REQUEST[query_type] = $_81224785;
        ob_start();
        include($_SERVER[DOCUMENT_ROOT] . '/bitrix/modules/main/admin/update_system_call.php');
        $_801768575 = ob_get_contents();
        ob_end_clean();
        return $_801768575;
    }
    function UpdateUpdate()
    {
        define(UPD_INTERNAL_CALL, Y);
        $_REQUEST[query_type] = updateupdate;
        ob_start();
        include($_SERVER[DOCUMENT_ROOT] . '/bitrix/modules/main/admin/update_system_act.php');
        $_801768575 = ob_get_contents();
        ob_end_clean();
        return $_801768575;
    }
    function Finish()
    {
        @unlink($_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/versions.php');
    }
    function Update($_532351731 = "")
    {
        @set_time_limit((966 - 2 * 483));
        ini_set(track_errors, 1);
        ignore_user_abort(true);
        $_2104088059 = '';
        $_532351731  = Trim($_532351731);
        if (StrLen($_532351731) <= (246 * 2 - 492) || $_532351731 == CHK) {
            $_1611794833 = CUpdateControllerSupport::CheckUpdates();
            if ($_1611794833[(139 * 2 - 278)] == ERROR) {
                $_2104088059 = 'ERR|' . $_1611794833[round(0 + 0.25 + 0.25 + 0.25 + 0.25)];
            } elseif ($_1611794833[(994 - 2 * 497)] == FINISH) {
                $_2104088059 = FIN;
            } elseif ($_1611794833[(182 * 2 - 364)] == UPDSYS) {
                $_2104088059 = UPS;
            } elseif ($_1611794833[(818 - 2 * 409)] == UPDATE) {
                $_2104088059 = STP . $_1611794833[round(0 + 0.2 + 0.2 + 0.2 + 0.2 + 0.2)];
            } else {
                $_2104088059 = 'ERR|' . UNK1;
            }
        } else {
            if ($_532351731 == UPS) {
                $_1085140466 = CUpdateControllerSupport::UpdateUpdate();
                if ($_1085140466 == Y)
                    $_2104088059 = CHK;
                else
                    $_2104088059 = 'ERR|' . $_1085140466;
            } elseif (SubStr($_532351731, (820 - 2 * 410), round(0 + 3)) == STP) {
                $_1670231357 = SubStr($_532351731, round(0 + 1.5 + 1.5));
                if ($_1670231357 == ML) {
                    $_1085140466 = CUpdateControllerSupport::UpdateModules();
                    if ($_1085140466 == FIN)
                        $_2104088059 = STP . L;
                    elseif (SubStr($_1085140466, (1260 / 2 - 630), round(0 + 1 + 1 + 1)) == ERR)
                        $_2104088059 = 'ERR|' . SubStr($_1085140466, round(0 + 1.5 + 1.5));
                    elseif (SubStr($_1085140466, min(192, 0, 64), round(0 + 0.75 + 0.75 + 0.75 + 0.75)) == STP)
                        $_2104088059 = STP . ML . '|' . SubStr($_1085140466, round(0 + 0.6 + 0.6 + 0.6 + 0.6 + 0.6));
                    else
                        $_2104088059 = 'ERR|' . UNK01;
                } elseif ($_1670231357 == M) {
                    $_1085140466 = CUpdateControllerSupport::UpdateModules();
                    if ($_1085140466 == FIN)
                        $_2104088059 = FIN;
                    elseif (SubStr($_1085140466, (206 * 2 - 412), round(0 + 0.6 + 0.6 + 0.6 + 0.6 + 0.6)) == ERR)
                        $_2104088059 = 'ERR|' . SubStr($_1085140466, round(0 + 3));
                    elseif (SubStr($_1085140466, (962 - 2 * 481), round(0 + 0.75 + 0.75 + 0.75 + 0.75)) == STP)
                        $_2104088059 = STP . M . '|' . SubStr($_1085140466, round(0 + 1.5 + 1.5));
                    else
                        $_2104088059 = 'ERR|' . UNK02;
                } elseif ($_1670231357 == L) {
                    $_1085140466 = CUpdateControllerSupport::UpdateLangs();
                    if ($_1085140466 == FIN)
                        $_2104088059 = FIN;
                    elseif (SubStr($_1085140466, (886 - 2 * 443), round(0 + 0.75 + 0.75 + 0.75 + 0.75)) == ERR)
                        $_2104088059 = 'ERR|' . SubStr($_1085140466, round(0 + 0.75 + 0.75 + 0.75 + 0.75));
                    elseif (SubStr($_1085140466, (920 - 2 * 460), round(0 + 0.6 + 0.6 + 0.6 + 0.6 + 0.6)) == STP)
                        $_2104088059 = STP . L . '|' . SubStr($_1085140466, round(0 + 1.5 + 1.5));
                    else
                        $_2104088059 = 'ERR|' . UNK03;
                } else {
                    $_2104088059 = 'ERR|' . UNK2;
                }
            } else {
                $_2104088059 = 'ERR|' . UNK3;
            }
        }
        if ($_2104088059 == FIN)
            CUpdateControllerSupport::Finish();
        return $_2104088059;
    }
    function CollectVersionsFile()
    {
        $_1315163656 = $_SERVER[DOCUMENT_ROOT] . US_SHARED_KERNEL_PATH . '/modules/versions.php';
        @unlink($_1315163656);
        $_1827294717 = '';
        $_511273306  = CUpdateClient::GetCurrentModules($_1827294717, false);
        if (StrLen($_1827294717) <= (1116 / 2 - 558)) {
            $_1310172535 = fopen($_1315163656, w);
            fwrite($_1310172535, '<' . '?' );
            fwrite($_1310172535, '$arVersions = array(' );
            foreach ($_511273306 as $_1468069767 => $_49807099)
                fwrite($_1310172535,  '' . htmlspecialchars($_1468069767) . ' => ' . htmlspecialchars($_49807099) . '', '');
            fwrite($_1310172535, ');' );
            fwrite($_1310172535, '?' . '>');
            fclose($_1310172535);
        }
    }
}
?>