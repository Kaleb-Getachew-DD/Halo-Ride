<?php

namespace App\Services;

class AfroMessageService
{
    protected $token;
    protected $identifier;
    protected $sender;

    public function __construct()
    {
        $this->token      = config('services.afromessage.token');
        $this->identifier = config('services.afromessage.identifier');
        $this->sender     = config('services.afromessage.sender');
    }

    /**
     * Send a one-time password (OTP)
     *
     * @param string $recipient
     * @param int $length Code length (default: 6)
     * @param int $type 0 = numeric, 1 = alpha, 2 = alphanumeric
     * @param int $ttl Time to live in seconds (0 = never expires)
     * @param string|null $prefix Message prefix
     * @param string|null $postfix Message postfix
     * @param int $spacesBefore Spaces before code
     * @param int $spacesAfter Spaces after code
     * @param string|null $callback Callback URL for status
     * @return array
     * @throws \Exception
     */
    public function sendOTP(
        string $recipient,
        int $length = 6,
        int $type = 0,
        int $ttl = 300,
        ?string $prefix = null,
        ?string $postfix = null,
        int $spacesBefore = 0,
        int $spacesAfter = 0,
        ?string $callback = null
    ): array {
        $query = http_build_query([
            'from'    => $this->identifier,
            'sender'  => $this->sender,
            'to'      => $recipient,
            'len'     => $length,
            't'       => $type,
            'ttl'     => $ttl,
            'pr'      => $prefix ?? '',
            'ps'      => $postfix ?? '',
            'sb'      => $spacesBefore,
            'sa'      => $spacesAfter,
            'callback'=> $callback ?? ''
        ]);

        $url = "https://api.afromessage.com/api/challenge?$query";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('Curl error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'http_code' => $httpCode,
            'result'    => json_decode($result, true)
        ];
    }

    /**
     * Verify a one-time password (OTP)
     *
     * @param string $code The OTP code
     * @param string|null $recipient Optional: the phone number
     * @param string|null $verificationId Optional: the verification ID
     * @return array
     * @throws \Exception
     */
    public function verifyOTP(string $code, ?string $recipient = null, ?string $verificationId = null): array
    {
        $query = http_build_query(array_filter([
            'code' => $code,
            'to'   => $recipient,
            'vc'   => $verificationId
        ]));

        $url = "https://api.afromessage.com/api/verify?$query";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('Curl error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'http_code' => $httpCode,
            'result'    => json_decode($result, true)
        ];
    }
}
