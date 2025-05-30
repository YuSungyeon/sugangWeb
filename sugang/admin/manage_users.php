<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

/* ─── 1. 검색 파라미터 처리 ─── */
$keyword = trim($_GET['q'] ?? '');

/* ─── 2. 사용자 조회 쿼리 ─── */
if ($keyword !== '') {
    $sql  = "SELECT 학번, 이름, 관리자여부
             FROM 사용자
             WHERE 학번 LIKE ? OR 이름 LIKE ?
             ORDER BY 학번";
    $stmt = $con->prepare($sql);
    $like = '%'.$keyword.'%';
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql  = "SELECT 학번, 이름, 관리자여부 FROM 사용자 ORDER BY 학번";
    $result = $con->query($sql);
}
?>

<h2>사용자 관리</h2>

<!-- ─── 3. 검색 form ─── -->
<form method="get" style="margin-bottom:10px;">
    <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>"
           placeholder="학번 또는 이름 검색" style="width:160px;">
    <button type="submit">검색</button>
    <?php if ($keyword !== ''): ?>
        <a href="manage_users.php" style="margin-left:8px;">검색 초기화</a>
    <?php endif; ?>
</form>

<table border="1" cellpadding="5">
    <tr>
        <th>학번</th><th>이름</th><th>권한</th><th>권한 변경</th><th>삭제</th>
    </tr>

<?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['학번'] ?></td>
        <td><?= $row['이름'] ?></td>
        <td><?= $row['관리자여부'] ? '관리자' : '사용자' ?></td>

        <!-- 권한 변경 -->
        <td>
          <?php if ($_SESSION['userID'] !== $row['학번']): ?>
            <form action="change_role.php" method="post" onsubmit="return checkApproval(this);">
              <input type="hidden" name="학번" value="<?= $row['학번'] ?>">
              <?php if ($row['관리자여부']): ?>
                <input type="text" name="승인코드" placeholder="코드 입력" style="width:80px;">
              <?php endif; ?>
              <button type="submit">권한변경</button>
            </form>
          <?php else: ?>(본인)<?php endif; ?>
        </td>

        <!-- 삭제 -->
        <td>
          <?php if ($_SESSION['userID'] !== $row['학번'] && !$row['관리자여부']): ?>
            <form action="delete_user.php" method="post"
                  onsubmit="return confirm('정말 삭제하시겠습니까?');">
              <input type="hidden" name="학번" value="<?= $row['학번'] ?>">
              <button type="submit">삭제</button>
            </form>
          <?php elseif ($row['관리자여부']): ?>(관리자 계정)
          <?php else: ?>(본인)<?php endif; ?>
        </td>
    </tr>
<?php endwhile; ?>
</table>

<br><a href="/sugang/admin">→ 관리자 메인 페이지로</a>

<script>
function checkApproval(f){
  const inp=f.querySelector("input[name='승인코드']");
  if(inp && inp.value.trim()!=='sugang'){
    alert("관리자 권한을 해제하려면 '승인코드'를 입력해야 합니다.");return false;
  }
  return true;
}
</script>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
$con->close();
?>