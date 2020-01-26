<?php
/**
 * airports - API implementation
 *
 * @copyright kongkika.gr 01/2020 - All rights reserved
 */


/**
 * 
 * Airport record
 */
Class AirportRecord {

	/**
	 * ID
	 * @var string $id
	 */
	var $id = "";

	/**
	 * IATA Code
	 * @var string $iata
	 */
	var $iata = "";

	/**
	 * ICAO Code
	 * @var string $icao
	 */
	var $icao = "";

	/**
	 * name
	 * @var string $name
	 */
	var $name = "";

	/**
	 * Location full
	 * @var string $location
	 */
	var $location = "";

	/**
	 * Street number
	 * @var string $street_number
	 */
	var $street_number = "";

	/**
	 * Street name
	 * @var string $street
	 */
	var $street = "";

	/**
	 * City name
	 * @var string $city
	 */
	var $city = "";

	/**
	 * Country name
	 * @var string $county
	 */
	var $county = "";

	/**
	 * State name
	 * @var string $state
	 */
	var $state = "";

	/**
	 * Country ISO code
	 * @var string $country_iso
	 */
	var $country_iso = "";

	/**
	 * Country name full
	 * @var string $country
	 */
	var $country = "";

	/**
	 * Postal code
	 * @var string $postal_code
	 */
	var $postal_code = "";

	/**
	 * Phone
	 * @var string $phone
	 */
	var $phone = "";

	/**
	 * Latitude
	 * @var string $latitude
	 */
	var $latitude = "";

	/**
	 * Longtitude
	 * @var string $longitude
	 */
	var $longitude = "";

	/**
	 * uct
	 * @var string $uct
	 */
	var $uct = "";

	/**
	 * Website URL
	 * @var string $website
	 */
	var $website = "";

	/**
	 * Fill record
	 * @param array $array fields
	 */
	function fillRecord($array) {

		list(
			$this->id,
			$this->iata,
			$this->icao,
			$this->name,
			$this->location,
			$this->street_number,
			$this->street,
			$this->city,
			$this->country,
			$this->postal_code,
			$this->phone,
			$this->latitude,
			$this->longitude,
			$this->uct,
			$this->website
			) = $array;

	}

}


/**
 * Airport class
 * Get airport data / information using the rapidapi.com api
 * 
 */
Class Airport extends AirportRecord {

	/**
	 * Debug mode
	 * @var boolean debug mode
	 */
	var $debug = false;

	/**
	 * Debug log
	 * @var array Debug log
	 */
	var $debugLog = array();

	/**
	 * Url to cURL
	 * @var string $url API URL
	 */
	var $url = "https://airport-info.p.rapidapi.com/airport";

	/**
	 * API Host
	 * @var string $host api host
	 */
	var $apihost = "airport-info.p.rapidapi.com";

	/**
	 * API key
	 * @var string $apikey API key
	 */
	var $apikey = "9123d739a1msh117127d3958ababp10a79fjsn20e3f247ad25";

	/**
	 * IATA Code
	 * @var string $iata IATA Code of airport
	 */
	var $iata = "";

	/**
	 * API Response
	 * @var string $response
	 */
	var $response;

	/**
	 * Constructor
	 * @param array $CONFIG optional array with keys {'url', 'apikey'}
	 * @param $CONFIG array
	 */
	function __construct($CONFIG=array()) {
		if(isset($CONFIG['apikey']))
			$this->apikey = $CONFIG['apikey'];
	}

	/**
	 * Log error
	 * @param string $func function name error occurred
	 * @param string $desc error description
	 * @return boolean
	 */
	private function _logError($func, $desc="") {

		if($this->debug==true)
			$this->debugLog[] = "[airports error on function: '".$func."' ] DESC: ".$desc;
		return true;

	}

	/**
	 * @param string $url
	 * @return boolean
	 */
	private function _APICall($url) {

		// Validate input
		if(filter_var($url, FILTER_VALIDATE_URL)==false) {
			$this->_logError(__FUNCTION__, "Invalid URL");
			return false;
		}

		if($this->debug==true)
			$this->debugLog[] = "[INFO] Request URL: ".$url;

		// Init curl
		$curl = curl_init();

		// Curl options
		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"x-rapidapi-host: ".$this->apihost."",
				"x-rapidapi-key: ".$this->apikey.""
			)
		);

		curl_setopt_array($curl, $options);

		// Transmit data
		$response = curl_exec($curl);

		// Check for errors
		$err = curl_error($curl);
		curl_close($curl);
		if($err) {
			$this->_logError(__FUNCTION__, print_r($err, true));
			return false;
		}

		// Store response
		$this->response = $response;

		return true;

	}

	/**
	 * Run service to get airport data
	 * @param string $airport 3-letter IATA Code or 4-letter ICAO Code
	 * @return boolean
	 */
	public function populateAirport($airport) {

		// Build $url
		if(mb_strlen($airport)==3) {
			$url = $this->url."?iata=".$airport;
		} elseif(mb_strlen($airport)==4) {
			$url = $this->url."?icao=".$airport;
		} else {
			$this->_logError(__FUNCTION__, "Invalid airport code: ".$airport);
			return false;
		}

		// Run Service
		$rc = $this->_APICall($url);
		if($rc==false) {
			$this->_logError(__FUNCTION__, "API Call Failed");
			return false;
		}

		// $this->fillRecord();
		$this->response = json_decode($this->response, true);
		
		// No airport found
		if(isset($this->response['error'])) {
			if(isset($this->response['error']['text']))
				$desc = $this->response['error']['text'];
			else
				$desc = "airport [".$airport."] not found";
			$this->_logError(__FUNCTION__, $desc);
			return false;
		}
		$record = array(
			$this->response['id'],
			$this->response['iata'],
			$this->response['icao'],
			$this->response['name'],
			$this->response['location'],
			$this->response['street_number'],
			$this->response['street'],
			$this->response['city'],
			$this->response['country'],
			$this->response['postal_code'],
			$this->response['phone'],
			$this->response['latitude'],
			$this->response['longitude'],
			$this->response['uct'],
			$this->response['website']
		);
		$this->fillRecord($record);

	}

}
