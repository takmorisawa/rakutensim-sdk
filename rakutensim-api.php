<?php

const BASE_URL = 'https://moricreate.com/api/';

function request($url, $method = 'GET', $query = NULL) {
    $username="tak";
    $password="DrXVXbmj2uyqmhno";
    $args = array(
        'method' => $method,
        'blocking' => true,
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
        ),
        'body' => $query === NULL ? "" : json_encode($query)
    );

    $result =  wp_remote_post($url, $args);

    if ( ! is_wp_error( $result ) ) {
        return json_decode( wp_remote_retrieve_body($result), true);
    } else {
        return $result;
    }
}


/**
 * シリーズ一覧を取得する
 * @return {Object} シリーズオブジェクトの配列
 */
function getSerieses() {
  return request(BASE_URL."serieses/");
}

/**
 * 指定シリーズの機種一覧を取得する
 * @param {number} seriesId シリーズID
 * @return {object} 機種オブジェクトの配列
 */
function getDevices($seriesId) {
  return request(BASE_URL."serieses/".$seriesId . "/get_related_devices/");
}

/**
 * 使用可否情報を投稿する
 * @param {string} userId ユーザーID
 * @param {boolean} usable 使用可否
 * @param {string} commnet コメント
 * @param {number} deviceId デバイスID
 */
function postReport($userId, $usable, $comment, $deviceId) {
  $data = array(
    "user_id" => $userId,
    "usable" => $usable,
    "comment" => $comment,
    "device" => $deviceId,
    "product" => 1);
  return request(BASE_URL."rakutensim/reports/", "POST", $data);
}

/**
 * 指定シリーズの使用可否集計値を取得する
 * @param {number} seriesId シリーズID
 * @return {Object} 機種別の使用可否集計結果の配列
 */
function countReports($seriesId) {
  return request(BASE_URL."serieses/".$seriesId . "/count_related_reports/");
}

/**
 * 指定機種のコメント一覧を取得する
 * @param {number} deviceId 機種のID
 * @return {Object} 使用可否別のコメント情報の配列
 */
function getReports($deviceId) {
  $items = request(BASE_URL."rakutensim/reports/?authorized=True&device=".$deviceId);
  $yesItems = array_filter($items, function($item) { return $item["usable"]; });
  $noItems = array_filter($items, function($item) { return !$item["usable"]; });
  return array(
    "yes" => $yesItems,
    "no" => $noItems);
}
