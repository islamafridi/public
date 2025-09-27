<?php
class Scraper {
    public function scrape($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $ip = rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["REMOTE_ADDR: $ip", "HTTP_X_FORWARDED_FOR: $ip"]);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
        curl_setopt($curl, CURLOPT_REFERER, "http://www.google.com");
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    public function match_all_key_value($regex, $str, $keyIndex = 1, $valueIndex = 2) {
        $arr = array();
        preg_match_all($regex, $str, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $arr[$m[$keyIndex]] = $m[$valueIndex];
        }
        return $arr;
    }

    public function match_all($regex, $str, $index = 0) {
        if (preg_match_all($regex, $str, $matches) === false) {
            return false;
        } else {
            return $matches[$index];
        }
    }

    public function match($regex, $str, $index = 0) {
        if (preg_match($regex, $str, $match) == 1) {
            return $match[$index];
        } else {
            return false;
        }
    }
}