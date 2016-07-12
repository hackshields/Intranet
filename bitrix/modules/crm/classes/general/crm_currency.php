<?php
IncludeModuleLangFile(__FILE__);

class CCrmCurrency
{
	private static $BASE_CURRENCY_ID = null;
	private static $ACCOUNT_CURRENCY_ID = null;
	private static $CURRENCY_BY_LANG = array();
	protected static $LAST_ERROR = '';
	// Default currency is stub that used only when 'currency' module is not installed
	protected static $DEFAULT_CURRENCY_ID = '';

	public static function GetDefaultCurrencyID()
	{
		if(self::$DEFAULT_CURRENCY_ID !== '')
		{
			return self::$DEFAULT_CURRENCY_ID;
		}

		self::$DEFAULT_CURRENCY_ID = 'USD';

		$rsLang = CLanguage::GetByID('ru');
		if($arLang = $rsLang->Fetch())
		{
			self::$DEFAULT_CURRENCY_ID = 'RUB';
		}
		else
		{
			$rsLang = CLanguage::GetByID('de');
			if($arLang = $rsLang->Fetch())
			{
				self::$DEFAULT_CURRENCY_ID = 'EUR';
			}
		}

		return self::$DEFAULT_CURRENCY_ID;
	}

	public static function NormalizeCurrencyID($currencyID)
	{
		$currencyID = strtoupper(trim(strval($currencyID)));
		if($currencyID === 'RUR')
		{
			//RUR - is obsolete ISO4217 alfa code
			$currencyID = 'RUB';
		}

		return $currencyID;
	}

	public static function GetBaseCurrencyID()
	{
		if (!CModule::IncludeModule('currency'))
		{
			return self::GetDefaultCurrencyID();
		}

		if(!self::$BASE_CURRENCY_ID)
		{
			self::$BASE_CURRENCY_ID = CCurrency::GetBaseCurrency();
		}
		return self::$BASE_CURRENCY_ID;
	}

	// Is used in reports only
	public static function GetAccountCurrencyID()
	{
		if(!self::$ACCOUNT_CURRENCY_ID)
		{
			self::$ACCOUNT_CURRENCY_ID = COption::GetOptionString('crm', 'account_currency_id', '');
			if(!isset(self::$ACCOUNT_CURRENCY_ID[0]))
			{
				self::$ACCOUNT_CURRENCY_ID = self::GetBaseCurrencyID();
			}
		}

		return self::$ACCOUNT_CURRENCY_ID;
	}

	public static function SetAccountCurrencyID($currencyID)
	{
		$currencyID = self::NormalizeCurrencyID($currencyID);
		if($currencyID === self::$ACCOUNT_CURRENCY_ID)
		{
			return;
		}

		self::$ACCOUNT_CURRENCY_ID = $currencyID;
		COption::SetOptionString('crm', 'account_currency_id', self::$ACCOUNT_CURRENCY_ID);

		CCrmDeal::OnAccountCurrencyChange();
		CCrmLead::OnAccountCurrencyChange();
	}

	public static function GetAccountCurrency()
	{
		return self::GetByID(self::GetAccountCurrencyID());
	}

	public static function GetBaseCurrency()
	{
		if (!CModule::IncludeModule('currency'))
		{
			return false;
		}

		$baseCurrencyID = CCurrency::GetBaseCurrency();
		if(!isset($baseCurrencyID[0]))
		{
			return false;
		}
		return self::GetByID($baseCurrencyID);
	}

	public static function EnsureReady()
	{
		if(!CModule::IncludeModule('currency'))
		{
			self::$LAST_ERROR = GetMessage('CRM_CURRERCY_MODULE_WARNING');
			return false;
		}

		return true;
	}

	public static function GetByID($currencyID, $langID = '')
	{
		$currencyID = self::NormalizeCurrencyID($currencyID);

		if(!isset($currencyID[0]))
		{
			return false;
		}

		$currencies = self::GetAll($langID);
		return isset($currencies[$currencyID]) ? $currencies[$currencyID] : false;
	}

	public static function GetByName($name, $langID = '')
	{
		$name = strval($name);
		$currencies = self::GetAll($langID);
		foreach($currencies as $currency)
		{
			if(isset($currency['FULL_NAME']) && $currency['FULL_NAME'] === $name)
				return $currency;
		}
		return false;
	}

	public static function GetAll($langID = '')
	{
		if (!CModule::IncludeModule('currency'))
		{
			return array();
		}

		$langID = strval($langID);
		if(!isset($langID[0]))
		{
			$langID = LANGUAGE_ID;
		}

		$currencies = isset(self::$CURRENCY_BY_LANG[$langID]) ? self::$CURRENCY_BY_LANG[$langID] : null;
		if(!$currencies)
		{
			$currencies = array();
			$resCurrency = CCurrency::GetList(($by1 = 'sort'), ($order1 = 'asc'), $langID);
			while ($arCurrency = $resCurrency->Fetch())
			{
				$currencies[$arCurrency['CURRENCY']] = $arCurrency;
			}
			self::$CURRENCY_BY_LANG[$langID] = $currencies;
		}

		return $currencies;
	}

	public static function GetCurrencyName($currencyID, $langID = '')
	{
		$currencyID = strval($currencyID);
		if($currencyID === '')
		{
			return '';
		}

		$ID = self::NormalizeCurrencyID($currencyID);
		$currencies = self::GetAll($langID);
		return isset($currencies[$ID]) && isset($currencies[$ID]['FULL_NAME']) ? $currencies[$ID]['FULL_NAME'] : $currencyID;
	}

	public static function GetCurrencyFormatString($currencyID, $langID = '')
	{
		if (!CModule::IncludeModule('currency'))
		{
			return '#';
		}

		$currencyID = strval($currencyID);
		$langID = strval($langID);
		if(!isset($langID[0]))
		{
			$langID = LANGUAGE_ID;
		}

		$formatInfo = CCurrencyLang::GetCurrencyFormat($currencyID, $langID);
		return isset($formatInfo['FORMAT_STRING']) ? $formatInfo['FORMAT_STRING'] : '#';
	}

	public static function MoneyToString($sum, $currencyID, $formatStr = '')
	{
		if (!CModule::IncludeModule('currency'))
		{
			return number_format($sum, 2, '.', '');
		}

		$formatStr = strval($formatStr);

		$formatInfo = CCurrencyLang::GetCurrencyFormat($currencyID);
		$formatInfo['DECIMALS'] = isset($formatInfo['DECIMALS']) ?  intval($formatInfo['DECIMALS']) : 2;

		if (!isset($formatInfo['DEC_POINT']))
		{
			$formatInfo['DEC_POINT'] = '.';
		}

		if(!empty($formatInfo['THOUSANDS_VARIANT']))
		{
			$thousands = $formatInfo['THOUSANDS_VARIANT'];

			if($thousands === 'N')
			{
				$formatInfo['THOUSANDS_SEP'] = '';
			}
			elseif($thousands === 'D')
			{
				$formatInfo['THOUSANDS_SEP'] = '.';
			}
			elseif($thousands === 'C')
			{
				$formatInfo['THOUSANDS_SEP'] = ',';
			}
			elseif($thousands === 'S' || $thousands === 'B')
			{
				$formatInfo['THOUSANDS_SEP'] = chr(32);
			}
		}

		if(!isset($formatInfo['THOUSANDS_SEP']))
		{
			$formatInfo['THOUSANDS_SEP'] = '';
		}

		if(is_integer($sum) || is_float($sum))
		{
			// Stansard format for float
			$s = number_format($sum, $formatInfo['DECIMALS'], $formatInfo['DEC_POINT'], $formatInfo['THOUSANDS_SEP']);
		}
		else
		{
			// Do not convert to float to avoid data lost caused by overflow (9 999 999 999 999 999.99 ->10 000 000 000 000 000.00)
			$triadSep = strval($formatInfo['THOUSANDS_SEP']);
			$decPoint = strval($formatInfo['DEC_POINT']);
			$dec = intval($formatInfo['DECIMALS']);

			$sum = str_replace(',', '.', strval($sum));
			list($i, $d) = explode('.', $sum, 2);

			$len = strlen($i);
			$leadLen = $len % 3;
			if($leadLen === 0)
			{
				$leadLen = 3; //take a first triad
			}
			$lead = substr($i, 0, $leadLen);
			if(!is_string($lead))
			{
				$lead = '';
			}
			$triads = substr($i, $leadLen);
			if(!is_string($triads))
			{
				$triads = '';
			}
			$s = $triads !== '' ? $lead.preg_replace('/(\\d{3})/', $triadSep.'\\1', $triads) : ($lead !== '' ? $lead : '0');
			$s .= $decPoint.str_pad(substr($d, 0, $dec), $dec, '0', STR_PAD_RIGHT);
		}

		if(!empty($formatInfo['THOUSANDS_VARIANT']) && $formatInfo['THOUSANDS_VARIANT'] === 'B')
		{
			$s = str_replace(' ', '&nbsp;', $s);
		}

		if(isset($formatStr[0]))
		{
			$formatInfo['FORMAT_STRING'] = $formatStr;
		}
		elseif(empty($formatInfo['FORMAT_STRING']))
		{
			$formatInfo['FORMAT_STRING'] = '#';
		}

		return str_replace('#', $s, $formatInfo['FORMAT_STRING']);
	}

	public static function ConvertMoney($sum, $srcCurrencyID, $dstCurrencyID, $srcExchRate = -1)
	{
		$sum = doubleval($sum);

		if (!CModule::IncludeModule('currency'))
		{
			return $sum;
		}

		$srcCurrencyID = self::NormalizeCurrencyID($srcCurrencyID);
		$dstCurrencyID = self::NormalizeCurrencyID($dstCurrencyID);
		$srcExchRate = doubleval($srcExchRate);

		if($sum === 0.0 || $srcCurrencyID === $dstCurrencyID)
		{
			return $sum;
		}

		$result = 0;
		if($srcExchRate < 0)
		{
			// Use default exchenge rate
			$result = CCurrencyRates::ConvertCurrency($sum, $srcCurrencyID, $dstCurrencyID);
		}
		else
		{
			// Convert source currency to base and convert base currency to destination
			$result = CCurrencyRates::ConvertCurrency(
				doubleval($sum * $srcExchRate),
				self::GetBaseCurrencyID(),
				$dstCurrencyID
			);
		}

		$decimals = 2;
		if (CModule::IncludeModule('currency'))
		{
			$formatInfo = CCurrencyLang::GetCurrencyFormat($dstCurrencyID);
			if(isset($formatInfo['DECIMALS']))
			{
				$decimals = intval($formatInfo['DECIMALS']);
			}
		}

		$result = round($result, $decimals);
		return $result;
	}

	public static function GetExchangeRate($currencyID)
	{
		if (!CModule::IncludeModule('currency'))
		{
			return 1;
		}

		$rates = new CCurrencyRates();
		if(!($rs = $rates->_get_last_rates(date('Y-m-d'), $currencyID)))
		{
			return 1.0;
		}

		$exchRate = doubleval($rs['RATE']);
		$cnt = intval($rs['RATE_CNT']);
		if ($exchRate <= 0)
		{
			$exchRate = doubleval($rs["AMOUNT"]);
			$cnt = intval($rs['AMOUNT_CNT']);
		}
		return $cnt != 1 ? ($exchRate / $cnt) : $exchRate;
	}

	private static function ClearCache()
	{
		self::$CURRENCY_BY_LANG = array();
	}

	public static function GetLastError()
	{
		return self::$LAST_ERROR;
	}

	private static function CheckFields($action, &$arFields, $ID)
	{
		if(isset($arFields['AMOUNT_CNT']))
		{
			$arFields['AMOUNT_CNT'] = intval($arFields['AMOUNT_CNT']);
		}

		if(isset($arFields['AMOUNT']))
		{
			$arFields['AMOUNT'] = doubleval($arFields['AMOUNT']);
		}

//		if(isset($arFields['SORT']))
//		{
//			$SORT = intval($arFields['SORT']);
//			$arFields['SORT'] = ($SORT > 255 || $SORT < 0 ? 0 : $SORT);
//		}

		return true;
	}

	public static function Add($arFields)
	{
		if (!CModule::IncludeModule('currency'))
		{
			self::$LAST_ERROR = GetMessage('CRM_CURRERCY_MODULE_IS_NOT_INSTALLED');
			return false;
		}

		global $APPLICATION;

		$ID = isset($arFields['CURRENCY']) ? $arFields['CURRENCY'] : '';
		if(!self::CheckFields('ADD', $arFields, $ID))
		{
			return false;
		}

		$ID = CCurrency::Add($arFields);
		if(!$ID)
		{
			$ex = $APPLICATION->GetException();
			if ($ex)
			{
				self::$LAST_ERROR = $ex->GetString();
			}

			return false;
		}

		self::ClearCache();
		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		if (!CModule::IncludeModule('currency'))
		{
			self::$LAST_ERROR = GetMessage('CRM_CURRERCY_MODULE_IS_NOT_INSTALLED');
			return false;
		}

		global $APPLICATION;

		$arFields['CURRENCY'] = $ID;

		if(!self::CheckFields('UPDATE', $arFields, $ID))
		{
			return false;
		}

		if(!CCurrency::Update($ID, $arFields))
		{
			$ex = $APPLICATION->GetException();
			if ($ex)
			{
				self::$LAST_ERROR = $ex->GetString();
			}

			return false;
		}

		self::ClearCache();
		return true;
	}

	public static function Delete($ID)
	{
		if (!CModule::IncludeModule('currency'))
		{
			self::$LAST_ERROR = GetMessage('CRM_CURRERCY_MODULE_IS_NOT_INSTALLED');
			return false;
		}

		IncludeModuleLangFile(__FILE__);

		global $APPLICATION;

		$ID = strval($ID);
		if(!isset($ID[0]))
		{
			return false;
		}

		if($ID === self::GetBaseCurrencyID())
		{
			self::$LAST_ERROR = GetMessage('CRM_CURRERCY_ERR_DELETION_OF_BASE_CURRENCY');
			return false;
		}

		if($ID === self::GetAccountCurrencyID())
		{
			self::$LAST_ERROR = GetMessage('CRM_CURRERCY_ERR_DELETION_OF_ACCOUNTING_CURRENCY');
			return false;
		}

		if (!CCurrency::Delete($ID))
		{
			$ex = $APPLICATION->GetException();
			if ($ex)
			{
				self::$LAST_ERROR = $ex->GetString();
			}

			return false;
		}

		self::ClearCache();
		return true;
	}
}