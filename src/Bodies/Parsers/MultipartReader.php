<?php //declare(strict_types=1);

namespace JuanchoSL\HttpData\Bodies\Parsers;

use JuanchoSL\HttpData\Contracts\BodyParsers;
use Psr\Http\Message\StreamInterface;
/**
 * Base on
 * @link https://foxpa.ws/manually-parse-multipart-form-data
 */
class MultipartReader implements BodyParsers
{

	protected $resource;
	protected ?string $boundary = null;
	/**
	 * Summary of data
	 * @var array<string, mixed>
	 */
	protected array $data = [];
	/**
	 * Summary of files
	 * @var array<string, array<mixed,mixed>>
	 */
	protected array $files = [];


	public function __construct(StreamInterface $resource, ?string $boundary = null)
	{
		$handler = fopen('php://memory', 'rw');
		if (empty($handler)) {
			throw new \RuntimeException("The Stream can not be created");
		}
		fwrite($handler, (string) $resource);
		fseek($handler, 0);
		$this->resource = $resource;
		$this->boundary = $boundary;
		[$this->data, $this->files] = $this->fromResource($handler, $this->boundary);
	}

	public function toPostGlobals(): void
	{
		[$_POST, $_FILES] = $this->getBodyParts();
	}

	/**
	 * Summary of getBodyParts
	 * @return array<int, array<string, mixed>>
	 */
	public function getBodyParts(): array
	{
		return [$this->getBodyParams(), $this->getBodyFiles()];
	}
	/**
	 * Summary of getBodyParams
	 * @return array<string, mixed>
	 */
	public function getBodyParams(): array
	{
		return $this->data;
	}

	/**
	 * Summary of getBodyFiles
	 * @return array<string, array<mixed,mixed>>
	 */
	public function getBodyFiles(): array
	{
		return $this->files;
	}

	/**
	 * Summary of fromResource
	 * @param mixed $stream
	 * @param mixed $boundary
	 * @return array<int, array<string, mixed>>
	 */
	protected function fromResource($stream, ?string $boundary = null): array
	{
		$return = array('', '');

		$partInfo = null;

		while (($lineN = fgets($stream)) !== false) {
			if (strpos($lineN, '--') === 0) {
				if (!isset($boundary) || $boundary == null) {
					$boundary = rtrim($lineN);
				}
				continue;
			}

			$line = rtrim($lineN);

			if ($line == '') {
				if (!empty($partInfo['Content-Disposition']['filename'])) {
					self::parse_file($stream, $boundary, $partInfo, $return[1]);
				} elseif ($partInfo != null) {
					self::parse_variable($stream, $boundary, $partInfo['Content-Disposition']['name'], $return[0]);
				}
				$partInfo = null;
				continue;
			}

			$delim = strpos($line, ':');

			$headerKey = substr($line, 0, $delim);
			$headerVal = ltrim($line, $delim + 1);

			$partInfo[$headerKey] = self::parse_header_value($headerVal, $headerKey);
		}
		fclose($stream);
		parse_str(trim($return[0], '&'), $return[0]);
		parse_str(trim($return[1], '&'), $return[1]);
		$return[1] = $this->fileInputParser($return[1]);
		return $return;
	}

	/**
	 * Summary of parse_header_value
	 * @param string $line
	 * @param string $header
	 * @return array<string|string>
	 */
	protected function parse_header_value(string $line, string $header = ''): array
	{
		$retval = array();
		$regex = '/(^|;)\s*(?P<name>[^=:,;\s"]*):?(=("(?P<quotedValue>[^"]*(\\.[^"]*)*)")|(\s*(?P<value>[^=,;\s"]*)))?/mx';

		$matches = null;
		preg_match_all($regex, $line, $matches, PREG_SET_ORDER);

		for ($i = 0; $i < count($matches); $i++) {
			$match = $matches[$i];
			$name = $match['name'];
			$quotedValue = $match['quotedValue'];
			if (empty($quotedValue)) {
				$value = $match['value'];
			} else {
				$value = $quotedValue;//stripcslashes($quotedValue);
			}
			if ($name == $header && $i == 0) {
				$name = 'value';
			}
			$retval[$name] = $value;
		}
		return $retval;
	}

	protected function parse_variable($stream, string $boundary, string $name, &$array)
	{
		$fullValue = '';
		$lastLine = null;
		while (($lineN = fgets($stream)) !== false && strpos($lineN, $boundary) !== 0) {
			if ($lastLine != null) {
				$fullValue .= $lastLine;
			}
			$lastLine = $lineN;
		}

		if ($lastLine != null) {
			$fullValue .= rtrim($lastLine, "\r\n");
		}
		//$array[$name] = $fullValue;
		$array .= "&" . $name . "=" . $fullValue;

	}

	/**
	 * Summary of parse_file
	 * @param mixed $stream
	 * @param string $boundary
	 * @param array<string, array<string, string>> $info
	 * @param string $array
	 * @return void
	 */
	protected function parse_file($stream, string $boundary, array $info, string &$array)
	{
		static $a_c = [];

		$tempdir = sys_get_temp_dir();

		$name = $info['Content-Disposition']['name'];
		if (!array_key_exists($name, $a_c)) {
			$a_c[$name] = 0;
		} else {
			$a_c[$name] += 1;
		}
		$fileStruct['name'] = $info['Content-Disposition']['filename'];
		$fileStruct['type'] = $info['Content-Type']['value'];

		//$array[$name] = &$fileStruct;


		if (empty($tempdir)) {
			$fileStruct['error'] = UPLOAD_ERR_NO_TMP_DIR;
			//return;
		} else {

			$tempname = tempnam($tempdir, 'php_upl');
			$outFP = fopen($tempname, 'wb');
			if ($outFP === false) {
				$fileStruct['error'] = UPLOAD_ERR_CANT_WRITE;
				//return;
			}

			$lastLine = null;
			while (($lineN = fgets($stream, 4096)) !== false) {
				if ($lastLine != null) {
					if (strpos($lineN, $boundary) === 0)
						break;
					if (fwrite($outFP, $lastLine) === false) {
						$fileStruct = UPLOAD_ERR_CANT_WRITE;
						//return;
					}
				}
				$lastLine = $lineN;
			}

			if ($lastLine != null) {
				if (fwrite($outFP, rtrim($lastLine, '\r\n')) === false) {
					$fileStruct['error'] = UPLOAD_ERR_CANT_WRITE;
					//return;
				}
			}
			$fileStruct['tmp_name'] = $tempname;
			$fileStruct['error'] = UPLOAD_ERR_OK;
			$fileStruct['size'] = filesize($tempname);
		}
		$array .= "&" . urldecode(http_build_query([str_replace('[]', '[' . $a_c[$name] . ']', $name) => $fileStruct]));
	}

	/**
	 * Summary of fileInputParser
	 * @param iterable<string, mixed> $arr
	 * @return array<array|array{error: null, name: null, size: null, tmp_name: null, type: null>}
	 */
	protected function fileInputParser(iterable $arr)
	{
		$output = [];
		$data = [
			'tmp_name' => null,
			'size' => null,
			'error' => null,
			'name' => null,
			'type' => null,
		];
		foreach ($arr as $name => $content) {
			$output[$name] = $data;
			if (is_iterable($content)) {
				foreach (array_keys($data) as $field) {
					if (!array_key_exists($field, $content)) {
						$output[$name][$field] = [];
					}
					static::fileInputParse($content, $field, $output[$name][$field]);
				}
			}
		}
		return $output;
	}
	protected function fileInputParse(iterable $arr, string|int $field, &$seq = [])
	{
		if (!array_key_exists($field, $arr)) {
			foreach ($arr as $key => $value) {
				$seq[$key] = [];
				static::fileInputParse($value, $field, $seq[$key]);
			}
		} else {
			$seq = $arr[$field];
		}
	}
}