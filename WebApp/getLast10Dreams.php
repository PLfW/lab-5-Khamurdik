<?php
$conn = new mysqli(localhost, root, root, dream_catcher);


$sql = "SELECT post.id_post, post.id_user, account.first_name, post.post_text, post.created_at FROM post LEFT JOIN account ON post.id_user = account.id_user LIMIT 10 ORDER BY post.created_at;";

$result = $conn->query($sql);

$rows = array();
while($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}
print json_encode($rows);