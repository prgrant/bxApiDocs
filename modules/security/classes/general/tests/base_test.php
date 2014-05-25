<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage security
 * @copyright 2001-2013 Bitrix
 */

/**
 * Class CSecurityBaseTest
 * @since 12.5.0
 */
abstract class CSecurityBaseTest
{
	protected $internalName = "BaseTest";
	protected $isDebug = false;
	protected $params = array();
	protected $tests = array();
	protected $detailErrors = array();

	/**
	 * Return test name
	 * @return string
	 */
	public function getName()
	{
		return GetMessage("SECURITY_SITE_CHECKER_".$this->getInternalName()."_NAME");
	}

	/**
	 * Check test requirements (e.g. max_execution_time)
	 *
	 * @param array $params
	 * @return bool
	 */
	static public function checkRequirements($params = array())
	{
		return true;
	}

	/**
	 * Run test and return results
	 * @param array $params
	 * @return array
	 */
	public function check(array $params = array())
	{
		$this->initializeParams($params);
		$isSomethingFound = false;
		$neededTests = self::getParam("folder", array());
		if(is_string($neededTests) && $neededTests != "")
		{
			$neededTests = array($neededTests);
		}

		foreach($this->tests as $name => $test)
		{
			if(!empty($pNeededTests) && !in_array($name, $neededTests, true))
				continue;

			if(isset($test["params"]) && is_array($test["params"]))
			{
				$testParams = $test["params"];
			}
			else
			{
				$testParams = array();
			}

			if(!call_user_func_array(array($this, $test["method"]), $testParams))
			{
				if(isset($test["base_message_key"]) && $test["base_message_key"] != "")
				{
					if(isset($test["critical"]) && $test["critical"] != "")
					{
						$critical = $test["critical"];
					}
					else
					{
						$critical = CSecurityCriticalLevel::LOW;
					}

					$this->addUnformattedDetailError($test["base_message_key"], $critical);
				}
				$isSomethingFound = true;
			}
		}


		$result = array(
			'name' => $this->getName(),
			'problem_count' => count($this->getDetailErrors()),
			'errors' => $this->getDetailErrors(),
			'status' => !$isSomethingFound
		);

		return $result;
	}

	/**
	 * Return internal name (for technical usage)
	 * @return string
	 */
	public function getInternalName()
	{
		return $this->internalName;
	}

	/**
	 * Initialize starting params, e.g. debug mode
	 * @param array $params
	 */
	protected function initializeParams(array $params = array())
	{
		if(is_array($params) && !empty($params))
		{
			$this->params = $params;
		}
		$this->isDebug = (self::getParam("debug", false) === true);
	}

	/**
	 * @return bool
	 */
	protected function isRunOnWin()
	{
		return (strtoupper(substr(PHP_OS, 0, 3)) === "WIN");
	}

	/**
	 * Return file or dir permissions
	 * @param string $path - file path
	 * @return int
	 */
	protected static function getFilePerm($path)
	{
		if(!(is_dir($path) || is_file($path)))
			return false;

		return fileperms($path);
	}

	/**
	 * Check file or dir for write permissions
	 * @param string $path - file path
	 * @return bool
	 */
	protected static function isWorldWritable($path)
	{
		return (self::getFilePerm($path) & 0x0002) > 0;
	}

	/**
	 * Check file or dir for read permissions
	 * @param string $path - file path
	 * @return bool
	 */
	protected static function isWorldReadable($path)
	{
		return (self::getFilePerm($path) & 0x0004) > 0;
	}

	/**
	 * Check file or dir for read or write permissions
	 * @param string $path - file path
	 * @return bool
	 */
	protected static function isWorldAccessible($path)
	{
		$perms = self::getFilePerm($path);
		return ($perms & 0x0004 > 0) || ($perms & 0x0002 > 0);
	}

	/**
	 * Return param value, or default value if not present
	 * @param string $name
	 * @param string $defaultValue
	 * @return string
	 */
	protected function getParam($name, $defaultValue = "")
	{
		if(isset($this->params[$name]))
		{
			return $this->params[$name];
		}
		else
		{
			return $defaultValue;
		}
	}

	/**
	 * @return bool
	 */
	protected function isDebug()
	{
		return $this->isDebug;
	}

	/**
	 * Return errors array for checking results
	 * @return array
	 */
	protected function getDetailErrors()
	{
		return $this->detailErrors;
	}

	/**
	 * @param string $baseMessageKey
	 * @param array $placeholders
	 * @return string
	 */
	protected static function getDetailText($baseMessageKey, array $placeholders = array())
	{
		if(HasMessage($baseMessageKey."_DETAIL"))
		{
			$result = GetMessage($baseMessageKey."_DETAIL", $placeholders);
		}
		else
		{
			$result = "";
		}
		return $result;
	}

	/**
	 * @param string $baseMessageKey
	 * @param array $placeholders
	 * @return string
	 */
	protected static function getRecommendationText($baseMessageKey, array $placeholders = array())
	{
		if(HasMessage($baseMessageKey."_RECOMMENDATION"))
		{
			$result = GetMessage($baseMessageKey."_RECOMMENDATION", $placeholders);
		}
		else
		{
			$result = "";
		}
		return $result;
	}

	/**
	 * @param string $baseMessageKey
	 * @param array $placeholders
	 * @return string
	 */
	protected static function getTitleText($baseMessageKey, array $placeholders = array())
	{
		if(HasMessage($baseMessageKey))
		{
			$result = GetMessage($baseMessageKey, $placeholders);
		}
		else
		{
			$result = "";
		}
		return $result;
	}

	/**
	 * Add new error
	 * @param string $title
	 * @param string $critical
	 * @param string $detail
	 * @param string $recommendation
	 */
	protected function addDetailError($title, $critical, $detail, $recommendation = "")
	{
		$detailError = array(
			"title" => $title,
			"critical" => $critical,
			"detail" => $detail,
			"recommendation" => $recommendation
		);
		$this->pushDetailError($detailError);
	}

	/**
	 * @param array $error
	 * @return $this
	 */
	private function pushDetailError(array $error)
	{
		if(is_array($error) && !empty($error))
		{
			array_push($this->detailErrors, $error);
		}
		return $this;
	}

	/**
	 * Add new unformatted error (call formatDetailError inside)
	 *
	 * @param string $baseMessageKey
	 * @param string $critical
	 * @return $this
	 */
	protected function addUnformattedDetailError($baseMessageKey, $critical)
	{
		$detailError = self::formatDetailError($baseMessageKey, $critical);
		$this->pushDetailError($detailError);
		return $this;
	}

	/**
	 * Return formatted detail error from messages
	 * @param string $baseMessageKey
	 * @param string $critical
	 * @return array
	 */
	protected static function formatDetailError($baseMessageKey, $critical)
	{
		return array(
				"title" => self::getTitleText($baseMessageKey),
				"critical" => $critical,
				"detail" => self::getDetailText($baseMessageKey),
				"recommendation" => self::getRecommendationText($baseMessageKey)
			);
	}

	/**
	 * Return path without $_SERVER['DOCUMENT_ROOT']
	 * @param string $path
	 * @return string
	 */
	protected static function removeDocumentRoot($path)
	{
		$path = removeDocRoot($path);
		return $path;
	}
}
