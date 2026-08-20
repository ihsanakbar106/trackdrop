<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookController extends Controller
{

//    GDPR request functions
    public function verifyWebhook(Request $request)
    {
        $hmac_header = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();

        $verified = $this->verifyWebhookData($data, $hmac_header);

        if ($verified) {
            // Process webhook payload
            // ...

            return response()->json(['status' => 'success'], 200);
        } else {
            return response()->json(['status' => 'unauthorized'], 401);
        }
    }

    private function verifyWebhookData($data, $hmac_header)
    {
        // The Shopify app's client secret. In a production environment, set the client
        // secret as an environment variable to prevent exposing it in code.
        $CLIENT_SECRET = env("SHOPIFY_API_SECRET");

        $calculated_hmac = base64_encode(hash_hmac('sha256', $data, $CLIENT_SECRET, true));
        return hash_equals($calculated_hmac, $hmac_header);
    }

}
