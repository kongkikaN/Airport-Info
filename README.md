# Airport

airport.inc.php is a php class to get airport data by IATA or ICAO Code.
This class uses the 'Airport info' API as described on https://rapidapi.com/Active-api/api/airport-info/endpoints


## Usage

```php
$airport = new Airport();
$airport->populateAirport("LHR");
var_dump($airport);
```

## Custom Configuration

```php
$config = array(
	'apikey'	=> "Your API key"
);

$airport = new Airport($config);
$airport->populateAirport("LHR");
var_dump($airport);

```
## License
[MIT](https://choosealicense.com/licenses/mit/)
