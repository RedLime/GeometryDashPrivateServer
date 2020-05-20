<h1>닉네임 변경</h1>
<?php
include "../incl/lib/connection.php";
require "../incl/lib/generatePass.php";
require_once "../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
//here im getting all the data
if (!empty($_POST["userName"])) {
	$userName = $ep->remove($_POST["userName"]);
	$newusr = $ep->remove($_POST["newusr"]);
	$password = $ep->remove($_POST["password"]);
} else {
	$userName = "";
	$newusr = "";
	$password = "";
}
if($userName != "" AND $newusr != "" AND $password != ""){
	$generatePass = new generatePass();
	$pass = $generatePass->isValidUsrname($userName, $password);
	if ($pass == 1) {
		$query = $db->prepare("UPDATE accounts SET username=:newusr WHERE userName=:userName");	
		$query->execute([':newusr' => $newusr, ':userName' => $userName]);
		if($query->rowCount()==0){
			echo "알 수 없는 계정이거나, 비밀번호가 잘못되었습니다. 다시 시도해주세요.<br><a href='./'>계정 관리 페이지로 돌아가기</a>";
		}else{
			echo "닉네임이 변경되었습니다. 인게임에서 Refreash Login을 해주세요<br><a href='./'>계정 관리 페이지로 돌아가기</a>";
		}
	}else{
		echo "알 수 없는 계정이거나, 비밀번호가 잘못되었습니다. 다시 시도해주세요.<br><a href='./'>계정 관리 페이지로 돌아가기</a>";
	}
}else{
	echo '<form action="changeUsername.php" method="post">현재 닉네임: <input type="text" name="userName"><br>변경할 닉네임: <input type="text" name="newusr"><br>계정 비밀번호: <input type="password" name="password"><br><input type="submit" value="변경"></form>';
}
?>