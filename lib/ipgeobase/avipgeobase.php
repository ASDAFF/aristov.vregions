<?

namespace Aristov\Vregions\IpGeoBase;

class AVIpGeoBase{
	public static $cidr_optim_file_path = 'cidr_optim.txt';
	public static $cities_file_path = 'cities.txt';
	public static $csvRowLength = 1000;
	public static $csvDelimiter = "	";

	public static function getInfoAboutIP($ip){
		$codedIp = static::getIpSum($ip);

		$ipRowArr = static::findIpRow($codedIp);

		$cityArr = Array();
		if ($ipRowArr[4]){
			$cityArr = static::getCityInfo($ipRowArr[4]);
		}

		return Array(
			"id"         => $cityArr[0],
			"cityName"   => $cityArr[1],
			"oblastName" => $cityArr[2],
			"okrugName"  => $cityArr[3],
			"countyCode" => $ipRowArr[3],
			"lat"        => $cityArr[4],
			"lon"        => $cityArr[5],
		);
	}

	// получаем число вида a*256*256*256+b*256*256+c*256+d, для поиска по файлу
	public static function getIpSum($ip){
		$multiplier = 256;

		$ipArr = explode('.', $ip);

		return $ipArr[0] * $multiplier * $multiplier * $multiplier + $ipArr[1] * $multiplier * $multiplier + $ipArr[2] * $multiplier + $ipArr[3];
	}

	public static function findIpRow($codedIp){
		$answer = false;
		// у строк такой формат: нижняя часть диапазона (закодированная), верхняя часть диапазона (закодированная), нижний ip, вурхний ip, код страны, id города
		if (($handle = fopen(__DIR__.'/'.static::$cidr_optim_file_path, "r")) !== false){
			while (($data = fgetcsv($handle, static::$csvRowLength, static::$csvDelimiter)) !== false){
				if ($data[0] > $codedIp || $codedIp > $data[1]){ // наш ip вне этого диапазона
					continue;
				}

				$answer = $data;
				break;
			}
			fclose($handle);
		}

		return $answer;
	}

	public static function getCityInfo($cityId){
		$answer = false;
		if (($handle = fopen(__DIR__.'/'.static::$cities_file_path, "r")) !== false){
			while (($data = fgetcsv($handle, static::$csvRowLength, static::$csvDelimiter)) !== false){
				if ($data[0] != $cityId){
					continue;
				}

				$answer = $data;
				break;
			}
			fclose($handle);
		}

		return $answer;
	}
}