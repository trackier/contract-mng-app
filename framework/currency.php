<?php
namespace Framework;

class Currency extends Base {
	/**
	 * @readwrite
	 * @var string
	 */
	protected $_base = 'usd';

	/**
	 * @readwrite
	 * x Base = 1 USD i.e 1 USD = x Base
	 * So this map shows how much of the base value would it take to make 1 USD
	 * @var array
	 */
	protected $_defaultMap = [
        'inr' => 66,
		'aed' => 3.67,
		'aud' => 1.3,
		'bgn' => 1.6651,
		'brl' => 3.3198,
		'cad' => 1.2541,
		'chf' => 0.9788,
		'cny' => 6.6225,
		'czk' => 22.044,
		'dkk' => 6.3366,
		'eur' => 0.9,
		'gbp' => 0.8,
		'hkd' => 7.76,
		'hrk' => 6.4293,
		'huf' => 267.84,
		'idr' => 13497.68,
		'ils' => 3.521,
        'pkr' => 104,
		'jpy' => 113.49,
		'krw' => 1076.8,
		'kwd' => 0.3,
		'mxn' => 19.511,
		'myr' => 4.23147,
		'nok' => 8.3492,
		'nzd' => 1.4261,
		'pen' => 3.44,
		'php' => 51.2028,
		'pln' => 3.5,
		'ron' => 3.9163,
		'rub' => 57.5,
		'sek' => 8.3799,
		'sar' => 3.75,
		'sgd' => 1.3439,
		'thb' => 33.3823,
		'try' => 3.7,
		'usd' => 1,
		'vnd' => 22727.26,
		'uah' => 27.93,
		'zar' => 12.705,
		'ugx' => 3675.59,
		'tnd' => 2.83,
		'xaf' => 591.83,
		'ngn' => 361.50,
		'mad' => 9.61,
		'kes' => 100.50,
		'ghs' => 5.44,
		'egp' => 15.84,
		'dzd' => 119.78,
		'rmb' => 7.09,
		'afn' => 76.95,
		'npr' => 119.06,
		'bdt' => 85.88,
		'lkr' => 202.94,
		'cop' => 3974.50,
		'twd' => 28.83,
		'clp' => 946.60,
		'ars' => 136.73,
		'byn' => 2.52,
		'kzt' => 467.30,
		// crypto currency
		'btc' => 0.000069,
		'bitcoin' => 0.000069
	];

	/**
	 * @readwrite
	 * @var array
	 */
	protected $_symbolMap = [
		'bgn'=> 'лв',
		'chf'=> 'CHF',
		'czk'=> 'Kč',
		'dkk'=> 'DKK',
		'eur'=> '€',
		'gbp'=> '£',
		'hrk'=> 'kn',
		'gel'=> '₾',
		'huf'=> 'ft',
		'nok'=> 'NOK',
		'pln'=> 'zł',
		'rub'=> '₽',
		'ron'=> 'lei',
		'sek'=> 'SEK',
		'try'=> '₺',
		'uah'=> '₴',
		'aed'=> 'د.إ',
		'ils'=> '₪',
		'kes'=> 'Ksh',
		'mad'=> '.د.م',
		'ngn'=> '₦',
		'zar'=> 'R',
		'brl'=> 'R$',
		'cad'=> '$',
		'clp'=> '$',
		'cop'=> '$',
		'mxn'=> '$',
		'pen'=> 'S/.',
		'usd'=> '$',
		'aud'=> '$',
		'bdt'=> '৳',
		'cny'=> '元',
		'hkd'=> 'HK$',
		'idr'=> 'Rp',
		'inr'=> '₹',
		'jpy'=> '¥',
		'myr'=> 'RM',
		'nzd'=> '$',
		'php'=> '₱',
		'pkr'=> 'Rs',
		'sgd'=> 'S$',
		'krw'=> '₩',
		'lkr'=> 'Rs',
		'thb'=> '฿',
		'vnd'=> '₫',
		'btc'=> '₿',
		'xrp'=> 'XRP',
		'xmr'=> 'ɱ',
		'ltc'=> 'Ł',
		'eth'=> 'Ξ',
		'rmb' => '¥',
		'afn' => '؋',
		'npr' => 'रू',
		'twd' => 'NT$',
		'clp' => 'CLP$',
		'ars' => 'Arg$',
		'byn' => 'Br',
		'kzt' => '₸',

		'bitcoin' => '₿'
	];

	protected $_symbolCurrencyMap = [
		"лв" => "bgn",
		"CHF" => "chf",
		"Kč" => "czk",
		"DKK" => "dkk",
		"€" => "eur",
		"£" => "gbp",
		"kn" => "hrk",
		"₾" => "gel",
		"ft" => "huf",
		"NOK" => "nok",
		"zł" => "pln",
		"₽" => "rub",
		"lei" => "ron",
		"SEK" => "sek",
		"₺" => "try",
		"₴" => "uah",
		"د.إ" => "aed",
		"₪" => "ils",
		"Ksh" => "kes",
		".د.م" => "mad",
		"₦" => "ngn",
		"R" => "zar",
		'R$' => "brl",
		'$' => "usd",
		"S/." => "pen",
		"৳" => "bdt",
		"元" => "cny",
		'HK$' => "hkd",
		"Rp" => "idr",
		"₹" => "inr",
		"¥" => "jpy",
		"RM" => "myr",
		"₱" => "php",
		"Rs" => "pkr",
		'S$' => "sgd",
		"₩" => "krw",
		"฿" => "thb",
		"₫" => "vnd",
		"₿" => "btc",
		"XRP" =>"xrp",
		"ɱ" => "xmr",
		"Ł" => "ltc",
		"Ξ" => "eth",
		"؋" => "afn",
		'रू' => 'npr',
		'NT$' => 'twd',
		'CLP$' => 'clp',
		'Arg$' =>	'ars',
		'Br'  =>'byn',
		'₸' => 'kzt'
	];

	const CURRENCY_TEXT_SYMBOL_MAP = [
		'bgn'=> 'лв',
		'chf'=> 'CHF',
		'czk'=> 'Kč',
		'dkk'=> 'DKK',
		'eur'=> '€',
		'gbp'=> '£',
		'hrk'=> 'kn',
		'gel'=> '₾',
		'huf'=> 'ft',
		'nok'=> 'NOK',
		'pln'=> 'zł',
		'rub'=> '₽',
		'ron'=> 'lei',
		'sek'=> 'SEK',
		'try'=> '₺',
		'uah'=> '₴',
		'aed'=> 'د.إ',
		'ils'=> '₪',
		'kes'=> 'Ksh',
		'mad'=> '.د.م',
		'ngn'=> '₦',
		'zar'=> 'R',
		'brl'=> 'R$',
		'cad'=> '$',
		'cop'=> '$',
		'mxn'=> '$',
		'pen'=> 'S/.',
		'aud'=> '$',
		'bdt'=> '৳',
		'cny'=> '元',
		'hkd'=> 'HK$',
		'idr'=> 'Rp',
		'inr'=> '₹',
		'jpy'=> '¥',
		'myr'=> 'RM',
		'nzd'=> '$',
		'php'=> '₱',
		'pkr'=> 'Rs',
		'sgd'=> 'S$',
		'usd'=> '$',
		'krw'=> '₩',
		'lkr'=> 'Rs',
		'thb'=> '฿',
		'vnd'=> '₫',
		'btc'=> '₿',
		'xrp'=> 'XRP',
		'xmr'=> 'ɱ',
		'ltc'=> 'Ł',
		'eth'=> 'Ξ',
		'rmb' => '¥',
		'afn' => '؋',
		'npr' => 'रू',
		'bitcoin' => '₿',
		'twd' => 'NT$',
		'clp' => 'CLP$',
		'ars' => 'Arg$',
		'byn' => 'Br',
		'kzt' => '₸',
	];

	protected $_textSymbolMap = self::CURRENCY_TEXT_SYMBOL_MAP;

	public function &getDefaultMap() {
		return $this->_defaultMap;
	}

	public function &getSymbolCurrencyMap() {
		return $this->_symbolCurrencyMap;
	}

	public function &getSymbolMap() {
		return $this->_symbolMap;
	}

	public function getCurrencySelectOpts() {
		$result = [];
		foreach ($this->_defaultMap as $key => $value) {
			$symbol = $this->_symbolMap[$key] ?? '';
			$result[strtoupper($key)] = ($symbol ? $symbol . ' ' : '') . strtoupper($key);
		}
		return $result;
	}

	public function setBase($value) {
		if (empty(trim($value))) {
			$value = "USD";
		}
		$value = strtolower($value);
		$keys = array_keys($this->_defaultMap);
		if (! in_array($value, $keys)) {
			throw new Core\Exception\Implementation("Invalid Currency Code: $value");
		}
		$this->_base = $value;
	}

	/**
	 * @param float $value Set 1 Base = x USD
	 * @throws Core\Exception\Argument Throws exception if value is not provided
	 */
	public function setBaseMap($value = null) {
		if (!is_numeric($value)) {
			throw new Core\Exception\Argument('Argument $value should be numeric!!');
		}
		$this->_defaultMap[$this->base] = $value;
	}

	public function getRate() {
		return $this->defaultMap[$this->base] ?? 1;
	}

	public function getSymbol() {
		return $this->_symbolMap[$this->base] ?? strtoupper($this->base);
	}

	public function getTextSymbol() {
		return $this->_textSymbolMap[$this->base] ?? strtoupper($this->base);
	}

	public function getConversionRate($currency, $ratesMap = []) {
		$currency = strtolower($currency);
		if (! isset($this->_defaultMap[$currency])) {
			throw new \Exception("Invalid currency: $currency");
		}
		$baseExRate = $ratesMap[$this->base] ?? $this->_defaultMap[$this->base];
		$currExRate = $ratesMap[$currency] ?? $this->_defaultMap[$currency];
		return $currExRate/$baseExRate;
	}

	public function toUsd($amountInBase, $ratesMap = []) {
		$amount = (float) $amountInBase;
		$rate = $this->getRate();
		if ($ratesMap && isset($ratesMap[$this->base]) && $ratesMap[$this->base]) {
			$rate = $ratesMap[$this->base];
		}

		$v = $amount / $rate;
		return $v;
	}

	public function toRegional($amount, $amountCurrency = 'usd', $ratesMap = []) {
		$amountCurrency = strtolower($amountCurrency);
		
		if ($amountCurrency !== 'usd') {
			$obj = new static(['base' => $amountCurrency]);
			$usdAmount = $obj->toUsd($amount, $ratesMap);
		} else {
			$usdAmount = (float) $amount;
		}

		$rate = $this->getRate();
		if ($ratesMap && isset($ratesMap[$this->base]) && $ratesMap[$this->base]) {
			$rate = $ratesMap[$this->base];
		}
		$v = $usdAmount * $rate;
		return $v;
	}

	public function niceNumber($n, $places = 6, $p = true, $format = true) {
		// first strip any formatting;
		$n = (0+str_replace(",", "", $n));
		// is this a number?
		if (!is_numeric($n)) return false;

		$prefix = $this->getSymbol();

		// now filter it;
		$num = false;
		if ($n > 1000000000000) $num = round(($n/1000000000000), 4).'T';
		elseif ($n > 1000000000) $num = round(($n/1000000000), 4).'B';
		elseif ($n > 1000000) $num = round(($n/1000000), 3).'M';
		elseif ($n > 1000) $num = round(($n/1000), 2).'K';

		if (is_float($n)) $n = round($n, $places);
		if ($num !== false && $format === true) {
			if ($p !== false) $num = $prefix . ' ' . $num;
			return $num;
		}

		if ($p !== false) {
			return $prefix . ' ' . $n;
		}
		return $n;
	}
}
