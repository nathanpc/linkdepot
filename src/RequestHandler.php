<?php
/**
 * RequestHandler.php
 * A universal and minimalist way to handle web requests.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

namespace LinkDepot;

require_once __DIR__ . "/../functions.php";
require_once __DIR__ . "/../vendor/autoload.php";

/**
 * A universal and minimalist way to handle web requests.
 */
abstract class RequestHandler {
	protected $method;
	protected $action;
	protected $format;
	protected $handlers;

	// Response type definitions.
	public const UNKNOWN = 0;
	public const PLAIN = 0;
	public const HTML = 1;
	public const JSON = 2;
	public const XML = 3;

	/**
	 * Constructs a new response handler object.
	 */
	public function __construct() {
		$this->initialize();
	}

	/**
	 * Properly initializes the state of the object and gathers some common
	 * parameters that will be required during rendering.
	 *
	 * This function is required to be called before any other statements in the
	 * constructor.
	 */
	protected function initialize() {
		// Extract the common parameters.
		$this->method = $_SERVER["REQUEST_METHOD"];
		$this->action = strtolower(urlparam("action"));
		$this->infer_format(strtolower(urlparam("format", "html")));

		// Add all of the common HTTP methods to the handlers array.
		$this->handlers = array(
			"GET" => array(),
			"POST" => array(),
			"PUT" => array(),
			"DELETE" => array()
		);
	}

	/**
	 * Builds up the handler callable object for use in add_handler. The
	 * implementation of this function must always be:
	 *
	 * return array($this, $method);
	 *
	 * @param string $method Name of the method to be called as a handler.
	 *
	 * @return callable Callback for a request handler.
	 */
	abstract protected function handler($method);

	/**
	 * Adds a request handling function to be executed when a specific web
	 * request comes in.
	 *
	 * @param string   $method  HTTP method that triggers this handler.
	 * @param string   $action  Associated request parameter.
	 * @param callable $handler Function to be called when the request
	 *                          parameters match.
	 */
	public function add_handler($method, $action, $handler) {
		$this->handlers[$method][$action] = $handler;
	}

	/**
	 * Renders the output to the client.
	 */
	public function render() {
		if (!$this->handle_request())
			http_response_code(405);
	}

	/**
	 * Handles the web request automatically using our internal handlers.
	 *
	 * @return bool TRUE if the handler existed or FALSE if we were unable to
	 *              handle the request.
	 */
	private function handle_request() {
		if (isset($this->handlers[$this->method][$this->action])) {
			call_user_func($this->handlers[$this->method][$this->action]);
			return true;
		}

		return false;
	}

	/**
	 * Renders the default view for an object given the requested format.
	 *
	 * @param Renderable $item Renderable item to be rendered using in the
	 *                         requested format.
	 */
	public function render_default($item) {
		switch ($this->format) {
		case self::HTML:
			echo $item->as_html(true);
			break;
		case self::JSON:
			echo $item->as_json(true);
			break;
		case self::XML:
			echo $item->as_xml(null, true)->asXML();
			break;
		default:
			throw new \Exception("Unknown format to render");
			break;
		}
	}

	/**
	 * Sets the Content-Type header using a specific MIME type or automatically
	 * via the requested format.
	 *
	 * @param string $mime MIME type or NULL if it should be detected
	 *                     automatically.
	 */
	public function set_content_type($mime = null) {
		if (is_null($mime)) {
			switch ($this->format) {
			case self::HTML:
				header("Content-Type: text/html");
				return;
			case self::JSON:
				header("Content-Type: application/json");
				return;
			case self::XML:
				header("Content-Type: application/xml");
				return;
			default:
				header("Content-Type: text/plain");
				return;
			}
		}

		header("Content-Type: $mime");
	}

	/**
	 * Replies to the client with an appropriately formatted, given the request
	 * format,  error and immediatly halts further processing of the request.
	 *
	 * @param int       $code      HTTP response code.
	 * @param string    $message   Descriptive error message.
	 * @param Exception $exception Exception object if present.
	 */
	public function error($code, $message, $exception = null) {
		switch ($this->format) {
		case self::HTML:
			self::error_html($code, $message, $exception);
			break;
		case self::JSON:
			self::error_json($code, $message, $exception);
			break;
		case self::XML:
			self::error_xml($code, $message, $exception);
			break;
		default:
			self::error_plain($code, $message, $exception);
			break;
		}
	}

	/**
	 * Replies to the client with an plain text error and immediatly halts
	 * further processing of the request.
	 *
	 * @param int       $code      HTTP response code.
	 * @param string    $message   Descriptive error message.
	 * @param Exception $exception Exception object if present.
	 */
	public static function error_plain($code, $message, $exception = null) {
		http_response_code($code);
		header("Content-Type: text/plain");

		// Show this glorious plain text response.
		echo "Error: $message";
		if (!is_null($exception))
			echo "\n\n$exception";

		die();
	}

	/**
	 * Replies to the client with an HTML error page and immediatly halts
	 * further processing of the request.
	 *
	 * @param int       $code      HTTP response code.
	 * @param string    $message   Descriptive error message.
	 * @param Exception $exception Exception object if present.
	 */
	public static function error_html($code, $message, $exception = null) {
		http_response_code($code);
		header("Content-Type: text/html");

		// Show a nice looking error page.
		require(__DIR__ . "/../templates/head.php"); 
		echo "<p><b>Error:</b> {$message}.</p>";
		if (!is_null($exception))
			echo "\n<pre><code>{$exception}</code></pre>";
		require(__DIR__ . "/../templates/footer.php"); 

		die();
	}

	/**
	 * Replies to the client with an JSON error and immediatly halts further
	 * processing of the request.
	 *
	 * @param int       $code      HTTP response code.
	 * @param string    $message   Descriptive error message.
	 * @param Exception $exception Exception object if present.
	 */
	public static function error_json($code, $message, $exception = null) {
		http_response_code($code);
		header("Content-Type: application/json");

		// Build up the JSON object.
		$json = array("error" => array("message" => $message));
		if (!is_null($exception)) {
			$json["error"]["exception"] = array(
				"report" => strval($exception),
				"message" => $exception->getMessage(),
				"trace" => $exception->getTrace()
			);
		}

		// Reply with the JSON object.
		echo json_encode($json);

		die();
	}

	/**
	 * Replies to the client with an XML error and immediatly halts further
	 * processing of the request.
	 *
	 * @param int       $code      HTTP response code.
	 * @param string    $message   Descriptive error message.
	 * @param Exception $exception Exception object if present.
	 */
	public static function error_xml($code, $message, $exception = null) {
		http_response_code($code);
		header("Content-Type: application/xml");

		// Build up the XML object.
		$xml = xmldoc("error");
		$xml->addChild("message", $message);
		if (!is_null($exception)) {
			$node = $xml->addChild("exception");
			$node->addChild("report", strval($exception));
			$node->addChild("message", $exception->getMessage());

			$node = $node->addChild("stacktrace");
			foreach ($exception->getTrace() as $trace) {
				$tn = $node->addChild("trace");
				if (isset($trace["file"]))
					$tn->addChild("file", $trace["file"]);
				if (isset($trace["line"]))
					$tn->addChild("line", $trace["line"]);
				if (isset($trace["function"]))
					$tn->addChild("function", $trace["function"]);
				if (isset($trace["args"])) {
					$tn = $tn->addChild("arguments");
					foreach ($trace["args"] as $arg)
						$tn->addChild("arg", $arg);
				}
			}
		}

		// Reply with the XML document.
		echo $xml->asXML();

		die();
	}

	/**
	 * Gets the link ID parameter or prints an error message if it's missing and
	 * halts the execution of the script.
	 *
	 * @return int Requested link ID.
	 */
	protected function id_param() {
		$id = urlparam("id");
		if (is_null($id))
			self::error(400, "Required parameter id wasn't set");

		return $id;
	}

	/**
	 * Tries to infer a response format from a string and sets the internal
	 * format property accordingly.
	 *
	 * @param string $format Textual definition of a format.
	 */
	protected function infer_format($format) {
		if ($format == "html") {
			$this->format = self::HTML;
		} else if ($format == "json") {
			$this->format = self::JSON;
		} else if ($format == "xml") {
			$this->format = self::XML;
		} else {
			$this->format = self::UNKNOWN;
		}
	}

	/**
	 * Checks if the requested action matches the given one.
	 *
	 * @param string $action Action parameter to check againt requested one.
	 *
	 * @return bool TRUE if the given action matches the currently requested
	 *              one.
	 */
	public function is_action($action) {
		return $this->action == $action;
	}

	/**
	 * Checks if the requested format matches the given one.
	 *
	 * @param int $format Format identifier to check againt requested one.
	 *
	 * @return bool TRUE if the given format matches the currently requested
	 *              one.
	 */
	public function is_format($format) {
		return $format == $this->format;
	}
}
