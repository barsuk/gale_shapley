<?php

$men = [
    'Petya' => [
        'Tanya' => 1,
        'Natasha' => 2,
    ],
    'Vasya' => [
        'Natasha' => 1,
        'Tanya' => 2,
    ],
];

$women = [
    'Natasha' => [
        'Petya' => 1,
        'Vasya' => 2,
    ],
    'Tanya' => [
        'Vasya' => 1,
        'Petya' => 2,
    ],
];

$free_men = array_keys($men);

$engagement = [];

while (!empty($free_men)) {
    $man = $free_men[count($free_men) - 1];
    $best_woman_name = key(array_splice($men[$man], -1, 1));

    if (!key_exists($best_woman_name, $engagement)) {
        $engagement[] = [$best_woman_name => $man];
        array_pop($free_men);
    } else {
        $bw_pref_list = $women[$best_woman_name];
        $is_old_better = $bw_pref_list[$engagement[$best_woman_name]] > $bw_pref_list[$man];
        if (!$is_old_better) {
            // Удаляем из холостяков мачо
            array_pop($free_men);
            // добавляем в холостяки брошенного
            array_push($free_men, $engagement[$best_woman_name]);
            // обручаем их
            $engagement[$best_woman_name] = $man;
        }
    }
}

print_r($engagement);

function isWomanEngaged($w_name, array $e): array
{
    foreach ($e as $key => $couple) {
        if (in_array($w_name, $couple))
            return ['key' => $key, 'couple' => $couple];
    }
    return [];
}

