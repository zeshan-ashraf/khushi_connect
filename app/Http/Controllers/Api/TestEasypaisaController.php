<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use DateTime;
use DateTimeZone;

class TestEasypaisaController extends Controller
{
    /**
     * Default log channel for this controller
     */
    protected $logChannel = 'payin';

    /**
     * Test EasyPaisa API with custom cURL request
     */
    public function testEasypaisaRequest(Request $request)
    {
        $startTime = microtime(true);
        $requestId = uniqid('test_');
        
        Log::channel($this->logChannel)->info('========== TEST EASYPAISA REQUEST STARTED ==========', [
            'request_id' => $requestId,
            'params' => $request->all()
        ]);

        try {
            // Get credentials from config
            $apiMode = config('easypaisa.mode'); // sandbox or production
            
            if ($apiMode === 'sandbox') {
                $apiUrl = config('easypaisa.sandbox_url');
                $username = config('easypaisa.sandbox_username');
                $password = config('easypaisa.sandbox_password');
                $storeId = config('easypaisa.sandbox_storeid');
            } else {
                $apiUrl = config('easypaisa.prod_url');
                $username = config('easypaisa.prod_username');
                $password = config('easypaisa.prod_password');
                $storeId = config('easypaisa.prod_storeid');
            }

            // Prepare credentials
            $credentials = base64_encode($username . ':' . $password);

            // Prepare request data
            $postData = [
                'orderId' => $request->orderId ?? 'test-' . time(),
                'storeId' => $storeId,
                'transactionAmount' => $request->amount ?? '10',
                'transactionType' => 'MA',
                'mobileAccountNo' => $request->phone ?? '03316215445',
                'emailAddress' => $request->email ?? 'testing@khushipay.com'
            ];

            Log::channel($this->logChannel)->info('Test request prepared', [
                'request_id' => $requestId,
                'api_url' => $apiUrl,
                'api_mode' => $apiMode,
                'post_data' => $postData,
                'credentials_length' => strlen($credentials)
            ]);

            // Method 1: Using Laravel HTTP Client with custom timeout
            $response1 = $this->testWithLaravelHttp($apiUrl, $credentials, $postData, $requestId);
            
            // Method 2: Using raw cURL
            $response2 = $this->testWithRawCurl($apiUrl, $credentials, $postData, $requestId);
            
            // Method 3: Using Guzzle directly
            $response3 = $this->testWithGuzzle($apiUrl, $credentials, $postData, $requestId);

            $executionTime = microtime(true) - $startTime;

            Log::channel($this->logChannel)->info('========== TEST COMPLETED ==========', [
                'request_id' => $requestId,
                'execution_time' => $executionTime
            ]);

            return response()->json([
                'status' => 'success',
                'request_id' => $requestId,
                'execution_time' => round($executionTime, 2) . 's',
                'api_url' => $apiUrl,
                'api_mode' => $apiMode,
                'post_data' => $postData,
                'method_1_laravel_http' => $response1,
                'method_2_raw_curl' => $response2,
                'method_3_guzzle' => $response3,
            ], 200);

        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            
            Log::channel($this->logChannel)->error('Test EasyPaisa request failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'execution_time' => $executionTime
            ]);

            return response()->json([
                'status' => 'error',
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'execution_time' => round($executionTime, 2) . 's',
            ], 500);
        }
    }

    /**
     * Test with Laravel HTTP Client
     */
    private function testWithLaravelHttp($apiUrl, $credentials, $postData, $requestId)
    {
        try {
            Log::channel($this->logChannel)->info('Testing with Laravel HTTP Client', [
                'request_id' => $requestId,
                'method' => 'Laravel HTTP'
            ]);

            $startTime = microtime(true);
            
            $response = Http::timeout(30)
                ->connectTimeout(15)
                ->withHeaders([
                    'credentials' => $credentials,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($apiUrl, $postData);
            
            $executionTime = microtime(true) - $startTime;

            $result = [
                'success' => true,
                'status_code' => $response->status(),
                'response' => $response->json(),
                'execution_time' => round($executionTime, 2) . 's',
                'headers' => $response->headers()
            ];

            Log::channel($this->logChannel)->info('Laravel HTTP test completed', [
                'request_id' => $requestId,
                'result' => $result
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::channel($this->logChannel)->error('Laravel HTTP test failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test with raw cURL
     */
    private function testWithRawCurl($apiUrl, $credentials, $postData, $requestId)
    {
        try {
            Log::channel($this->logChannel)->info('Testing with raw cURL', [
                'request_id' => $requestId,
                'method' => 'Raw cURL'
            ]);

            $startTime = microtime(true);
            
            $curl = curl_init();
            
            curl_setopt_array($curl, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postData),
                CURLOPT_HTTPHEADER => [
                    'credentials: ' . $credentials,
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_VERBOSE => true
            ]);

            $response = curl_exec($curl);
            $curlError = curl_error($curl);
            $curlErrno = curl_errno($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlInfo = curl_getinfo($curl);
            
            curl_close($curl);
            
            $executionTime = microtime(true) - $startTime;

            if ($response === false) {
                Log::channel($this->logChannel)->error('Raw cURL request failed', [
                    'request_id' => $requestId,
                    'curl_error' => $curlError,
                    'curl_errno' => $curlErrno,
                    'curl_info' => $curlInfo
                ]);

                return [
                    'success' => false,
                    'error' => $curlError,
                    'error_code' => $curlErrno,
                    'execution_time' => round($executionTime, 2) . 's',
                    'curl_info' => $curlInfo
                ];
            }

            $result = [
                'success' => true,
                'http_code' => $httpCode,
                'response' => json_decode($response, true),
                'raw_response' => $response,
                'execution_time' => round($executionTime, 2) . 's',
                'curl_info' => [
                    'total_time' => $curlInfo['total_time'],
                    'namelookup_time' => $curlInfo['namelookup_time'],
                    'connect_time' => $curlInfo['connect_time'],
                    'pretransfer_time' => $curlInfo['pretransfer_time'],
                    'starttransfer_time' => $curlInfo['starttransfer_time'],
                    'redirect_time' => $curlInfo['redirect_time'],
                    'primary_ip' => $curlInfo['primary_ip'] ?? null,
                    'primary_port' => $curlInfo['primary_port'] ?? null,
                ]
            ];

            Log::channel($this->logChannel)->info('Raw cURL test completed', [
                'request_id' => $requestId,
                'result' => $result
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::channel($this->logChannel)->error('Raw cURL test failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test with Guzzle directly
     */
    private function testWithGuzzle($apiUrl, $credentials, $postData, $requestId)
    {
        try {
            Log::channel($this->logChannel)->info('Testing with Guzzle', [
                'request_id' => $requestId,
                'method' => 'Guzzle'
            ]);

            $startTime = microtime(true);
            
            $client = new \GuzzleHttp\Client([
                'timeout' => 30,
                'connect_timeout' => 15,
                'http_errors' => false,
                'verify' => true,
            ]);

            $response = $client->post($apiUrl, [
                'json' => $postData,
                'headers' => [
                    'credentials' => $credentials,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            $executionTime = microtime(true) - $startTime;

            $result = [
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'response' => json_decode($response->getBody()->getContents(), true),
                'execution_time' => round($executionTime, 2) . 's',
                'headers' => $response->getHeaders()
            ];

            Log::channel($this->logChannel)->info('Guzzle test completed', [
                'request_id' => $requestId,
                'result' => $result
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::channel($this->logChannel)->error('Guzzle test failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Simple cURL test - Direct implementation
     */
    public function simpleCurlTest(Request $request)
    {
        $url = "https://easypay.easypaisa.com.pk/easypay-service/rest/v4/initiate-ma-transaction/";

        // Get parameters from query string (GET) or request body (POST)
        $data = [
            "orderId"        => $request->input('orderId') ?? $request->query('orderId', "khushi-conn-02a"),
            "amount"         => $request->input('amount') ?? $request->query('amount', "10"),
            "phone"          => $request->input('phone') ?? $request->query('phone', "03316215445"),
            "email"          => $request->input('email') ?? $request->query('email', "testing@khushipay.com"),
            "client_email"   => $request->input('client_email') ?? $request->query('client_email', "testing@khushipay.com"),
            "payment_method" => $request->input('payment_method') ?? $request->query('payment_method', "easypaisa"),
            "callback_url"   => $request->input('callback_url') ?? $request->query('callback_url', "https://xxxxxx.c7pay.net/api/callback/order/D17455730586267"),
        ];

        $headers = [
            'Content-Type: application/json',
            // Add your credentials if Easypaisa requires authentication:
            // 'credentials: YOUR_CREDENTIALS_HERE'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Timeout settings - Increased for EasyPaisa API (usually takes 60+ seconds)
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 160); // Connection timeout: 120 seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 180); // Total timeout: 180 seconds (3 minutes)

        // Advanced debugging
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_STDERR, fopen('php://temp', 'w+'));
        
        // DNS and connection settings
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 160);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        
        // SSL settings
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $startTime = microtime(true);
        $response = curl_exec($ch);
        $executionTime = microtime(true) - $startTime;
        
        // Get detailed connection info
        $curlInfo = curl_getinfo($ch);

        $result = [];
        
        if (curl_errno($ch)) {
            $result = [
                'status' => 'error',
                'curl_error' => curl_error($ch),
                'curl_errno' => curl_errno($ch),
                'execution_time' => round($executionTime, 2) . 's',
                'request_data' => $data,
                'url' => $url,
                'diagnostic_info' => [
                    'total_time' => $curlInfo['total_time'] ?? 0,
                    'namelookup_time' => $curlInfo['namelookup_time'] ?? 0,
                    'connect_time' => $curlInfo['connect_time'] ?? 0,
                    'pretransfer_time' => $curlInfo['pretransfer_time'] ?? 0,
                    'starttransfer_time' => $curlInfo['starttransfer_time'] ?? 0,
                    'redirect_time' => $curlInfo['redirect_time'] ?? 0,
                    'primary_ip' => $curlInfo['primary_ip'] ?? null,
                    'primary_port' => $curlInfo['primary_port'] ?? null,
                    'local_ip' => $curlInfo['local_ip'] ?? null,
                    'local_port' => $curlInfo['local_port'] ?? null,
                    'http_code' => $curlInfo['http_code'] ?? 0,
                ],
                'diagnosis' => $this->diagnoseCurlError($curlInfo, $executionTime)
            ];
            
            Log::channel($this->logChannel)->error('Simple cURL test failed', $result);
        } else {
            $result = [
                'status' => 'success',
                'response' => $response,
                'decoded_response' => json_decode($response, true),
                'execution_time' => round($executionTime, 2) . 's',
                'request_data' => $data,
                'url' => $url,
                'diagnostic_info' => [
                    'total_time' => $curlInfo['total_time'] ?? 0,
                    'namelookup_time' => $curlInfo['namelookup_time'] ?? 0,
                    'connect_time' => $curlInfo['connect_time'] ?? 0,
                    'pretransfer_time' => $curlInfo['pretransfer_time'] ?? 0,
                    'starttransfer_time' => $curlInfo['starttransfer_time'] ?? 0,
                    'primary_ip' => $curlInfo['primary_ip'] ?? null,
                    'http_code' => $curlInfo['http_code'] ?? 0,
                ]
            ];
            
            Log::channel($this->logChannel)->info('Simple cURL test success', $result);
        }
        
        curl_close($ch);

        return response()->json($result);
    }

    /**
     * Diagnose cURL connection error
     */
    private function diagnoseCurlError($curlInfo, $executionTime)
    {
        $namelookupTime = $curlInfo['namelookup_time'] ?? 0;
        $connectTime = $curlInfo['connect_time'] ?? 0;
        $primaryIp = $curlInfo['primary_ip'] ?? null;
        
        $diagnosis = [];
        
        // DNS Resolution Check
        if ($namelookupTime == 0 || $namelookupTime >= $executionTime) {
            $diagnosis[] = '❌ DNS Resolution Failed - Cannot resolve easypay.easypaisa.com.pk';
            $diagnosis[] = '→ Your server cannot find the IP address of EasyPaisa';
            $diagnosis[] = '→ Check DNS settings or use alternative DNS (8.8.8.8)';
        } else {
            $diagnosis[] = '✅ DNS Resolution OK - Took ' . round($namelookupTime * 1000, 2) . 'ms';
            if ($primaryIp) {
                $diagnosis[] = '→ Resolved to IP: ' . $primaryIp;
            }
        }
        
        // Connection Check
        if ($connectTime == 0 || $connectTime >= $executionTime) {
            $diagnosis[] = '❌ TCP Connection Failed - Cannot connect to EasyPaisa server';
            $diagnosis[] = '→ Port 443 might be blocked by firewall';
            $diagnosis[] = '→ Server IP might be blacklisted by EasyPaisa';
            $diagnosis[] = '→ Network routing issue to Pakistan servers';
        } else {
            $diagnosis[] = '✅ TCP Connection OK - Took ' . round($connectTime * 1000, 2) . 'ms';
        }
        
        // Timeout Analysis
        if ($executionTime >= 119) { // Close to 120s timeout
            $diagnosis[] = '⏱️ Hit Connection Timeout Limit (120s)';
            $diagnosis[] = '→ Connection attempt timed out';
            $diagnosis[] = '→ EasyPaisa server not responding';
            
            if ($namelookupTime > 0 && $namelookupTime < 5) {
                $diagnosis[] = '→ DNS works but connection hangs';
                $diagnosis[] = '→ Likely: Firewall blocking outbound connections';
                $diagnosis[] = '→ Solution: Contact Hostinger to whitelist easypay.easypaisa.com.pk';
            }
        }
        
        return implode("\n", $diagnosis);
    }

    /**
     * Test network connectivity to EasyPaisa server
     */
    public function testConnectivity()
    {
        $requestId = uniqid('conn_test_');
        
        try {
            $apiMode = config('easypaisa.mode');
            $apiUrl = $apiMode === 'sandbox' 
                ? config('easypaisa.sandbox_url') 
                : config('easypaisa.prod_url');

            // Parse URL to get host
            $urlParts = parse_url($apiUrl);
            $host = $urlParts['host'] ?? null;
            $port = $urlParts['port'] ?? 443;

            if (!$host) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid API URL',
                    'api_url' => $apiUrl
                ], 400);
            }

            $results = [
                'api_url' => $apiUrl,
                'host' => $host,
                'port' => $port,
                'api_mode' => $apiMode,
                'tests' => []
            ];

            // Test 1: DNS Resolution
            $startTime = microtime(true);
            $ip = gethostbyname($host);
            $dnsTime = microtime(true) - $startTime;
            
            $results['tests']['dns_resolution'] = [
                'success' => $ip !== $host,
                'ip_address' => $ip,
                'time' => round($dnsTime * 1000, 2) . 'ms'
            ];

            // Test 2: Socket Connection
            $startTime = microtime(true);
            $socket = @fsockopen($host, $port, $errno, $errstr, 10);
            $socketTime = microtime(true) - $startTime;
            
            $results['tests']['socket_connection'] = [
                'success' => $socket !== false,
                'time' => round($socketTime * 1000, 2) . 'ms',
                'error' => $socket === false ? "$errstr ($errno)" : null
            ];
            
            if ($socket) {
                fclose($socket);
            }

            // Test 3: HTTP GET Request
            try {
                $startTime = microtime(true);
                $response = Http::timeout(15)->connectTimeout(10)->get($apiUrl);
                $httpTime = microtime(true) - $startTime;
                
                $results['tests']['http_get'] = [
                    'success' => true,
                    'status_code' => $response->status(),
                    'time' => round($httpTime * 1000, 2) . 'ms'
                ];
            } catch (\Exception $e) {
                $results['tests']['http_get'] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }

            Log::channel($this->logChannel)->info('Connectivity test completed', [
                'request_id' => $requestId,
                'results' => $results
            ]);

            return response()->json([
                'status' => 'success',
                'request_id' => $requestId,
                'results' => $results
            ], 200);

        } catch (\Exception $e) {
            Log::channel($this->logChannel)->error('Connectivity test failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'request_id' => $requestId,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

