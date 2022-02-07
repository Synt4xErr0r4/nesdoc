<?php

/* check for GET parameter 'e' consisting of 3 digits; default to 404 (not found) otherwise */
if(isset($_GET['e']) && is_string($_GET['e']) && preg_match('/^\d{3}$/', $_GET['e']))
    $code = intval($_GET['e']);
else $code = 404;

/* get and encode the request URL used by some error codes */
if(isset($_SERVER['REQUEST_URL']))
    $reqUrl = htmlspecialchars($_SERVER['REQUEST_URL'], ENT_HTML5);
else $reqUrl = '?';

/* get the request method (usually GET) */
$reqMethod = $_SERVER['REQUEST_METHOD'];

/* Most of these are redundant, but I'll keep them here anyways */
$codes = [
    400 => [ 'Bad Request',              "Your browser (or proxy) sent a request that this server could not understand." ],
    401 => [ 'Unauthorized',             "This server could not verify that you are authorized to access the URL \"$reqUrl\". You either supplied the wrong credentials (e.g., bad password), or your browser doesn't understand how to supply the credentials required." ],
    403 => [ 'Forbidden',                "You don\'t have permission to access the requested object. It is either read-protected or not readable by the server." ],
    404 => [ 'Not found',                "The requested URL was not found on this server." ],
    405 => [ 'Method not allowed',       "The $reqMethod method is not allowed for the requested URL." ],
    408 => [ 'Request time out',         "The server closed the network connection because the browser didn\'t finish the request within the specified time." ],
    410 => [ 'Gone',                     "The requested URL is no longer available on this server and there is no forwarding address." ],
    411 => [ 'Length required',          "A request with the $reqMethod method requires a valid <code>Content-Length</code> header." ],
    412 => [ 'Precondition failed',      "The precondition on the request for the URL failed positive evaluation." ],
    413 => [ 'Request entity too large', "The $reqMethod method does not allow the data transmitted, or the data volume exceeds the capacity limit." ],
    414 => [ 'Request uri too large',    "The length of the requested URL exceeds the capacity limit for this server. The request cannot be processed." ],
    415 => [ 'Unsupported Media Type',   "The server does not support the media type transmitted in the request." ],
    500 => [ 'Internal Server Error',    "The server encountered an internal error and was unable to complete your request." ],
    501 => [ 'Not implemented',          "The server does not support the action requested by the browser." ],
    502 => [ 'Bad Gateway',              "The proxy server received an invalid response from an upstream server." ],
    503 => [ 'Service Unavailable',      "The server is temporarily unable to service your request due to maintenance downtime or capacity problems. Please try again later. " ],
    506 => [ 'Variant also varies',      "A variant for the requested entity is itself a negotiable resource. Access not possible." ]
];

/* redirect to 404 page if code doesn't exist */
if(!isset($codes[$code]))
    die(header('Location: /error'));

$pageInfo = [ 'title' => "Error $code", 'script' => 'index' ];
require_once 'private/header.php';
?>

<h1>Error <?=$code?></h1>
<h2><?=$codes[$code][0]?></h2>
<p><?=$codes[$code][1]?></p>

<button onclick="javascript:history.back()" class="btn btn-primary">Go back</button>

<?php require_once 'private/footer.php'; ?>