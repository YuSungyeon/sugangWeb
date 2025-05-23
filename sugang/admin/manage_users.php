<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/admin/include/admin_check.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/sugang/include/db.php';
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/header.php';

$sql = "SELECT 학번, 이름, 관리자여부 FROM 사용자 ORDER BY 학번";
$result = $con->query($sql);
?>

<h2>사용자 관리</h2>

<table border="1" cellpadding="5">
    <tr>
        <th>학번</th>
        <th>이름</th>
        <th>권한</th>
        <th>권한 변경</th>
        <th>삭제</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['학번'] ?></td>
            <td><?= $row['이름'] ?></td>
            <td><?= $row['관리자여부'] ? '관리자' : '사용자' ?></td>

            <!-- 권한 변경 폼 -->
            <td>
                <?php if ($_SESSION['userID'] !== $row['학번']): ?>
                    <form action="change_role.php" method="post" onsubmit="return checkApproval(this);">
                        <input type="hidden" name="학번" value="<?= $row['학번'] ?>">
                        
                        <?php if ($row['관리자여부'] == 1): ?>
                            <input type="text" name="승인코드" placeholder="코드 입력" style="width: 80px;">
                        <?php endif; ?>

                        <button type="submit">권한변경</button>
                    </form>
                <?php else: ?>
                    (본인)
                <?php endif; ?>
            </td>

            <!-- 삭제 버튼 -->
            <td>
                <?php if ($_SESSION['userID'] !== $row['학번'] && !$row['관리자여부']): ?>
                    <form action="delete_user.php" method="post" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                        <input type="hidden" name="학번" value="<?= $row['학번'] ?>">
                        <button type="submit">삭제</button>
                    </form>
                <?php elseif ($row['관리자여부']): ?>
                    (관리자 계정)
                <?php else: ?>
                    (본인)
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
<br>
<a href="/sugang/admin">-> 관리자 메인 페이지로</a>

<script>
function checkApproval(form) {
    const input = form.querySelector("input[name='승인코드']");
    if (input && input.value.trim() !== 'sugang') {
        alert("관리자 권한을 해제하려면 '승인코드'를 입력해야 합니다.");
        return false;
    }
    return true;
}
</script>

<?php
include $_SERVER['DOCUMENT_ROOT'].'/sugang/include/footer.php';
$con->close();
?>
