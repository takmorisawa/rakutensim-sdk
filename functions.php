<?php

require __DIR__ . "/rakutensim-api.php";
const TABLE_TEMPLATE = "<table id=%s style='display:%s;'><thead>%s</thead><tbody>%s<tbody></table>";


function get_series_buttons($atts, $content) {

  $ret = '';

  $form = <<<EOM
<form class="" method="post" action="%s">
	<input type="hidden" name="series_id" value="%d">
	<input type="submit" value="%s">
</form>
EOM;

  $items = getSerieses();
	foreach ($items as $item) {
    $ret .= sprintf($form, $content, $item["id"], $item["name"]);
	}
	unset($item);

  return $ret;
}
add_shortcode('get_series_buttons', 'get_series_buttons');


function get_device_buttons($atts, $content) {

	$seriesId = 1;
	if ($_SERVER['REQUEST_METHOD'] === 'POST'){
		$seriesId = $_POST["series_id"];
	}

  $ret = '';

  $form = <<<EOM
<form class="" method="post" action="%s">
  <input type="hidden" name="device_id" value="%d">
	<input type="hidden" name="device_name" value="%s">
	<input type="submit" value="%s">
</form>
EOM;

  $items = getDevices($seriesId);
  foreach ($items as $item) {
    $ret .= sprintf($form, $content, $item["id"], $item["name"], $item["name"]);
  }
  unset($item);

  return $ret;
}
add_shortcode('get_device_buttons', 'get_device_buttons');


function get_count_by_device_table() {

	$seriesId = 1;
	$deviceId = 1;

	if ($_SERVER['REQUEST_METHOD'] === 'POST'){
		$seriesId = $_POST["series_id"];
    if (isset($_POST["device_id"])){
      $deviceId = $_POST["device_id"];
    }
    else {
      $devices=getDevices($seriesId);
      $deviceId=$devices[0]["id"];
    }
	}

  $form = <<<EOM
<form class="" method="post">
  <input type="hidden" name="series_id" value="%d">
  <input type="hidden" name="device_id" value="%d">
	<input type="submit" value="%s" style="background-color:%s;">
</form>
EOM;

	$trs = '';
  $items = countReports($seriesId);
	foreach ($items as $item) {
    $button=sprintf($form, $seriesId, $item["device_id"], $item["device_name"]
		,	$item["device_id"] == $deviceId ? "": "#cccccc");
		$trs .= sprintf("<tr><td>%s</td><td>%s</td><td>%s</td></tr>"
		, $button, $item["yes"], $item["no"]);
	}
	unset($item);

	$ths = "<tr><th>device_name</th><th>yes</th><th>no</th></tr>";
	$table = sprintf(TABLE_TEMPLATE, "", "block", $ths, $trs);

  return $table;
}
add_shortcode('get_count_by_device_table', 'get_count_by_device_table');


function get_report_table() {

	$seriesId = 1;
	$deviceId = 1;

	if ($_SERVER['REQUEST_METHOD'] === 'POST'){
		$seriesId = $_POST["series_id"];
    if (isset($_POST["device_id"])){
      $deviceId = $_POST["device_id"];
    }
    else {
      $devices=getDevices($seriesId);
      $deviceId=$devices[0]["id"];
    }
	}

	$create_table = function($id, $display, $arg) {
		$trs = '';
		foreach ($arg  as $item) {
			$trs .= sprintf("<tr><td>%s</td><td>%s</td><td>%s</td></tr>"
			, $item["user_id"], $item["date"], $item["comment"]);
		}
		unset($item);

		$ths = "<tr><th>user_id</th><th>date</th><th>comment</th></tr>";
		$table = sprintf(TABLE_TEMPLATE, $id, $display, $ths, $trs);

	  return $table;
	};

  $script = <<<EOM
<script type="text/javascript">
  function onClickSwitchDisplay(isYes){
    const tblYes = document.getElementById("yes_table");
    const tblNo = document.getElementById("no_table");
    const btnYes = document.getElementById("yes_button");
    const btnNo = document.getElementById("no_button");
    tblYes.style.display= isYes? "block" : "none";
    tblNo.style.display= isYes? "none" : "block";
    btnYes.style.backgroundColor= isYes ? "" : "#cccccc";
    btnNo.style.backgroundColor= isYes ? "#cccccc" : "";
  }
</script>
<form>
  <input type="button" id="yes_button" value="yes" onclick="onClickSwitchDisplay(true)">
  <input type="button" id="no_button" value="no" onclick="onClickSwitchDisplay(false)" style="background-color:#cccccc">
</form>
EOM;

  $items = getReports($deviceId);
	$yes_table = $create_table("yes_table", "block", $items["yes"]);
	$no_table = $create_table("no_table", "none", $items["no"]);

	$ret = $script . $yes_table	. $no_table;

  return $ret;
}
add_shortcode('get_report_table', 'get_report_table');


function get_post_form($atts, $content) {

	// $atts = shortcode_atts(array(
	// 'series' => 1
	// ), $atts);
	// $seriesId = $atts["series"]

	$deviceId = 1;
	$deviceName = "";
	if ($_SERVER['REQUEST_METHOD'] === 'POST'){
		$deviceId = $_POST["device_id"];
		$deviceName = $_POST["device_name"];
	}

  $userId = get_current_user_id();

	$ret = <<<EOM
<p>Can you use Rakuten-SIM with %s?</p>
<form class="" action="%s" method="post">
	<input type="radio" name="usable" value="yes" checked="checked">YES
  <input type="radio" name="usable" value="no" checked>NO
	<textarea name="comment" cols="10" rows="10" placeholder="コメント欄（自由投稿）"></textarea>
  <br>
  <input type="submit" name="post_report" value="送信"/>

  <input type="hidden" name="user_id" value=%s>
	<input type="hidden" name="device_id" value="%d">
</form>
EOM;
	$ret = sprintf($ret, $deviceName, $content, $userId, $deviceId);

	return $ret;
}
add_shortcode('get_post_form', 'get_post_form');


if ($_SERVER['REQUEST_METHOD'] === 'POST'
  && isset($_POST["post_report"])){
  $userId = $_POST['user_id'];
  $usable = $_POST['usable'] === "yes";
  $comment = $_POST['comment'];
  $deviceId = $_POST["device_id"];

  $result = postReport($userId, $usable, $comment, $deviceId);
  $url = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/top/';

  wp_redirect($url);
  exit();
}