<?php
// $dir = $_SERVER['DOCUMENT_ROOT'];
// $path = '/module_12/task_12.6.1/persons.php';
// $file = $dir . $path;

// Подключение файла с массивом
$file =  __DIR__ . '/persons.php';
require_once($file);

/**
 * Функция для отладки (удобаворимое отображение переменных)
 *
 * @param [mixed] $var
 * @return void
 */
function view($var)
{
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

/**
 * Получение полного имени в одну строку
 *
 * @param string $sorname
 * @param string $name
 * @param string $patronymic
 * @return string
 */
function getFullnameFromParts(string $sorname, string $name, string $patronymic): string
{
    return implode(' ', [$sorname, $name, $patronymic]);
}

view(getFullnameFromParts('Иванов', 'Иван', 'Иванович'));
echo '<br>';

/**
 * Разделение полного имени на части
 *
 * @param string $fullname
 * @return array
 */
function getPartsFromFullname(string $fullname): array
{
    return explode(' ', $fullname);
}

view(getPartsFromFullname('Петров Петр Петрович'));
echo '<br>';

/**
 * Получение короткого имени
 *
 * @param string $fullname
 * @return string
 */
function getShortName(string $fullname): string
{
    $person = getPartsFromFullname($fullname);

    $name = $person[1];
    $short_sorname = mb_substr($person[0], 0, 1);
    $short_person_name = "{$name} {$short_sorname}.";

    return $short_person_name;
}

view(getShortName('Николаев Николай Николаевич'));
echo '<br>';

/**
 * Определение пола
 *
 * @param string $fullname
 * @return integer
 */
function getGenderFromName(string $fullname): int
{
    $gender = 0;

    list($sorname, $name, $patronymic) = getPartsFromFullname($fullname);
    $soname_cond = ['ва' => -1, 'в' => 1];
    $name_cond = ['а' => -1, 'й' => 1, 'н' => 1];
    $patronymic_cond = ['вна' => -1, 'ич' => 1];

    foreach ($soname_cond as $key => $val) {
        if (str_ends_with($sorname, $key)) {
            $gender += $val;
        }
    }

    foreach ($name_cond as $key => $val) {
        if (str_ends_with($name, $key)) {
            $gender += $val;
        }
    }

    foreach ($patronymic_cond as $key => $val) {
        if (str_ends_with($patronymic, $key)) {
            $gender += $val;
        }
    }

    return $gender <=> 0;
}

view(getGenderFromName('Иванов Иван Иванович'));
echo '<br>';
view(getGenderFromName('Степанова Наталья Степановна'));
echo '<br>';
view(getGenderFromName('аль-Хорезми Мухаммад ибн-Муса'));
echo '<br>';

/**
 * Определение возрастно-полового состава
 *
 * @param array $person
 * @return string
 */
function getGenderDescription(array $person): string
{
    $quantity_person = count($person);

    $male = array_filter($person, function ($val) {
        return getGenderFromName($val['fullname']) === 1;
    });
    $male = count($male);

    $female = array_filter($person, function ($val) {
        return getGenderFromName($val['fullname']) === -1;
    });
    $female = count($female);

    $undefined = array_filter($person, function ($val) {
        return getGenderFromName($val['fullname']) === 0;
    });
    $undefined = count($undefined);

    $result_string  = "Гендерный состав аудитории:\n";
    $result_string .= str_repeat('-', mb_strlen($result_string));
    $result_string  .= "\n";
    $result_string  .= "Мужчины - " . round(($male / $quantity_person) * 100, 1) . "%\n";
    $result_string  .= "Женщины  - " . round(($female / $quantity_person) * 100, 1) . "%\n";
    $result_string  .= "Не удалось определить  - " . round(($undefined / $quantity_person) * 100, 1) . "%\n";

    return $result_string;
}

view(getGenderDescription($example_persons_array));
echo '<br>';

function getPerfectPartner(string $sorname, string $name, string $patronymic, array $persons): string
{
    $mb_ucfirst = function (string $str): string {
        $str = mb_strtolower($str);
        $fs = mb_substr($str, 0, 1);
        $fs = mb_strtoupper($fs);
        $str = $fs . mb_substr($str, 1);
        return $str;
    };

    $sorname = $mb_ucfirst($sorname);
    $name = $mb_ucfirst($name);
    $patronymic = $mb_ucfirst($patronymic);

    $first_name['fullname'] = getFullnameFromParts($sorname, $name, $patronymic);
    $first_name['gender'] = getGenderFromName($first_name['fullname']);

    $getRandPerson = function (array $persons): array {
        $person['fullname'] = $persons[array_rand($persons, 1)]['fullname'];
        $person['gender'] = getGenderFromName($person['fullname']);
        return $person;
    };

    if (!$first_name['gender']) {
        return $result_string = 'Не удается определить пол';
    } else {
        $second_name = $getRandPerson($persons);

        if ($first_name['gender'] === $second_name['gender'] || !$second_name['gender']) {
            while ($first_name['gender'] === $second_name['gender'] || !$second_name['gender']) {
                $second_name = $getRandPerson($persons);
            }
        }


        $compatibility = round(50 + mt_rand(0, (100 - 50) * 1000) / 1000, 2);

        $result_string = getShortName($first_name['fullname']) . " + " .  getShortName($second_name['fullname']) . " = \n";
        $result_string .= "\u{2661} Идеально на {$compatibility}% \u{2661}";
        return $result_string;
    }
}

view(getPerfectPartner('Бардо', 'Жаклин', 'Фёдоровна', $example_persons_array));
echo '<br>';
view(getPerfectPartner('ПЕТРОВ', 'ПеТр', 'петрович', $example_persons_array));
echo '<br>';
