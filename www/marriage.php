<?php

$db_path = './human.sqlite';
if (!file_exists($db_path)) 
    die(sprintf('there is no file at %s', $db_path));

$pdo = new PDO(
    "sqlite:$db_path",
    null,
    null,
    array(PDO::ATTR_PERSISTENT => true)
);

$sth = $pdo->query('SELECT * from woman');
$rows = $sth->fetchAll(PDO::FETCH_ASSOC);

foreach($rows as $k => $v) {
    printf("%s is %s\n", $k, json_encode($v));
}

unset($pdo);