<?
class CCrmExternalSaleProxy
{
	private $externalSaleId = 0;

	private $scheme = "http";
	private $server = null;
	private $port = '80';

	private $proxyScheme = null;
	private $proxyServer = null;
	private $proxyPort = null;
	private $proxyUserName = null;
	private $proxyUserPassword = null;
	private $proxyUsed = false;

	private $userName = null;
	private $userPassword = null;

	private $fp = null;
	private $socketTimeout = 15;
	private $connected = false;
	private $userAgent = 'BitrixCRM client';
	private $cookie = array();

	private $arError = array();

	public function __construct($saleId)
	{
		$this->externalSaleId = intval($saleId);
		$dbResult = CCrmExternalSale::GetList(array(), array("ID" => $this->externalSaleId, "ACTIVE" => "Y"));
		if ($arResult = $dbResult->Fetch())
		{
			$this->scheme = ((strtolower($arResult["SCHEME"]) == "https") ? "https" : "http");
			$this->server = $arResult["SERVER"];
			$this->port = $arResult["PORT"];
			$this->userName = $arResult["LOGIN"];
			$this->userPassword = $arResult["PASSWORD"];
			if (!empty($arResult["COOKIE"]))
			{
				$this->cookie = unserialize($arResult["COOKIE"]);
				if (!is_array($this->cookie))
					$this->cookie = array();
			}
		}
		else
		{
			$this->AddError("PA1", "External site is not found");
		}

		$this->connected = false;

		$arProxySettings = CCrmExternalSale::GetProxySettings();
		if (is_array($arProxySettings) && isset($arProxySettings["PROXY_HOST"]) && (strlen($arProxySettings["PROXY_HOST"]) > 0))
		{
			$this->proxyScheme = $arProxySettings["PROXY_SCHEME"];
			$this->proxyServer = $arProxySettings["PROXY_HOST"];
			$this->proxyPort = $arProxySettings["PROXY_PORT"];
			$this->proxyUserName = $arProxySettings["PROXY_USERNAME"];
			$this->proxyUserPassword = $arProxySettings["PROXY_PASSWORD"];
			$this->proxyUsed = true;
		}
	}

	public function IsInitialized()
	{
		return (!empty($this->server));
	}

	public function GetUrl()
	{
		return $this->scheme."://".$this->server.((intval($this->port) > 0) ? ":".$this->port : "");
	}

	public function Connect()
	{
		if ($this->connected)
			return true;

		$requestScheme = $this->scheme;
		$requestServer = $this->server;
		$requestPort = $this->port;
		if ($this->proxyUsed)
		{
			$requestScheme = $this->proxyScheme;
			$requestServer = $this->proxyServer;
			$requestPort = $this->proxyPort;
		}

		switch ($requestScheme)
		{
			case 'https':
				if (!function_exists("openssl_verify"))
				{
					$this->AddError("EC0", "OpenSSL PHP extention required");
					$this->connected = false;
					return false;
				}

				$requestScheme = 'ssl://';
				$requestPort = ($requestPort === null) ? 443 : $requestPort;
				break;

			case 'http':
				$requestScheme = '';
				$requestPort = ($requestPort === null) ? 80 : $requestPort;
				break;

			default:
				$this->AddError("EC1", "Invalid protocol");
				$this->connected = false;
				return false;
		}

		$this->fp = @fsockopen($requestScheme.$requestServer, $requestPort, $errno, $errstr, $this->socketTimeout);

		if (!$this->fp)
		{
			$this->AddError($errno, $errstr);
			$this->connected = false;
			return false;
		}
		else
		{
			socket_set_blocking($this->fp, 1);
			$this->connected = true;
			return true;
		}
	}

	public function Disconnect()
	{
		if (!$this->connected)
			return;

		fclose($this->fp);
		$this->connected = false;
	}

	public function GetErrors()
	{
		return $this->arError;
	}

	private function AddError($code, $message)
	{
		$this->arError[] = array($code, $message);
	}

	private function ClearErrors()
	{
		$this->arError = array();
	}

	public function Send($request)
	{
		$busOriginalMethod = null;
		$response = null;

		$authAttempts = 0;

		$i = 0;
		while (true)
		{
			$i++;
			if ($i > 4)
				break;

			$this->SendRequest($request);
			$response = $this->GetResponse();

			if (!is_null($response))
			{
				if (isset($response["HEADERS"]['Location']) && !is_null($response["HEADERS"]['Location']) && in_array($response["STATUS"]["CODE"], array(301, 302, 303, 305, 307)))
				{
					$arLocation = parse_url($response["HEADERS"]['Location']);
					$request["PATH"] = $arLocation["path"].(isset($arLocation["query"]) ? "?".$arLocation["query"] : "").(isset($arLocation["fragment"]) ? "?".$arLocation["fragment"] : "");

					if ($busOriginalMethod != null)
					{
						$request["METHOD"] = $busOriginalMethod;
						unset($request["BODY"]["AUTH_FORM"]);
						unset($request["BODY"]["TYPE"]);
						unset($request["BODY"]["USER_LOGIN"]);
						unset($request["BODY"]["USER_PASSWORD"]);
					}
					continue;
				}
				elseif ((($response["STATUS"]["CODE"] == 401) || (strpos($response["BODY"], "form_auth") !== false) || (strpos($response["BODY"], "top.BX.WindowManager.Get().Authorize") !== false)) && ($authAttempts < 2))
				{
					$authAttempts++;

					$request = $this->Authenticate($request, $response);

					if (is_null($request))
						return null;

					continue;
				}
				elseif (strpos($response["BODY"], "form_auth") !== false)
				{
					$authAttempts++;

					$busOriginalMethod = $request["METHOD"];
					$request["METHOD"] = "POST";
					if (!isset($request["BODY"]))
						$request["BODY"] = array();
					$request["BODY"]["AUTH_FORM"] = "Y";
					$request["BODY"]["TYPE"] = "AUTH";
					$request["BODY"]["USER_LOGIN"] = $this->userName;
					$request["BODY"]["USER_PASSWORD"] = $this->userPassword;

					continue;
				}
			}

			break;
		}

		return $response;
	}

	private function Authenticate($request, $response)
	{
		$authenticate = $response["HEADERS"]['WWW-Authenticate'];
		$authenticateProxy = $response["HEADERS"]['Proxy-Authenticate'];
		//if (is_null($authenticate) && is_null($authenticateProxy))
		//	return null;

		if (is_null($authenticate) && is_null($authenticateProxy))
			$authenticate = "Basic";

		if (!is_null($authenticate) && !is_array($authenticate))
			$authenticate = array($authenticate);
		if (!is_null($authenticateProxy) && !is_array($authenticateProxy))
			$authenticateProxy = array($authenticateProxy);

		if (!is_null($authenticate))
		{
			$arAuth = array();
			foreach ($authenticate as $auth)
			{
				$auth = trim($auth);
				$p = strpos($auth, " ");
				if ($p !== false)
					$arAuth[strtolower(substr($auth, 0, $p))] = trim(substr($auth, $p));
				else
					$arAuth[strtolower($auth)] = "";
			}

			if (array_key_exists("digest", $arAuth))
				$request = $this->AuthenticateDigest(self::ExtractArray($arAuth["digest"]), $request, "Authorization");
			elseif (array_key_exists("basic", $arAuth))
				$request = $this->AuthenticateBasic($request, "Authorization");
			else
				return null;
		}

		if (!is_null($authenticateProxy))
		{
			$arAuthProxy = array();
			foreach ($authenticateProxy as $auth)
			{
				$auth = trim($auth);
				$p = strpos($auth, " ");
				if ($p !== false)
					$arAuthProxy[strtolower(substr($auth, 0, $p))] = trim(substr($auth, $p));
				else
					$arAuthProxy[strtolower($auth)] = "";
			}

			if (array_key_exists("digest", $arAuthProxy))
				$request = $this->AuthenticateDigest(self::ExtractArray($arAuthProxy["digest"]), $request, "Proxy-Authorization");
			elseif (array_key_exists("basic", $arAuthProxy))
				$request = $this->AuthenticateBasic($request, "Proxy-Authorization");
			else
				return null;
		}

		return $request;
	}

	private static function ExtractArray($str)
	{
		$arResult = array();

		$ar = explode(",", $str);
		foreach ($ar as $v)
		{
			list($x1, $x2) = explode("=", $v);
			$arResult[trim($x1)] = trim(trim($x2), '"\'');
		}

		return $arResult;
	}

	private function AuthenticateDigest($arDigestRequest, $request, $verb = "Authorization")
	{
		// qop="auth",algorithm=MD5-sess,nonce="+Upgraded+v1fdcb1e18d2cc7a72322c81c0d8d2a3c332f7908ef0dfcb01aa9fb63930eadf5722dc8f6ce7b82912353531b18360cd62382a6c2433939d3f",charset=utf-8,realm="Digest"

		$username = $this->userName;
		$password = $this->userPassword;
		if ($verb == "Proxy-Authorization")
		{
			$username = $this->proxyUserName;
			$password = $this->proxyUserPassword;
		}

		$cn = md5(uniqid());

		$a1 = md5($username.':'.$arDigestRequest["realm"].':'.$password).":".$arDigestRequest["nonce"].":".$cn;
		$a2 = $request["METHOD"].":".$request["PATH"];
		$hash = md5(md5($a1).":".$arDigestRequest["nonce"].":00000001:".$cn.":".$arDigestRequest["qop"].":".md5($a2));

		$request["HEADERS"][$verb] = sprintf(
			"Digest username=\"%s\",realm=\"%s\",nonce=\"%s\",uri=\"%s\",cnonce=\"%s\",nc=00000001,algorithm=%s,response=\"%s\",qop=\"%s\",charset=utf-8",
			$username,
			$arDigestRequest["realm"],
			$arDigestRequest["nonce"],
			$request["PATH"],
			$cn,
			$arDigestRequest["algorithm"],
			$hash,
			$arDigestRequest["qop"]
		);

		return $request;
	}

	private function AuthenticateBasic($request, $verb = "Authorization")
	{
		// realm="test-exch2007"
		$username = $this->userName;
		$password = $this->userPassword;
		if ($verb == "Proxy-Authorization")
		{
			$username = $this->proxyUserName;
			$password = $this->proxyUserPassword;
		}

		$request["HEADERS"][$verb] = sprintf(
			"Basic %s",
			base64_encode($username.":".$password)
		);

		return $request;
	}

	private function SendRequest($request)
	{
		if (!$this->connected)
			$this->Connect();

		if (!$this->connected)
			return;

		fputs($this->fp, $this->RequestToString($request));
	}

	private function GetResponse()
	{
		if (!$this->connected)
			return null;

		$arHeaders = array();
		$body = "";

		while ($line = fgets($this->fp, 4096))
		{
			if ($line == "\r\n")
				break;

			$arHeaders[] = trim($line);
		}

		if (count($arHeaders) <= 0)
			return null;

		$bChunked = $bConnectionClosed = false;
		$contentLength = null;
		foreach ($arHeaders as $value)
		{
			if (!$bChunked && preg_match("#Transfer-Encoding:\s*chunked#i", $value))
				$bChunked = true;
			if (!$bConnectionClosed && preg_match('#Connection:\s*close#i', $value))
				$bConnectionClosed = true;
			if (is_null($contentLength))
			{
				if (preg_match('#Content-Length:\s*([0-9]*)#i', $value, $arMatches))
					$contentLength = intval($arMatches[1]);
				if (preg_match('#HTTP/1\.1\s+204#i', $value))
					$contentLength = 0;
			}
		}

		if ($bChunked)
		{
			do
			{
				$line = fgets($this->fp, 4096);
				$line = strtolower($line);

				$chunkSize = "";
				$i = 0;
				while ($i < strlen($line))
				{
					$c = substr($line, $i, 1);
					if (in_array($c, array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f")))
						$chunkSize .= $c;
					else
						break;
					$i++;
				}

				$chunkSize = hexdec($chunkSize);

				if ($chunkSize > 0)
				{
					$lb = $chunkSize;
					$body1 = '';
					while ($lb > 0)
					{
						$body1 .= fread($this->fp, $lb);
						$lb = $chunkSize - ((function_exists('mb_strlen') ? mb_strlen($body1, 'latin1') : strlen($body1)));
					}

					$body .= $body1;
				}

				fgets($this->fp, 4096);
			}
			while ($chunkSize);
		}
		elseif ($contentLength === 0)
		{
		}
		elseif ($contentLength > 0)
		{
			$lb = $contentLength;
			while ($lb > 0)
			{
				$body .= fread($this->fp, $lb);
				$lb = $contentLength - ((function_exists('mb_strlen') ? mb_strlen($body, 'latin1') : strlen($body)));
			}
		}
		else
		{
			stream_set_timeout($this->fp, 0);

			while (!feof($this->fp))
			{
				$body .= fread($this->fp, 4096);
				if (substr($body, -9) == "\r\n\r\n0\r\n\r\n")
				{
					$body = substr($body, 0, -9);
					break;
				}
			}

			stream_set_timeout($this->fp, $this->socketTimeout);
		}

		if ($bConnectionClosed)
			$this->Disconnect();

		$arHeaders = $this->ParseHeaders($arHeaders);
		if (is_null($arHeaders) || ($arHeaders["STATUS"]["VERSION"] != 'HTTP/1.1' && $arHeaders["STATUS"]["VERSION"] != 'HTTP/1.0'))
			return null;

		$response = array(
			"STATUS" => $arHeaders["STATUS"],
			"HEADERS" => $arHeaders["HEADERS"],
			"CONTENT" => $arHeaders["CONTENT"],
			"BODY" => $body
		);

		return $response;
	}

	private function ParseHeaders($arHeaders)
	{
		if (!is_array($arHeaders) || (count($arHeaders) <= 0))
			return null;

		$arResult = array("STATUS" => array(), "HEADERS" => array(), "CONTENT" => array());

		// First line should be a HTTP status line (see http://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html#sec6)
		// Format is: HTTP-Version SP Status-Code SP Reason-Phrase CRLF
		list($httpVersion, $statusCode, $reasonPhrase) = explode(' ', $arHeaders[0], 3);
		$arResult["STATUS"] = array(
			'VERSION' => $httpVersion,
			'CODE' => $statusCode,
			'PHRASE' => $reasonPhrase
		);

		// get the response header fields
		// See http://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html#sec6
		for ($i = 1, $cnt = count($arHeaders); $i < $cnt; $i++)
		{
			$ar = explode(':', $arHeaders[$i]);
			$name = array_shift($ar);
			$value = implode(":", $ar);

			if (!array_key_exists($name, $arResult["HEADERS"]))
			{
				$arResult["HEADERS"][$name] = trim($value);
			}
			elseif (is_array($arResult["HEADERS"][$name]))
			{
				$arResult["HEADERS"][$name][] = trim($value);
			}
			else
			{
				$ar = array($arResult["HEADERS"][$name], trim($value));
				$arResult["HEADERS"][$name] = $ar;
			}
		}

		if (isset($arResult["HEADERS"]["Content-Type"]))
		{
			$ar1 = explode(";", $arResult["HEADERS"]["Content-Type"]);
			$arResult["CONTENT"]["TYPE"] = trim($ar1[0]);
			foreach ($ar1 as $v1)
			{
				$ar2 = explode("=", $v1);
				if (trim($ar2[0]) == "charset")
				{
					$arResult["CONTENT"]["ENCODING"] = trim($ar2[1]);
					break;
				}
			}
		}

		if (isset($arResult["HEADERS"]["Set-Cookie"]))
		{
			$ar = $arResult["HEADERS"]["Set-Cookie"];
			if (!is_array($ar))
				$ar = array($arResult["HEADERS"]["Set-Cookie"]);
			foreach ($ar as $ar1)
			{
				$ar2 = explode(";", $ar1);
				$ar3 = explode("=", $ar2[0]);
				$this->cookie[trim($ar3[0])] = $ar3[1];
			}
			CCrmExternalSale::Update($this->externalSaleId, array("COOKIE" => serialize($this->cookie)));
		}

		return $arResult;
	}

	private function RequestToString($request)
	{
		$path = $request["PATH"];
		if ($this->proxyUsed)
			$path = $this->scheme."://".$this->server.((intval($this->port) > 0) ? ":".$this->port : "").$path;

		$body = null;
		if (isset($request["BODY"]) && is_array($request["BODY"]) && count($request["BODY"]) > 0)
		{
			$body = http_build_query($request["BODY"]);
			if ((!isset($request["UTF"]) || !$request["UTF"]) && !defined("BX_UTF"))
				$body = CharsetConverter::ConvertCharset($body, SITE_CHARSET, "UTF-8");
		}

		$buffer = sprintf("%s %s HTTP/1.0\r\n", $request["METHOD"], $path);
		$buffer .= sprintf("Host: %s%s\r\n", $this->server, ($this->port > 0 ? ":".$this->port : ""));

		$buffer .= sprintf("User-Agent: %s\r\n", $this->userAgent);
		if ($body != null)
			$buffer .= "Content-type: application/x-www-form-urlencoded; charset=UTF-8\r\n";
		if (is_array($this->cookie) && count($this->cookie) > 0)
		{
			$buffer .= "Cookie: ";
			$fl = false;
			foreach ($this->cookie as $k => $v)
			{
				if ($fl)
					$buffer .= "; ";
				$buffer .= sprintf("%s=%s", $k, $v);
				$fl = true;
			}
			$buffer .= "\r\n";
		}

		if (isset($request["HEADERS"]) && is_array($request["HEADERS"]))
		{
			foreach ($request["HEADERS"] as $key => $value)
			{
				if (!is_array($value))
					$value = array($value);

				foreach ($value as $value1)
					$buffer .= sprintf("%s: %s\r\n", $key, $value1);
			}
		}
		if ($body != null)
			$buffer .= sprintf("Content-length: %s\r\n", ((function_exists('mb_strlen') ? mb_strlen($body, 'latin1') : strlen($body))));
		$buffer .= "\r\n";

		if ($body != null)
			$buffer .= $body;

		return $buffer;
	}
}
