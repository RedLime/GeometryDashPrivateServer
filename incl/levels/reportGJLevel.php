<?php
chdir(dirname(__FILE__));
//error_reporting(0);
include "../lib/connection.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
if($_POST["levelID"]){
	$levelID =  $ep->remove($_POST["levelID"]);
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	$query = "SELECT count(*) FROM reports WHERE levelID = :levelID AND hostname = :hostname";
	$query = $db->prepare($query);
	$query->execute([':levelID' => $levelID, ':hostname' => $ip]);

	if($query->fetchColumn() == 0){
		$query = $db->prepare("INSERT INTO reports (levelID, hostname) VALUES (:levelID, :hostname)");	
		$query->execute([':levelID' => $levelID, ':hostname' => $ip]);
		
		//REPORT SYSTEM
		$webhookurl = "https://discordapp.com/api/webhooks/";
		//FIX THIS LINE TO LINK
		
		$make_json = json_encode(array(
			"content" => "ahah... that's too bad. reported a new level.",
			"embeds" => [
				[
					"title" => "**Report Hooker**",
					"description" => "Warning! Please check this level!",
					"color" => hexdec( "FF0000" ),
					"footer" => [
						"text" => date("Y-m-d H:i:s"),
					],
					"fields" => [
						[
							"name" => "**Level ID**",
							"value" => "**".$levelID."**",
							"inline" => false
						],
						[
							"name" => "Report IP",
							"value" => "||$ip||",
							"inline" => false
						]
					]
				]
			]
		));

		$ch = curl_init($webhookurl);
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $make_json);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec( $ch );
		echo $db->lastInsertId();
	}else{
		$webhookurl = "https://discordapp.com/api/webhooks/";

		$make_json = json_encode(array(
			"content" => "ahah... that's too bad. reported a new level.",
			"embeds" => [
				[
					"title" => "**Report Hooker**",
					"description" => "Warning! Please check this level!",
					"color" => hexdec( "FF0000" ),
					"footer" => [
						"text" => date("Y-m-d H:i:s"),
					],
					"fields" => [
						[
							"name" => "**Level ID**",
							"value" => "**".$levelID."**",
							"inline" => false
						],
						[
							"name" => "Report IP",
							"value" => "||$ip||",
							"inline" => false
						]
					]
				]
			]
		));

		$ch = curl_init($webhookurl);
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $make_json);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec( $ch );
		echo -1;
	}	
}
?>