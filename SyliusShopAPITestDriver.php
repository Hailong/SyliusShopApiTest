<?php

/**
 * Test utility for Sylius shop APIs.
 *
 * @author Hailong Zhao <hailongzh@hotmail.com>
 */

require __DIR__ . '/vendor/autoload.php';

use \Curl\Curl;

class HttpClient
{
    protected $hostname;
    protected $protocol;
    protected $debug;

    public function __construct(string $hostname, string $protocol, bool $debug)
    {
        $this->hostname = strtolower($hostname);
        $this->protocol = strtolower($protocol);
        $this->debug = $debug;
    }

    protected function url(string $route, array $params = array())
    {
        $protocol = $this->protocol == 'https' ? 'https' : 'http';

        $url = sprintf('%s://%s%s', $protocol, $this->hostname, $route);

        if ($this->debug) {
            $params['XDEBUG_SESSION_START'] = 'name';
        }

        $first = true;

        foreach ($params as $key => $value) {
            if ($first) {
                $url .= '?';
                $first = false;
            } else {
                $url .= '&';
            }

            $url .= $key . '=' . urlencode($value);
        }

        return $url;
    }

    protected function response($curl) {
        $response = null;

        echo '++++++++++++++++ Response ++++++++++++++++' . "\n";

        if ($curl->error) {
            echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
            print_r($curl->response);
            $response = $curl->response;
        }

        echo '------------------------------------------' . "\n";

        return $response;
    }

    public function request($method, $route, $params = array(), $data = array(), $headers = array())
    {
        $curl = new Curl();
        $curl->setTimeout(0);

        foreach ($headers as $key => $value) {
            $curl->setHeader($key, $value);
        }

        $url = $this->url($route, $params);

        echo '++++++++++++++++ ' . $method . ' ++++++++++++++++' . "\n";
        echo $url . "\n";
        echo "Data:\n";
        print_r($data);

        $curl->$method($url, $data);

        echo '---------------- ' . $method . ' ----------------' . "\n";

        return $this->response($curl);
    }
}

function runOne($method, $route, $params = array(), $data = array(), $headers = array())
{
    static $client = null;
    static $authorizationHeader = array();

    if ($client == null) {
        $client = new HttpClient(TEST_HOSTNAME, TEST_PROTOCOL, WITH_DEBUG);
    }

    if ($route != '/shop-api/login_check') {
        if (!$authorizationHeader) {
            $authorizationHeader = unserialize(file_get_contents(__DIR__ . '/authorization.txt'));
        }
        $headers = array_merge($authorizationHeader, $headers);
    }

    $response = $client->request($method, $route, $params, $data, $headers);

    if ($route == '/shop-api/login_check') {
        $authorizationHeader = [ 'Authorization' => sprintf('Bearer %s', $response->token) ];
        file_put_contents(__DIR__ . '/authorization.txt', serialize($authorizationHeader));
    }
}

function run(array $toRun, array $testCases)
{
    if ($toRun) {
        foreach ($toRun as $num) {
            echo "\n#" . ($num) . "\n";

            $i = $num - 1;

            runOne(
                $testCases[$i][0],
                $testCases[$i][1],
                isset($testCases[$i][2]) ? $testCases[$i][2] : array(),
                isset($testCases[$i][3]) ? $testCases[$i][3] : array(),
                isset($testCases[$i][4]) ? $testCases[$i][4] : array()
            );
        }

        return;
    }

    for ($i=0; $i < count($testCases); $i++) {
        echo "\n#" . ($i + 1) . "\n";

        $testCase = $testCases[$i];

        runOne(
            $testCase[0],
            $testCase[1],
            isset($testCase[2]) ? $testCase[2] : array(),
            isset($testCase[3]) ? $testCase[3] : array(),
            isset($testCase[4]) ? $testCase[4] : array()
        );
    }
}

function TestCases($testCases)
{
    global $argc, $argv;

    $to_run = [];

    if ($argc > 1) {
        for ($i = 1; $i < $argc; $i++) {
            $to_run[] = $argv[$i];
        }
    }

    run($to_run, $testCases);
}
