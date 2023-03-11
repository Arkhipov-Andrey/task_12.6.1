<?php
// $dir = $_SERVER['DOCUMENT_ROOT'];
// $path = '/module_12/task_12.6.1/persons.php';
// $file = $dir . $path;

// Подключение файла с массивом
$file =  __DIR__ . '/persons.php';
require_once($file);

/**
 * Константы для удобства
 */
define('MALE', 1);
define('FEMALE', -1);
define('UNDEFINED', 0);


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
 * Приведение строки к виду 'Петров' первый симол прописной, остальные строчные
 *
 * @param string $str
 * @return string
 */
// function mb_ucfirst(string $str): string
// {
//     $str = mb_strtolower($str);
//     $fs = mb_substr($str, 0, 1);
//     $fs = mb_strtoupper($fs);
//     $str = $fs . mb_substr($str, 1);
//     return $str;
// };

/**
 * Получение полного имени в одну строку
 *
 * @param string $sorname
 * @param string $name
 * @param string $patronymic
 * @return string
 */
function getFullnameFromParts(string $surname, string $name, string $patronomyc): string
{
    return implode(' ', [$surname, $name, $patronomyc]);
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
    $keys = ['surname', 'name', 'patronomyc'];
    $value = explode(' ', $fullname);
    return array_combine($keys, $value);
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

    $name = $person['name'];
    $short_sorname = mb_substr($person['surname'], 0, 1);
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

    $person = getPartsFromFullname($fullname);

    $surname_cond = ['ва' => -1, 'в' => 1];
    $name_cond = ['а' => -1, 'й' => 1, 'н' => 1];
    $patronomyc_cond = ['вна' => -1, 'ич' => 1];

    foreach ($surname_cond as $key => $val) {
        if (str_ends_with($person['surname'], $key)) {
            $gender += $val;
        }
    }

    foreach ($name_cond as $key => $val) {
        if (str_ends_with($person['name'], $key)) {
            $gender += $val;
        }
    }

    foreach ($patronomyc_cond as $key => $val) {
        if (str_ends_with($person['patronomyc'], $key)) {
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
function getGenderDescription(array $persons): string
{
    $persons = array_column($persons, 'fullname');
    $quantity_person = count($persons);

    $male = array_filter($persons, function ($val) {
        return getGenderFromName($val) === MALE;
    });
    $male = count($male);

    $female = array_filter($persons, function ($val) {
        return getGenderFromName($val) === FEMALE;
    });
    $female = count($female);

    $undefined = array_filter($persons, function ($val) {
        return getGenderFromName($val) === UNDEFINED;
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

function getPerfectPartner(string $surname, string $name, string $patronomyc, array $persons): string
{
    $persons = array_column($persons, 'fullname');

    $first_name['fullname'] = getFullnameFromParts($surname, $name, $patronomyc);
    $first_name['fullname'] = mb_convert_case($first_name['fullname'], MB_CASE_TITLE);

    $first_name['gender'] = getGenderFromName($first_name['fullname']);

    if (!$first_name['gender']) {
        return $result_string = 'Не удается определить пол';
    } else {
        $pairs = [];

        switch ($first_name['gender']) {
            case MALE:
                $pairs = array_filter($persons, function ($val) {
                    return getGenderFromName($val) === FEMALE;
                });
                break;
            case FEMALE:
                $pairs = array_filter($persons, function ($val) {
                    return getGenderFromName($val) === MALE;
                });
                break;
        }

        if (!count($pairs)) {
            return $result_string = 'Пара не найдена';
        } else {
            $pair = $pairs[array_rand($pairs, 1)];
            $compatibility = round(50 + mt_rand(0, (100 - 50) * 1000) / 1000, 2);

            $result_string = getShortName($first_name['fullname']) . " + " .  getShortName($pair) . " = \n";
            $result_string .= "\u{2661} Идеально на {$compatibility}% \u{2661}";
            return $result_string;
        }
    }
}

view(getPerfectPartner('Бардо', 'Жаклин', 'Фёдоровна', $example_persons_array));
echo '<br>';
view(getPerfectPartner('ПЕТРОВ', 'ПеТр', 'петрович', $example_persons_array));
echo '<br>';
