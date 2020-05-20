<h1>비밀번호 변경</h1>
<?php
include "../incl/lib/connection.php";
include_once "../config/security.php";
require "../incl/lib/generatePass.php";
require_once "../incl/lib/exploitPatch.php";
include_once "../incl/lib/defuse-crypto.phar";
use Defuse\Crypto\KeyProtectedByPassword;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
$ep = new exploitPatch();
if (!empty($_POST["userName"])) {
	$userName = $ep->remove($_POST["userName"]);
	$oldpass = $_POST["oldpassword"];
	$newpass = $_POST["newpassword"];
} else {
	$userName = "";
	$oldpass = "";
	$newpass = "";
}
$salt = "";
if($userName != "" AND $newpass != "" AND $oldpass != ""){
$generatePass = new generatePass();
$pass = $generatePass->isValidUsrname($userName, $oldpass);
if ($pass == 1) {
	if($cloudSaveEncryption == 1){
		$query = $db->prepare("SELECT accountID FROM accounts WHERE userName=:userName");	
		$query->execute([':userName' => $userName]);
		$accountID = $query->fetchColumn();
		$saveData = file_get_contents("../data/accounts/$accountID");
		if(file_exists("../data/accounts/keys/$accountID")){
			$protected_key_encoded = file_get_contents("../data/accounts/keys/$accountID");
			$protected_key = KeyProtectedByPassword::loadFromAsciiSafeString($protected_key_encoded);
			$user_key = $protected_key->unlockKey($oldpass);
			try {
				$saveData = Crypto::decrypt($saveData, $user_key);
			} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
				exit("-2");	
			}
			$protected_key = KeyProtectedByPassword::createRandomPasswordProtectedKey($newpass);
			$protected_key_encoded = $protected_key->saveToAsciiSafeString();
			$user_key = $protected_key->unlockKey($newpass);
			$saveData = Crypto::encrypt($saveData, $user_key);
			file_put_contents("../data/accounts/$accountID",$saveData);
			file_put_contents("../data/accounts/keys/$accountID",$protected_key_encoded);
		}
	}
	//creating pass hash
	$passhash = password_hash($newpass, PASSWORD_DEFAULT);
	$query = $db->prepare("UPDATE accounts SET password=:password, salt=:salt WHERE userName=:userName");	
	$query->execute([':password' => $passhash, ':userName' => $userName, ':salt' => $salt]);
	echo "비밀번호가 변경되었습니다. 인게임에서 Refreash Login을 해주세요<br><a href='./'>계정 관리 페이지로 돌아가기</a>";
}else{
	echo "알 수 없는 계정이거나, 비밀번호가 잘못되었습니다. 다시 시도해주세요.<br><a href='./'>계정 관리 페이지로 돌아가기</a>";

}
}else{
	echo '<form action="changePassword.php" method="post">닉네임: <input type="text" name="userName"><br>현재 비밀번호: <input type="password" name="oldpassword"><br>새로운 비밀번호: <input type="password" name="newpassword"><br><input type="submit" value="변경하기"></form>';
}
?>