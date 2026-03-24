<?php
// ────────────────────────────────────────────
// 에러 출력 및 UTF-8 설정
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/html; charset=UTF-8');
putenv('NLS_LANG=KOREAN_KOREA.AL32UTF8');

session_start();
if (!isset($_SESSION['ses_userid'])) {
    die('로그인이 필요합니다.');
}
$user_id = $_SESSION['ses_userid'];

// ────────────────────────────────────────────
// Oracle DB 접속
$conn = oci_connect(
    "dbuser202046",
    "ce1234",
    "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=earth.gwangju.ac.kr)(PORT=1521))"
    . "(CONNECT_DATA=(SID=orcl)))",
    "AL32UTF8"
);
if (!$conn) {
    $e = oci_error();
    die('DB 연결 실패: ' . htmlspecialchars($e['message'], ENT_QUOTES, 'UTF-8'));
}

// ────────────────────────────────────────────
// 삭제 처리 (GET 방식)
if (isset($_GET['delete_prod_id'])) {
    $gid = (int) $_GET['delete_prod_id'];

    foreach ([
        "DELETE FROM FAVORITE WHERE good_id = :gid",
        "DELETE FROM GOOD WHERE good_id = :gid"
    ] as $sql) {
        $st = oci_parse($conn, $sql);
        oci_bind_by_name($st, ":gid", $gid);
        $result = oci_execute($st, OCI_NO_AUTO_COMMIT);
        if (!$result) {
            $err = oci_error($st);
            die("삭제 실패: " . htmlspecialchars($err['message']));
        }
        oci_free_statement($st);
    }
    oci_commit($conn);
    header('Location: ProfilePage.php');
    exit;
}

if (isset($_GET['delete_fav_id'])) {
    $fid = (int) $_GET['delete_fav_id'];

    $st = oci_parse(
        $conn,
        "DELETE FROM FAVORITE WHERE favorite_id = :fid AND user_id = :user_id"
    );
    oci_bind_by_name($st, ":fid", $fid);
    oci_bind_by_name($st, ":user_id", $user_id);

    $result = oci_execute($st, OCI_COMMIT_ON_SUCCESS);
    if (!$result) {
        $err = oci_error($st);
        die("삭제 실패: " . htmlspecialchars($err['message']));
    }
    oci_free_statement($st);

    header('Location: ProfilePage.php');
    exit;
}

// 닉네임 조회
$stmt = oci_parse(
    $conn,
    "SELECT nickname FROM USERS WHERE user_id = :user_id"
);
oci_bind_by_name($stmt, ":user_id", $user_id);
oci_execute($stmt);
$row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS);
$nickname = $row['NICKNAME'] ?? $user_id;
oci_free_statement($stmt);

// 즐겨찾기 목록 조회
$stmt = oci_parse(
    $conn,
    "SELECT f.favorite_id, g.good_id, g.good_name, g.good_price, g.good_image, c.category_name
     FROM FAVORITE f
     JOIN GOOD g ON f.good_id = g.good_id
     LEFT JOIN CATEGORY c ON c.category_id = g.category_id
     WHERE f.user_id = :user_id
     ORDER BY f.favorite_registration_date DESC"
);
oci_bind_by_name($stmt, ":user_id", $user_id);
oci_execute($stmt);
$favorites = [];
while ($r = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
    $favorites[] = $r;
}
oci_free_statement($stmt);

// ────────────────────────────────────────────
// 내 상품 목록 조회
$stmt = oci_parse(
    $conn,
    "SELECT g.good_id, g.good_name, g.good_price, g.good_image, c.category_name
     FROM GOOD g
     LEFT JOIN CATEGORY c ON c.category_id = g.category_id
     WHERE g.user_id = :user_id
     ORDER BY g.good_registration_date DESC"
);
oci_bind_by_name($stmt, ":user_id", $user_id);
oci_execute($stmt);
$products = [];
while ($r = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
    $products[] = $r;
}
oci_free_statement($stmt);

// 연결 종료
oci_close($conn);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>My Profile</title>
  <style>
    body { background:#fff; margin:0; font-family:'Pretendard','Malgun Gothic',sans-serif; }
    .my-header { height:50px; display:flex; justify-content:flex-end; align-items:center; padding:0 24px; background:#f5f5f5; }
    .my-btn { margin-left:8px; background:#111; color:#fff; border:none; border-radius:4px; padding:4px 12px; cursor:pointer; }
    .my-container { max-width:960px; margin:24px auto; padding:0 16px; }
    .my-profile-row { display:flex; align-items:center; margin-bottom:32px; }
    .my-profile-imgbox { width:120px; height:120px; border:1.5px solid #e0a0a0; border-radius:10px; background:#f8c8c8; display:flex; align-items:center; justify-content:center; margin-right:24px; }
    .my-profile-img { width:100px; height:100px; object-fit:cover; border-radius:8px; }
    .my-user-id { font-size:18px; }
    .my-box { background:#c57c7c; padding:24px; border-radius:8px; color:#fff; }
    .my-section { margin-bottom:24px; }
    .my-section-title { font-size:16px; margin-bottom:12px; }
    #favorite-items, #product-items { display:flex; gap:16px; overflow-x:auto; padding-bottom:8px; }
    #favorite-items { flex-wrap:wrap; max-height:240px; }
    #product-items { flex-wrap:nowrap; max-height:240px; }
    .my-item { position:relative; flex:0 0 auto; width:150px; background:#fff; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1); overflow:hidden; display:flex; flex-direction:column; align-items:center; padding-bottom:12px; transition:transform .2s; }
    .my-item:hover { transform:translateY(-4px); }
    .my-item-img img { width:100%; height:120px; object-fit:cover; }
    .my-item-name { color: black; margin:8px 0 4px; font-size:14px; text-align:center; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; width:90%; }
    .my-item-price { font-size:13px; color:#444; text-align:center; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; width:90%; }
    .delete-btn { position:absolute; top:6px; right:6px; background:rgba(0,0,0,0.6); color:#fff; border:none; border-radius:50%; width:22px; height:22px; font-size:15px; text-decoration:none; line-height:22px; text-align:center; display:none; }
    .my-item:hover .delete-btn { display:block; }
    .add-btn { flex:0 0 auto; width:94px; height:94px; display:flex; align-items:center; justify-content:center; border:2px dashed #fff; border-radius:8px; font-size:48px; color:#fff; text-decoration:none; }
    .add-btn:hover { background:rgba(255,255,255,0.1); }
    .empty { opacity:0.6; }
  </style>
</head>
<body>
  <div class="my-header">
    <button class="my-btn" onclick="location.href='../main.php'">메인</button>
    <button class="my-btn" onclick="location.href='../chatPage/chatroom.php'">채팅</button>
    <button class="my-btn" onclick="location.href='./ProfilePage.php'">
    <?=htmlspecialchars($nickname, ENT_QUOTES, 'UTF-8')?></button>
    <button class="my-btn" onclick="location.href='../loginPage/logout.php'">로그아웃</button>
  </div>
  <div class="my-container">
    <div class="my-profile-row">
      <div class="my-profile-imgbox">
        <img class="my-profile-img" src="../icons/tomato.png" alt="profile" />
      </div>
      <div class="my-user-id">닉네임: <?=htmlspecialchars($nickname, ENT_QUOTES, 'UTF-8')?></div>
    </div>
    <div class="my-box">
      <!-- 즐겨찾기 -->
      <div class="my-section">
  <div class="my-section-title">즐겨찾기</div>
  <div id="favorite-items">
    <?php if (empty($favorites)): ?>
      <p class="empty">등록된 즐겨찾기가 없습니다.</p>
    <?php else: foreach ($favorites as $fav): ?>
      <?php $img = base64_encode($fav['GOOD_IMAGE']->load()); ?>
      <div class="my-item">
        <a href="ProfilePage.php?delete_fav_id=<?=htmlspecialchars($fav['FAVORITE_ID'])?>" class="delete-btn" onclick="return confirm('정말 삭제하시겠습니까?');">&times;</a>
        <a href="../gooddetail.php?good_id=<?=htmlspecialchars($fav['GOOD_ID'])?>" class="my-item-link" style="display:block; text-decoration:none; color:inherit;">
          <div class="my-item-img">
            <img src="data:image/jpeg;base64,<?=$img?>" alt="" />
          </div>
          <div class="my-item-name"><?=htmlspecialchars($fav['GOOD_NAME'], ENT_QUOTES, 'UTF-8')?></div>
          <div class="my-item-price"><?=htmlspecialchars($fav['CATEGORY_NAME'] ?? '-', ENT_QUOTES, 'UTF-8')?></div>
          <div class="my-item-price"><?=htmlspecialchars($fav['GOOD_PRICE'], ENT_QUOTES, 'UTF-8')?> 원</div>
        </a>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>


      <!-- 내 상품 -->
      <div class="my-section">
        <div class="my-section-title">내 상품</div>
        <div id="product-items">
          <a href="./ProductForm.php" class="add-btn" title="상품 추가">+</a>
          <?php if (empty($products)): ?>
            <p class="empty">등록된 상품이 없습니다.</p>
          <?php else: foreach ($products as $prod): ?>
            <?php $img = base64_encode($prod['GOOD_IMAGE']->load()); ?>
            <div class="my-item">
  <a href="./ProductEdit.php?good_id=<?=htmlspecialchars($prod['GOOD_ID'])?>" style="text-decoration:none; color:inherit;">
    <div class="my-item-img"><img src="data:image/jpeg;base64,<?=$img?>" alt="" /></div>
    <div class="my-item-name"><?=htmlspecialchars($prod['GOOD_NAME'], ENT_QUOTES, 'UTF-8')?></div>
    <div class="my-item-price"><?=htmlspecialchars($prod['CATEGORY_NAME'] ?? '-', ENT_QUOTES, 'UTF-8')?></div>
    <div class="my-item-price"><?=htmlspecialchars($prod['GOOD_PRICE'], ENT_QUOTES, 'UTF-8')?> 원</div>
  </a>
  <a href="ProfilePage.php?delete_prod_id=<?=htmlspecialchars($prod['GOOD_ID'])?>" class="delete-btn" onclick="return confirm('정말 삭제하시겠습니까?');">&times;</a>
</div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
