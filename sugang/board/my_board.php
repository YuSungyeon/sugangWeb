<?php
session_start();
// 데이터베이스 연결 설정 & 로그인 상태 확인 & 헤더
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/check_login.php';
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

// 사용자 학번
$userID = $_SESSION['userID'];

// 내가 작성한 게시글 조회 ────────────────────────────────
$sql = "
  SELECT 게시글.게시글ID, 게시글.제목, 게시글.작성시간, 사용자.이름
    FROM 게시글
    JOIN 사용자 ON 게시글.작성자 = 사용자.학번
   WHERE 게시글.작성자 = ?
ORDER BY 게시글.게시글ID DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $userID);
$stmt->execute();
$result = $stmt->get_result();
/* ──────────────────────────────────────────────── */
?>

<h2>내가 쓴 글</h2>

<table border="1" cellpadding="6">
  <tr>
    <th>번호</th>
    <th>제목</th>
    <th>작성일</th>
    <th>관리</th>
  </tr>

  <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row['게시글ID']) ?></td>
      <td>
        <a href="/sugang/board/board_view.php?id=<?= $row['게시글ID'] ?>">
          <?= htmlspecialchars($row['제목']) ?>
        </a>
      </td>
      <td><?= htmlspecialchars($row['작성시간']) ?></td>
      <td>
        <a href="/sugang/board/board_edit.php?id=<?= $row['게시글ID'] ?>">✏ 수정</a>
        |
        <a href="/sugang/board/board_delete.php?id=<?= $row['게시글ID'] ?>&post=<?= $id ?>" onclick="return confirm('정말 삭제하시겠습니까?');">🗑 삭제</a>
      </td>
    </tr>
  <?php endwhile; ?>

  <!-- 작성한 게시글이 없으면 해당 내용 출력 -->
  <?php if ($result->num_rows === 0): ?>
    <tr><td colspan="4">작성한 게시글이 없습니다.</td></tr>
  <?php endif; ?>
</table>

<p><a href="/sugang/user/mypage.php">← 마이페이지로 돌아가기</a></p>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
$con->close();
?>