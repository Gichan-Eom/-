<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <title>회원가입</title>
  <style>
    
    /* 전체 화면 배경 */
    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: sans-serif;
      background: #FFFFFF;
    }

    /* 로그인 컨테이너 - 화면 중앙 정렬 */
    .login-container {
      position: relative;
      width: 100%;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    /* 로그인 박스 */
    .login-box {
      width: 400px;
      padding: 40px;
      background-color: #f9f9f9;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      box-sizing: border-box;
    }

    .login-box h2 {
      text-align: center;
      margin-bottom: 24px;
      color: #e53935;
      font-weight: 700;
      font-size: 28px;
    }

    /* 폼 그룹 */
    .form-group {
      margin-bottom: 20px;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }

    .form-group label {
      margin-bottom: 6px;
      font-weight: 600;
      font-size: 14px;
      color: #333;
      user-select: none;
    }

    /* 인풋 스타일 */
    .login-box input[type="text"],
    .login-box input[type="password"] {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 16px;
      box-sizing: border-box;
      transition: border-color 0.3s;
    }
    .login-box input[type="text"]:focus,
    .login-box input[type="password"]:focus {
      border-color: #e53935;
      outline: none;
    }

    /* 닉네임, 아이디 중복확인 영역 */
    .with-btn {
      display: flex;
      gap: 10px;
      width: 100%;
      align-items: center;
    }

    .with-btn input {
      flex: 1;
    }

    .check-btn {
      padding: 12px 16px;
      background-color: #e53935;
      border: none;
      color: white;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      white-space: nowrap;
      transition: background-color 0.3s;
    }
    .check-btn:hover {
      background-color: #d32f2f;
    }

    /* 전화번호 입력 영역 */
    .phone-group {
      display: flex;
      gap: 8px;
      width: 100%;
    }

    .phone-group input {
      flex: 1;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 16px;
      text-align: center;
      transition: border-color 0.3s;
    }
    .phone-group input:focus {
      border-color: #e53935;
      outline: none;
    }

    /* 제출 버튼 */
    .submit-btn {
      margin-top: 10px;
      width: 100%;
      padding: 12px;
      background-color: #e53935;
      border: none;
      color: white;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      transition: background-color 0.3s;
    }
    .submit-btn:hover {
      background-color: #d32f2f;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-box">
      <h2>회원가입</h2>
      <form name="user_form" action="add_db.php" method="post" onsubmit="return chk_input()">
        
        <div class="form-group">
          <label for="nickname">닉네임</label>
          <div class="with-btn">
            <input type="text" id="nickname" name="nickname" />
            <button type="button" class="check-btn" onclick="chk_nickname()">중복확인</button>
          </div>
        </div>

        <div class="form-group">
          <label for="fuserid">아이디</label>
          <div class="with-btn">
            <input type="text" id="fuserid" name="fuserid" />
            <button type="button" class="check-btn" onclick="chk_id()">중복확인</button>
          </div>
        </div>

        <div class="form-group">
          <label for="fpasswd">비밀번호</label>
          <input type="password" id="fpasswd" name="fpasswd" />
        </div>

        <div class="form-group">
          <label for="fpasswd_re">비밀번호 확인</label>
          <input type="password" id="fpasswd_re" name="fpasswd_re" onblur="chk_passwd()" />
        </div>

        <div class="form-group">
          <label>전화번호</label>
          <div class="phone-group">
            <input type="text" name="phone1" maxlength="3" />
            <input type="text" name="phone2" maxlength="4" />
            <input type="text" name="phone3" maxlength="4" />
          </div>
        </div>

        <button type="submit" class="submit-btn">확인</button>
      </form>
    </div>
  </div>

  <script>
    function chk_nickname() {
      const nickname = document.getElementById("nickname").value.trim();
      if (nickname === "") {
        alert("닉네임을 입력하세요.");
        return;
      }
      window.open('nick_chk.php?nickname=' + encodeURIComponent(nickname), 'chk', 'width=350,height=180');
    }

    function chk_id() {
      const id = document.getElementById("fuserid").value.trim();
      if (id === "") {
        alert("아이디를 입력하세요.");
      } else {
        window.open('id_chk.php?fuserid=' + encodeURIComponent(id), 'chk', 'width=300,height=100');
      }
    }

    function chk_passwd() {
      const pw = document.getElementById("fpasswd").value;
      const pw_re = document.getElementById("fpasswd_re").value;
      if (pw !== pw_re) {
        alert("비밀번호가 일치하지 않습니다.");
        document.getElementById("fpasswd").value = "";
        document.getElementById("fpasswd_re").value = "";
        document.getElementById("fpasswd").focus();
        return false;
      }
    }

    function chk_input() {
      if (document.getElementById("nickname").value.trim() === "") {
        alert("닉네임을 입력하세요.");
        document.getElementById("nickname").focus();
        return false;
      }
      if (document.getElementById("fuserid").value.trim() === "") {
        alert("아이디를 입력하세요.");
        document.getElementById("fuserid").focus();
        return false;
      }
      if (document.getElementById("fpasswd").value === "") {
        alert("비밀번호를 입력하세요.");
        document.getElementById("fpasswd").focus();
        return false;
      }
      if (document.getElementById("fpasswd_re").value === "") {
        alert("비밀번호 확인을 입력하세요.");
        document.getElementById("fpasswd_re").focus();
        return false;
      }
      return true;
    }
  </script>
</body>
</html>
