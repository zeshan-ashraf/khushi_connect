# EasyPaisa API Testing Guide

This guide will help you test and diagnose the EasyPaisa API timeout issues.

## Test Endpoints Created

### 1. Test EasyPaisa Request (POST)
**Endpoint:** `POST /api/test-easypaisa`

This endpoint tests the EasyPaisa API using 3 different methods:
- Laravel HTTP Client
- Raw cURL
- Guzzle

**Request Body:**
```json
{
    "orderId": "khushi-conn-08a",
    "amount": "10",
    "phone": "03316215445",
    "email": "testing@khushipay.com",
    "client_email": "testing@khushipay.com",
    "payment_method": "easypaisa",
    "callback_url": "https://xxxxxx.c7pay.net/api/callback/order/D17455730586267"
}
```

**cURL Command:**
```bash
curl -X POST http://your-domain.com/api/test-easypaisa \
  -H "Content-Type: application/json" \
  -d '{
    "orderId": "khushi-conn-08a",
    "amount": "10",
    "phone": "03316215445",
    "email": "testing@khushipay.com",
    "client_email": "testing@khushipay.com",
    "payment_method": "easypaisa",
    "callback_url": "https://xxxxxx.c7pay.net/api/callback/order/D17455730586267"
  }'
```

**Response:**
```json
{
    "status": "success",
    "request_id": "test_xxxxx",
    "execution_time": "2.5s",
    "api_url": "https://easypay.easypaisa.com.pk/easypay-service/rest/v4/initiate-ma-transaction",
    "api_mode": "production",
    "post_data": {
        "orderId": "khushi-conn-08a",
        "storeId": "your_store_id",
        "transactionAmount": "10",
        "transactionType": "MA",
        "mobileAccountNo": "03316215445",
        "emailAddress": "testing@khushipay.com"
    },
    "method_1_laravel_http": {
        "success": true,
        "status_code": 200,
        "response": {},
        "execution_time": "1.2s"
    },
    "method_2_raw_curl": {
        "success": true,
        "http_code": 200,
        "response": {},
        "execution_time": "1.3s",
        "curl_info": {
            "total_time": 1.234,
            "namelookup_time": 0.1,
            "connect_time": 0.5,
            "pretransfer_time": 0.6,
            "starttransfer_time": 1.0,
            "primary_ip": "154.198.13.219",
            "primary_port": 443
        }
    },
    "method_3_guzzle": {
        "success": true,
        "status_code": 200,
        "response": {},
        "execution_time": "1.1s"
    }
}
```

### 2. Test Connectivity (GET)
**Endpoint:** `GET /api/test-easypaisa-connectivity`

This endpoint tests basic network connectivity to the EasyPaisa server.

**cURL Command:**
```bash
curl -X GET http://your-domain.com/api/test-easypaisa-connectivity
```

**Response:**
```json
{
    "status": "success",
    "request_id": "conn_test_xxxxx",
    "results": {
        "api_url": "https://easypay.easypaisa.com.pk/easypay-service/rest/v4/initiate-ma-transaction",
        "host": "easypay.easypaisa.com.pk",
        "port": 443,
        "api_mode": "production",
        "tests": {
            "dns_resolution": {
                "success": true,
                "ip_address": "154.198.13.219",
                "time": "50.5ms"
            },
            "socket_connection": {
                "success": true,
                "time": "250.3ms",
                "error": null
            },
            "http_get": {
                "success": true,
                "status_code": 200,
                "time": "1200.5ms"
            }
        }
    }
}
```

## How to Use

### Step 1: Test Connectivity First
```bash
curl -X GET http://your-domain.com/api/test-easypaisa-connectivity
```

This will show you if your server can even reach the EasyPaisa server.

### Step 2: Test Full Request
```bash
curl -X POST http://your-domain.com/api/test-easypaisa \
  -H "Content-Type: application/json" \
  -d '{
    "orderId": "khushi-conn-08a",
    "amount": "10",
    "phone": "03316215445",
    "email": "testing@khushipay.com"
  }'
```

This will test the actual EasyPaisa API request using 3 different methods.

### Step 3: Check Logs
Check your logs for detailed information:
```bash
tail -f storage/logs/payout-*.log
tail -f storage/logs/laravel-*.log
```

## Debugging Tips

### If DNS Resolution Fails:
- Your server cannot resolve the EasyPaisa domain
- Check your DNS settings
- Try using a different DNS server (e.g., 8.8.8.8, 1.1.1.1)

### If Socket Connection Fails:
- Your server can resolve the domain but cannot connect
- Check firewall settings
- Check if port 443 is allowed for outbound connections
- Contact your hosting provider

### If HTTP Request Fails with Timeout:
- The connection is established but timing out
- The EasyPaisa server might be slow or unreachable
- Try increasing timeout values
- Check if EasyPaisa API is down

### If All Tests Pass but Still Getting Errors:
- The issue might be with authentication/credentials
- Check your EasyPaisa configuration in `.env` file
- Verify your API credentials with EasyPaisa

## Configuration Check

Make sure your `.env` file has the correct EasyPaisa configuration:

```env
# EasyPaisa Configuration
EASYPAISA_MODE=production # or sandbox
EASYPAISA_TYPE=direct

# Sandbox
EASYPAISA_SANDBOX_URL=https://easypay.easypaisa.com.pk/easypay-service/rest/v4/initiate-ma-transaction
EASYPAISA_SANDBOX_USERNAME=your_sandbox_username
EASYPAISA_SANDBOX_PASSWORD=your_sandbox_password
EASYPAISA_SANDBOX_STOREID=your_sandbox_store_id
EASYPAISA_SANDBOX_HASHKEY=your_sandbox_hash_key

# Production
EASYPAISA_PROD_URL=https://easypay.easypaisa.com.pk/easypay-service/rest/v4/initiate-ma-transaction
EASYPAISA_PROD_USERNAME=your_prod_username
EASYPAISA_PROD_PASSWORD=your_prod_password
EASYPAISA_PROD_STOREID=your_prod_store_id
EASYPAISA_PROD_HASHKEY=your_prod_hash_key

# Callback URL
EASYPAISA_CALLBACK=https://your-domain.com/api/callback
```

## Expected Results

### If Everything Works:
All three methods should return successful responses with the EasyPaisa API response.

### If Timeout Occurs:
You'll see which method fails and get detailed timing information to help diagnose the issue.

### Common Timeout Causes:
1. **Firewall blocking outbound HTTPS** - Check with your hosting provider
2. **DNS issues** - Server cannot resolve easypay.easypaisa.com.pk
3. **Network routing issues** - Connection to Pakistan IPs might be blocked
4. **EasyPaisa server issues** - Their API might be down or slow
5. **SSL/TLS issues** - Certificate validation problems

## Next Steps Based on Results

1. **If DNS fails**: Configure proper DNS servers on your hosting
2. **If socket connection fails**: Contact hosting provider about firewall rules
3. **If all local tests pass but still timeout**: Issue is with EasyPaisa API or network route
4. **If you get authentication errors**: Verify API credentials
5. **If you get validation errors**: Check request data format

## Support

Check the logs in `storage/logs/` for detailed error information. All test requests are logged with a unique request ID for easy tracking.

