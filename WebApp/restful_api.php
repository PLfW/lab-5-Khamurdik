<?php
 
// отримання HTTP методу яким був зроблений запит
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);
 
// створення підключення до MySQL
$link = mysqli_connect('localhost', 'root', 'root', 'dream_catcher');
mysqli_set_charset($link,'utf8');
 
// отримання назви таблиці і ключа із URL запиту
$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
$key = array_shift($request)+0;
 
// парсинг назв колонок та відповідних їм значень у вхідному об’єкті
$columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
$values = array_map(function ($value) use ($link) {
  if ($value===null) return null;
  return mysqli_real_escape_string($link,(string)$value);
},array_values($input));
 
// створення частини SQL запиту специфічної для SET методу
$set = '';
for ($i=0;$i<count($columns);$i++) {
  $set.=($i>0?',':'').'`'.$columns[$i].'`=';
  $set.=($values[$i]===null?'NULL':'"'.$values[$i].'"');
}
 
// створення SQL запиту на основі одного з 4 типів HTTP методів
switch ($method) {
  case 'GET':
    $sql = "select * from `$table`".($key?" WHERE id=$key":''); break;
  case 'PUT':
    $sql = "update `$table` set $set where id=$key"; break;
  case 'POST':
    $sql = "insert into `$table` set $set"; break;
  case 'DELETE':
    $sql = "delete `$table` where id=$key"; break;
}
 
// виклик створеного SQL запиту
$result = mysqli_query($link,$sql);
 
// виклик помилки якщо SQL запит не пройшов успішно
if (!$result) {
  http_response_code(404);
  die(mysqli_error());
}
 
// виведення результатів запиту, або тих змін які він привніс у БД
if ($method == 'GET') {
  if (!$key) echo '[';
  for ($i=0;$i<mysqli_num_rows($result);$i++) {
    echo ($i>0?',':'').json_encode(mysqli_fetch_object($result));
  }
  if (!$key) echo ']';
} elseif ($method == 'POST') {
  echo mysqli_insert_id($link);
} else {
  echo mysqli_affected_rows($link);
}
 
// закриття MySQL підключення
mysqli_close($link);