<?php

class BikmoHttp {

    const URL = 'http://api.projekt313.com/search';

    public function request(array $data) {

        $ch = curl_init(self::URL);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);

        curl_close($ch);

        if (!$response) {
            return false;
        }

        $response = json_decode($response, true);

        if (empty($response['data']['search']['products'])) {
            return false;
        }

        return $response['data']['search']['products'];
    }

}
