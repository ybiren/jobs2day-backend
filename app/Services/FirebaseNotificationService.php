<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    public function sendNotification($data)
    {
        $dynamicData = [
            "fcm_tokens" => [$data['fcm_token']],
            "title" => $data['title'],
            "body" => $data['body'],
            'data' => $data['data']
        ];
        $constantData = [
            "firebaseCredentialsJson" => [
                "type"=> "service_account",
                "project_id"=> "job2day-84b26",
                "private_key_id"=> "18ad5eb920353965d09b2156702789ccbc3f8eac",
                "private_key"=> "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCba8WFP3fOGLOG\nJnbiGLXBFGngpl+ePcCI6QsSJj4BBQFzGVrH+poumInYRVVOirAf3CNxJmxjpgvo\nli0d+E/uKhz8ienG3IgRcNgidtGpXqG3o+Vcn3JmojboCvb20R/ffRLad0Z/DtwC\nZGtZkioYmusATUGLqt7cV+s+VxCE6ZH+0g9C9vsr+W0gzmm/erjlIDy4xf+eX1QP\nlh/LoesbhF8TseIoRwVG9BGbGbBQ4Mt4kal/t8sttrZK8iYgDK7RJTZABPIWEYC7\npWbenax1yyeqFiI73dznHIwLElgOyvmyLpZqSMEM/2J7lPdTRjldRSsyAvbpFBH3\nDyVDFGVtAgMBAAECggEAJ9RPkx03u6ZS428aCkkg0vFDTOH2IRxcKfnm72jDfQlm\nXLPDsvoC5ywKL1VdTjp2dkO5BXc+Ua8s4BcUStjmyvcZ5JIHSgIcELQlf3eJtWjd\n8qzay4jFDY5GmeKtdYUUMPbhjQECHO+W7DoF1LeDeDjDPpmN0ZghcsBJNfmj7xtx\n9M+2DEmn0gPFgb9gZGQYVx63j7hQi3UK553Edctjylw2VR4dCOPV3J3HbhNET8lM\nI6HXUOU9OzcaKrS57dch3qsJlf5koATrEhbkKgTUP7dSYREZhRJPjSLQduT3bmEK\nBu+xEMbJzDuJjU1qJWZ4JDGgcK8UebECt3e2+91JlQKBgQDT+Ak8qKHL6llP7hBT\nEaqP5w/MrOKxEJvXi58/51z+cggDzA9+LN/KVT0Hi7UZQ68sceIRpH9x93XeC/lL\nctn6hxTOYCI3NajD3RpCFJGVXV2SlYdPm5IDIO/SL3Xm7Ytm6lPGkRdPuGOoZrmx\nu57f7/K47w3Tb3b6ndvcU+R35wKBgQC7tKmIHLUevQVRMrqkQY1yfTelhrMe8HNh\nftkLKC1tOONukmxSlxQGRiY/s3/t7wjdEJA9aRr1cSVPiamg6eDHjx1XxyHHgacU\nKes+oHsNcEM8ovpOCwsDp3CjVWx0AWY49MtXMiLYxEVN7/wwaHAaDaXIi5x8KQDR\nSTgvpzj9iwKBgQCE8OhiKeUn7jfLoKSOZOMTU7ieBsQ6hw8mtYPQYXv6fWw3bXE+\nEkjdLm6TX/TZ/pBMELTXmdLwmGJNPDdDMaoyrSvIb5SmCbpp9S71yM6x3hfEypFN\nxWVjHvIqYefRSKSIjGi0feUf86ZVRPAr3186VdRmVk/Wju8RKZZt7hO5ewKBgDQ7\noNWEj44fMWSYBkIBD9hlKeAA7MVWSfU+dC1bn9B9/SPe0Q/mhKb+TFR7ocTXOJMz\nsb+1CNB2DNjmSPLDQk6l75dmjWrUFGdTBuhlhfiGXd94xsiC4CzDImABPG3HmeeN\njywTgojpjROUDYx284L8ez6kQwZ17olOo7j3Aq/jAoGBANL9fkMmbOEgwcvIBEsS\n9m7zOnI0J15BOTborxWlCtN3Ty4igpYJQbbfs8UFL/k1all+pRMnRjsW0u6Z1gyj\nuuCZ+/ep5KN6qTQxOy/9GWBhy6dor5a4w6TWDkgD/li82vk5WDlO0n6vgeRqSQ7i\ndJDb3f2x9UbMCkbSVOr73LvN\n-----END PRIVATE KEY-----\n",
                "client_email"=> "firebase-adminsdk-fbsvc@job2day-84b26.iam.gserviceaccount.com",
                "client_id"=> "102730825849319896626",
                "auth_uri"=> "https://accounts.google.com/o/oauth2/auth",
                "token_uri"=> "https://oauth2.googleapis.com/token",
                "auth_provider_x509_cert_url"=> "https://www.googleapis.com/oauth2/v1/certs",
                "client_x509_cert_url"=> "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-fbsvc%40job2day-84b26.iam.gserviceaccount.com",
                "universe_domain"=> "googleapis.com"
            ]
        ];

        // Merge dynamic data with constant data
        $alldata = array_merge($dynamicData, $constantData);

        $response = Http::withHeaders([
            'Authorization' => 'key=U2FuZ2EncyBSZXN0IEFQSVMgUGxhdGVmb3Jt',

            'Content-Type' => 'application/json',
        ])->post('https://restapi.sangahub.com/api/fcm/send', $alldata);

// Log the full Firebase response
        Log::info('Firebase Full Response:', ['response' => $response->json()]);

// Log failedTokens if available
        if (isset($response['data']['failedTokens'])) {
            Log::error('Firebase Failed Tokens:', ['failedTokens' => $response['data']['failedTokens']]);
        }

        return $response->json();

    }

}
