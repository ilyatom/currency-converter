<?php

class CurrencyConverter {

	/**
	 * Конвертирует рубли в любую валюту по актуальному курсу Центробанка
	 * @param $amount — сумма в рублях
	 * @param $toCurrency — буквенное обозначение валюты (например, 'KZT')
	 * @return bool|float — сумма в валюте или false
	 */

        public function ConvertRub($amount,$toCurrency) {
		$rate = $this->GetRate($toCurrency);
		$r = ceil($rate * $amount);
		return $r;
	}

	private function today() {
		return date('d/m/Y');
	}

        private function GetXML() {
		$r = file_get_contents('http://www.cbr.ru/scripts/XML_daily.asp?date_req='.$this->today());
		$xml = simplexml_load_string($r);
		return $xml;
	}

	private function GetRateFromXML($currency) {
		$xml = $this->GetXML();
		foreach ($xml->Valute as $valute) {
			if ($valute->CharCode == $currency) {
				$value = str_replace(',', '.', $valute->Value);
				$rate = $valute->Nominal / $value;
			}
		}
		if (isset($rate)) {
			$r = $rate;
		} else {
			$r = false;
		}
		return $r;
	}

	private function GetRateFromCookie($currency) {
		if ($this->IsSetCurrencyCookie($currency)) {
			$parts = explode('_', $this->GetRateCookie($currency));
			$rate = $parts[0];
			$r = $rate;
		} else {
			$r = false;
		}
		return $r;
	}

	private function SetRateCookie($currency, $rate) {
		setcookie($currency, $rate.'_'.$this->today());
	}
        
        private function GetRateCookie($currency) {
                return filter_input(INPUT_COOKIE, $currency);
        }
        
        private function IsSetCurrencyCookie($currency){
            if ($this->GetRateCookie($currency) != FALSE and $this->GetRateCookie($currency) != NULL) {
                return TRUE;
            }else {
                return FALSE;
            }
        }

	private function GetRate($currency) {
		if ($this->IsSetCurrencyCookie($currency)) {
			$parts = explode('_', $this->GetRateCookie($currency));
			$date = $parts[1];
			if ($date == $this->today()) {
				$rate = $this->GetRateFromCookie($currency);
			} else {
				$rate = $this->GetRateFromXML($currency);
				$this->SetRateCookie($currency, $rate);
			}
		} else {
			$rate = $this->GetRateFromXML($currency);
			$this->SetRateCookie($currency, $rate);
		}
		return $rate;
	}
}
