<?php
/**
 * Created by PhpStorm.
 * User: mylonov
 * Date: 9/15/15
 * Time: 10:11 PM
 */

const RECEIVERS_PER_GROUP = 5;
const PER_PERSON = 5;

/**
 * Get $person and assign PER_GROUP other persons to it in random from the $otherPersons list. $otherPersons does not
 * include $person (if you don't want it of course).
 *
 * @param $person
 * @param array $otherPersons
 * @param $counts
 * @return array
 */
function createGroupFor($person, $otherPersons = [], &$counts)
{
    $group = [$person];
    $max = count($otherPersons);

    for ($i = 1; $i <= RECEIVERS_PER_GROUP; $i++) {
        $random = rand(1, $max);

        if (!isset($otherPersons[$random])) {
            $i--;
            continue;
        }
        if (!isset($counts[$random])) {
            $counts[$random] = 0;
        } else if ($counts[$random] == PER_PERSON) {
            continue;
        }
        $group[] = $otherPersons[$random];
        $counts[$random]++;
        unset($otherPersons[$random]);
    }

    return $group;
}

/**
 * Take flat array of associative arrays $persons, convert to assoc array with key being person id (first field) and
 * value being the person array.
 *
 * @param $persons
 * @return array
 */
function transcode($persons)
{
    $result = [];

    foreach ($persons as $person) {
        $k = $person[0];
        $result[$k] = $person;
    }

    return $result;
}

/**
 * Just read in CSV into array.
 *
 * @param $fileName
 * @return array
 */
function readCsvFile($fileName)
{
    $rows = [];
    if (($handle = fopen($fileName, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $rows[] = $data;
        }
        fclose($handle);
    } else {
        die('Input file cannot be open');
    }

    return $rows;
}

/**
 * Determines how we want to save groups to the csv. By default we put all group persons in a chunk and separate next
 * group by a single empty line.
 *
 * @param $fileName
 * @param $groups
 */
function saveIntoCsvFile($fileName, $groups)
{
    if (($handle = fopen($fileName, "w")) !== false) {
        foreach ($groups as $group) {
            $line = [];
            foreach ($group as $person) {
                $line[] = $person[1];
            }
            fputcsv($handle, $line);
        }
        fclose($handle);
    } else {
        die('Output file cannot be open');
    }
}

/**
 * This is being run to do stuff.
 */
function main($argv)
{
    $input = $argv[1];
    $csv = readCsvFile($input);
    $persons = transcode($csv);
    $groups = [];
    $counts = [];
    foreach ($persons as $k => $p) {
        $others = array_diff_key($persons, [$k => $p]); // remove person to avoid duplicate in group
        $group = createGroupFor($p, $others, $counts);

        $groups[] = $group;
    }

    saveIntoCsvFile($argv[2], $groups);
}

main($argv);
