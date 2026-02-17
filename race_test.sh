#!/bin/sh

# Configuration
APP_URL="http://nginx"
MASTER_EMAIL="master@example.com"
MASTER_PASSWORD="password"
COOKIE_FILE="/tmp/cookies.txt"
OUTPUT_FILE_1="/tmp/status_1.txt"
OUTPUT_FILE_2="/tmp/status_2.txt"

echo "Starting race condition test for 'Take In Work' action..."

# 1. Create a new request and assign it to the master
echo "Creating a new test request and assigning it to ${MASTER_EMAIL}..."
REQUEST_ID=$(php artisan tinker --execute="
    \$master = App\Models\User::where('email', '${MASTER_EMAIL}')->first();
    \$request = App\Models\Request::create([
        'clientName' => 'Race Test Client',
        'phone' => '111-111-1111',
        'address' => 'Race Test Address',
        'problemText' => 'Problem for race test',
        'status' => 'new',
    ]);
    \$request->update(['assigned_to' => \$master->id, 'status' => 'assigned']);
    echo \$request->id;
")

if [ -z "$REQUEST_ID" ]; then
    echo "Failed to create or assign test request. Exiting."
    exit 1
fi

echo "Created and assigned request with ID: $REQUEST_ID"

# 2. Get CSRF token and session cookie
echo "Fetching CSRF token and session cookie..."
LOGIN_PAGE_HTML=$(curl -s -c "$COOKIE_FILE" "$APP_URL/login")
CSRF_TOKEN=$(echo "$LOGIN_PAGE_HTML" | grep -o 'name="_token" value="[^"]*' | cut -d '"' -f 4)

if [ -z "$CSRF_TOKEN" ]; then
    echo "Failed to retrieve CSRF token. Exiting."
    exit 1
fi

# 3. Log in the master user
echo "Logging in as ${MASTER_EMAIL}..."
curl -s -b "$COOKIE_FILE" -c "$COOKIE_FILE" -L -X POST \
     -H "Referer: $APP_URL/login" \
     -d "_token=$CSRF_TOKEN" \
     -d "email=$MASTER_EMAIL" \
     -d "password=$MASTER_PASSWORD" \
     "$APP_URL/login" > /dev/null

# Get a new CSRF token from a protected page after login
MASTER_DASHBOARD_HTML=$(curl -s -b "$COOKIE_FILE" "$APP_URL/master")
CSRF_TOKEN_AFTER_LOGIN=$(echo "$MASTER_DASHBOARD_HTML" | grep -o 'name="_token" value="[^"]*' | cut -d '"' -f 4 | head -n 1)

if [ -z "$CSRF_TOKEN_AFTER_LOGIN" ]; then
    echo "Failed to retrieve CSRF token after login. Exiting."
    exit 1
fi

echo "Simulating concurrent 'Take In Work' requests for Request ID: $REQUEST_ID"

# 4. Simulate concurrent "Take In Work" requests
# Each curl command writes the HTTP status code to its output file
curl -s -o /dev/null -w "%{http_code}" -b "$COOKIE_FILE" -c "$COOKIE_FILE" -X POST \
     -H "Referer: $APP_URL/master" \
     -d "_token=$CSRF_TOKEN_AFTER_LOGIN" \
     "$APP_URL/master/requests/${REQUEST_ID}/take-in-work" > "$OUTPUT_FILE_1" &
PID1=$!

curl -s -o /dev/null -w "%{http_code}" -b "$COOKIE_FILE" -c "$COOKIE_FILE" -X POST \
     -H "Referer: $APP_URL/master" \
     -d "_token=$CSRF_TOKEN_AFTER_LOGIN" \
     "$APP_URL/master/requests/${REQUEST_ID}/take-in-work" > "$OUTPUT_FILE_2" &
PID2=$!

# Wait for both requests to complete
wait $PID1
wait $PID2

echo "Analyzing results from HTTP codes and database..."

# 5. Analyze the results
STATUS_CODE_1=$(cat "$OUTPUT_FILE_1")
STATUS_CODE_2=$(cat "$OUTPUT_FILE_2")
FINAL_DB_STATUS=$(php artisan tinker --execute="echo App\Models\Request::find($REQUEST_ID)->status;")

echo ""
echo "--- Test Summary ---"
echo "Request 1 HTTP Status: $STATUS_CODE_1"
echo "Request 2 HTTP Status: $STATUS_CODE_2"
echo "Final DB Status: $FINAL_DB_STATUS"

if [ "$STATUS_CODE_1" -eq 302 ] && [ "$STATUS_CODE_2" -eq 302 ] && [ "$FINAL_DB_STATUS" = "in_progress" ]; then
    echo "Race test PASSED: Both requests were handled (302) and the final DB state is correct (in_progress)."
    exit 0
else
    echo "Race test FAILED: Unexpected outcome."
    exit 1
fi
