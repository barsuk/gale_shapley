<?php
// Gale-Shapley Stable Matching Algorithm
// From Kleinberg and Tardoc
// Second version of mine: with lists and arrays
// P.45-47 of English version
// P.73-74 of Russian version

class Man {

    function __toString()
    {
        return sprintf("Man #%d, name %s", $this->id, $this->name);
    }

    function __construct(
        public int $id,
        public string $name
    ) { }
}

class Woman {

    function __toString()
    {
        return sprintf("Woman #%d, name %s", $this->id, $this->name);
    }

    function __construct(
        public int $id,
        public string $name
    ) { }
}

$women = [
    new Woman(1, "Tania"),
    new Woman(2, "Natasha"),
    new Woman(3, "Masha"),
    new Woman(4, "Gulia"),
];

$men = [
    new Man(1, "Vasia"),
    new Man(2, "Nikita"),
    new Man(3, "Misha"),
    new Man(4, "Kolia"),
];

// изначально все мужчины свободны
$free_men = array_map(
    fn(Man $m) => $m->id,
    $men
);

// предпочтения мужчин. Мужчина, [женщина => ранг]
$ManPref = [
    1 => [
        1 => 2,
        2 => 4,
        3 => 1,
        4 => 3,
    ],
    2 => [
        1 => 4,
        2 => 2,
        3 => 3,
        4 => 1,
    ],
    3 => [
        1 => 4,
        2 => 1,
        3 => 2,
        4 => 3,
    ],
    4 => [
        1 => 4,
        2 => 1,
        3 => 3,
        4 => 2,
    ],
];

// предпочтения женщин. Женщина, мужчина, ранг
$WomanPref = [
    1 => [
        1 => 2,
        2 => 4,
        3 => 1,
        4 => 3,
    ],
    2 => [
        1 => 3,
        2 => 4,
        3 => 2,
        4 => 1,
    ],
    3 => [
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
    ],
    4 => [
        1 => 4,
        2 => 1,
        3 => 2,
        4 => 3,
    ],
];

// чтобы не искать "лучшую", а сразу получить -- отсортируем предпочтения мужчин по ключу
function man_woman_sort(array &$a) {
    arsort($a);
    $a = array_keys($a);
}
array_walk($ManPref, "man_woman_sort");

// заполняем массив "следующих" в поиске лучшей женщины
$Next = array_fill_keys($free_men, 0);

// массив помолвленных женщин
$Current = array_fill_keys(array_keys($WomanPref), null);

while(!empty($free_men)) {
    $man = array_pop($free_men);

    // получаем ту самую через "следующая!" в Next
    $highestWoman_id = $ManPref[$man][$Next[$man]];
    // printf("Highest woman for %s is %s\n", $man, $highestWoman_id); continue;

    // сдвигаем на следующую, независимо от ответа на предложение
    $Next[$man] += 1;

    // проверим, свободна ли женщина
    if (is_null($Current[$highestWoman_id])) {
        printf("помолвка: %s & %s\n", $man, $highestWoman_id);
        $Current[$highestWoman_id] = $man;
        continue;
    }

    if ($WomanPref[$highestWoman_id][$man] > $WomanPref[$highestWoman_id][$Current[$highestWoman_id]]) {
        // отправляем менее успешного экса искать следующую...
        $free_men[] = $Current[$highestWoman_id];
        
        // ...и помолвка
        printf("помолвка: %s & %s\n", $man, $highestWoman_id);
        $Current[$highestWoman_id] = $man;
    } else {
        // возвращаем в холостяки, в следующий раз повезёт
        $free_men[] = $man;
    }
}

foreach ($Current as $w => $m) {
    printf("Man %s married with %s\n", $m, $w);
}