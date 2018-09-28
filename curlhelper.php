<?php

class CurlHelper {

    /**
     * @param $url
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public static function post($url, $params) {

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOPROGRESS, true);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        if (false === $response) {
            throw new \Exception($url . ' ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (null === $response && $httpCode != 200) {
            throw new \Exception($httpCode);
        }

        curl_close($ch);

        return [
            'status' => $httpCode,
            'data' => $response,
        ];

    }

    /**
     * @param string $url
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public static function get($url, $params) {

        $query = http_build_query($params);
        $url = $url . '?' . $query;

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOPROGRESS, true);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);

        $response = curl_exec($ch);
        if (false === $response) {
            throw new \Exception($url . ' ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (null === $response && $httpCode != 200) {
            throw new \Exception($httpCode);
        }

        curl_close($ch);

        return [
            'status' => $httpCode,
            'data' => $response,
        ];

    }

}