<?php
//error_reporting(0);
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/GJPCheck.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
$gjp = $ep->remove($_POST["gjp"]);
$stars = $ep->remove($_POST["stars"]);
$feature = $ep->remove($_POST["feature"]);
$levelID = $ep->remove($_POST["levelID"]);
$accountID = $ep->remove($_POST["accountID"]);
if($accountID != "" AND $gjp != ""){
	$GJPCheck = new GJPCheck();
	$gjpresult = $GJPCheck->check($gjp,$accountID);
	if($gjpresult == 1){
		$difficulty = $gs->getDiffFromStars($stars);
		if($gs->checkPermission($accountID, "commandRate")){
			$gs->rateLevel($accountID, $levelID, $stars, $difficulty["diff"], $difficulty["auto"], $difficulty["demon"]);
			$gs->featureLevel($accountID, $levelID, $feature);
			$gs->verifyCoinsLevel($accountID, $levelID, 1);
			
			//rate notification
			//
		    $post_field_string = http_build_query(array('levelID' => $levelID), '', '&');
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, "./downloadGJLevel.php");
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_field_string);
		    curl_setopt($ch, CURLOPT_POST, true);
		    $response = curl_exec($ch);
		    curl_close ($ch);

		    $levelData = explode(":", $response);

			$rate = $gs->getDifficulty($difficulty["diff"], $difficulty["auto"], $difficulty["demon"]);
			$levelTitle = $levelData[3];
			$levelUploader = $gs->getUserName($levelData[11]);
			
			//DISCORD WEBHOOK NOTIFICATION
			$webhookurl = "https://discordapp.com/api/webhooks/";
			//FIX THIS LINE to LINK

			$make_json = json_encode(array(
				"content" => "hey, new rated level!",
			    "embeds" => [
			        [
			            "title" => "**Notification**",
			            "description" => "ah, new awarded level! checkout this level!",
			            "color" => hexdec( "FFFFFF" ),
			            "footer" => [
			                "text" => date("Y-m-d H:i:s"),
			            ],
			            "fields" => [
			                [
			                    "name" => "**Level Info**",
			                    "value" => "**".$levelTitle."** by ".$levelUploader,
			                    "inline" => false
			                ],
			                [
			                    "name" => "Difficulty",
			                    "value" => "★".$stars." (".$rate.")",
			                    "inline" => true
			                ],
			                [
			                    "name" => "Featured",
			                    "value" => $feature == 1 ? "Featured!" : "No-Featured :(",
			                    "inline" => true
			                ],
			                [
			                    "name" => "ID",
			                    "value" => $levelID,
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
			
			echo 1;
		}else if($gs->checkPermission($accountID, "actionSuggestRating")){
			$gs->suggestLevel($accountID, $levelID, $difficulty["diff"], $stars, $feature, $difficulty["auto"], $difficulty["demon"]);
			echo 1;
		}else{
			echo -1;
		}
	}else{
		echo -1;
	}
}else{
	echo -1;
}
?>
