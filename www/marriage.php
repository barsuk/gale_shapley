<?php

class Woman {
    public int $id;
    public string $name;

    public function __toString() {
        return sprintf("Woman #%s, name %s\n", $this->id, $this->name);
    }
}

class FreeMan {
    public int $id;
    public string $name;
    public object $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function __toString() {
        return sprintf("Man #%s, name %s\n", $this->id, $this->name);
    }

    // returns the best non-proposed woman
    public function getBestWoman() {
        $sth = $this->db->query("SELECT * FROM woman WHERE id = (
                SELECT woman_id FROM priority WHERE man_id = $this->id AND weight =  
                    (SELECT max(weight) FROM priority WHERE man_id = $this->id and is_proposed = 0)
            )"
        );

        $woman = $sth->fetchObject("Woman");

        return $woman;
    }


}

$db_path = './human.sqlite';
if (!file_exists($db_path)) 
    die(sprintf('there is no file at %s', $db_path));

$pdo = new PDO(
    "sqlite:$db_path",
    null,
    null,
    [PDO::ATTR_PERSISTENT => true]
);

$sth = $pdo->query('SELECT * from man');
$free_men = [];

while($man = $sth->fetchObject("FreeMan", [$pdo])) {
    $free_men[] = $man;
}

while(!empty($free_men)) {
    $man = array_pop($free_men);

    echo $man, " and his ", $man->getBestWoman();
}