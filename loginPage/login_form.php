<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8" />
    <title>로그인 폼</title>
    <link rel="stylesheet" href="style.css" />
    <script>
        function chk_logform() {
            if (login_form.fuserid.value == "") {
                alert('[ 아이디 ]를 입력하세요.');
                login_form.fuserid.focus();
                return false;
            } else if (login_form.fpasswd.value == "") {
                alert('[ 비밀번호 ]를 입력하세요.');
                login_form.fpasswd.focus();
                return false;
            } else {
                return true;
            }
        }
    </script>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>로그인</h1>
            <form name="login_form" action="login.php" method="post" onsubmit="return chk_logform();">
                <input type="text" name="fuserid" placeholder="아이디">
                <input type="password" name="fpasswd" placeholder="비밀번호">
                <button type="submit">로그인</button>
            </form>
            <p style="text-align:center;">
                [ <a href="add_form.php">회원가입</a> ]
            </p>
        </div>
    </div>

<?php
if (isset($_GET['error']) && $_GET['error'] == '1') {
    echo "<script>
        alert('[로그인 실패]\\n아이디 또는 비밀번호가 틀립니다.');
        if (history.replaceState) {
            const url = new URL(window.location);
            url.searchParams.delete('error');
            history.replaceState(null, '', url.toString()); // 문자열로 변환 필수
        }
    </script>";
}
?>
</body>
</html>
