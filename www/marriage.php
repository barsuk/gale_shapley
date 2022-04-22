<?php

class DBClient {
    protected object $pdo;

    public function __construct() {
        $db_path = './human.sqlite';
        if (!file_exists($db_path)) 
            die(sprintf('there is no file at %s', $db_path));

        $this->pdo = new PDO(
            "sqlite:$db_path",
            null,
            null,
            [PDO::ATTR_PERSISTENT => true]
        );
    }

    public function getPDO() {
        return $this->pdo;
    }
}

class Woman extends DBClient {
    public int $id;
    public string $name;
    public int $husband;

    public function __toString() {
        return sprintf("Woman #%s, name %s\n", $this->id, $this->name);
    }

    public function isFree(): bool {
        $sth = $this->pdo->query(
            "SELECT woman_id FROM engage WHERE woman_id = $this->id"
        );
        $row = $sth->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return true;
        }

        return false;
    }

    public function prefersHusbandTo(FreeMan $m): bool {
        $sth = $this->pdo->query(
            "SELECT
                (SELECT weight FROM woman_rank WHERE man_id = $this->husband and woman_id = $this->id) >
                (SELECT weight FROM woman_rank WHERE man_id = $m->id and woman_id = $this->id) as is_husband_better"
        );
        $row = $sth->fetch(PDO::FETCH_ASSOC);

        if (count($row) < 1) {
            throw new Exception("bad compare with husband\n");
        }

        return $row['is_husband_better'];

    }
}

class FreeMan extends DBClient {
    public int $id;
    public string $name;

    public function __toString() {
        return sprintf("Man #%s, name %s\n", $this->id, $this->name);
    }

    // returns the best non-proposed woman
    public function getHighestRankedWoman() {
        $sth = $this->pdo->query("SELECT woman.id, woman.name FROM woman 
        JOIN priority ON priority.woman_id = woman.id AND priority.man_id = $this->id AND priority.weight =  
                    (SELECT max(weight) FROM priority WHERE man_id = $this->id AND woman_id NOT IN 
                        (SELECT woman_id FROM proposal WHERE man_id = $this->id)
                        )"
        );

        $woman = $sth->fetchObject("Woman");

        if (!is_object($woman)) {
            throw new Exception("no woman found\n");
        }

        $sthusb = $this->pdo->query("SELECT man_id FROM engage WHERE woman_id = $woman->id");
        $woman->husband = $sthusb->fetchColumn();

        $this->pdo->exec("INSERT INTO proposal(woman_id, man_id) VALUES($woman->id, $this->id)");

        return $woman;
    }

    public function engageWith(Woman $w) {
        $this->pdo->exec("INSERT INTO engage(woman_id, man_id) VALUES($w->id, $this->id)");

        return $this;
    }

    public function replaceExOf(Woman $w) {
        $sth = $this->pdo->query(
            "SELECT * from man WHERE id = 
                (SELECT man_id FROM engage WHERE woman_id = $w->id)"
            );
        $ex = $sth->fetchObject("FreeMan");

        $this->pdo->exec("DELETE engage WHERE woman_id = $w->id AND man_id = $ex->id");
        $this->pdo->exec("INSERT INTO engage(woman_id, man_id) VALUES($w->id, $this->id)");

        return $ex;
    }
}

$db = new DBClient();
$sth = $db->getPDO()->query('SELECT * from man');
$free_men = [];

while($man = $sth->fetchObject("FreeMan")) {
    $free_men[] = $man;
}

while(!empty($free_men)) {
    $man = array_pop($free_men);

    $highestWoman = $man->getHighestRankedWoman();

    if ($highestWoman->isFree()) {
        $man->engageWith($highestWoman);
        continue;
    }

    match ($highestWoman->prefersHusbandTo($man)) {
        false => array_unshift($free_men, $man->replaceExOf($highestWoman)),
        true => array_unshift($free_men, $man),
    };

}

$engaged = $db->getPDO()->query(
    'SELECT man.name AS husband, woman.name AS wife FROM engage
    JOIN man ON man.id = engage.man_id
    JOIN woman ON woman.id = engage.woman_id'
    );

while($couple = $engaged->fetch(PDO::FETCH_ASSOC)) {
    printf("%s married with %s\n", $couple['husband'], $couple['wife']);
}