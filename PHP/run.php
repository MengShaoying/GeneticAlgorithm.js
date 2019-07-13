<?php
$types = ['0', '1'];
$len = 8;
$size = 100;
$generation = 1000;
$variation = 0.01;

function individual()
{
    global $types, $len;
    $individual = [];
    for ($i = 0; $i < $len; $i++) {
        $individual[] = $types[mt_rand(0, count($types) - 1)];
    }
    return $individual;
}

//var_dump(individual());

function population()
{
    global $size;
    $population = [];
    for ($i = 0; $i < $size; $i++) {
        $population[] = individual();
    }
    return $population;
}

//var_dump(population());

function fitness($individual)
{
    $x = intval(implode('', $individual), 2);
    return 1 / (pow((2 - $x), 2) + 1);
}

//var_dump(fitness(individual()));

function select($population)
{
    $fitnesses = array_map('fitness', $population);
    $pointer = mt_rand() / mt_getrandmax() * array_sum($fitnesses);
    $left = 0;
    foreach ($fitnesses as $i => $fitness) {
        if ($pointer <= $left) {
            return $population[$i];
        }
        $left += $fitness;
    }
    return $population[$i];
}

function crossover($individuala, $individualb)
{
    global $len;
    $center = intval($len / 2);
    return array_merge(array_slice($individuala, 0, $center), array_slice($individualb, $center, $len - $center));
}

function variation($individual)
{
    global $variation, $types;
    $new = [];
    foreach ($individual as $gene) {
        if (mt_rand() / mt_getrandmax() < $variation) {
            $choice = $types;
            unset($choice[array_search($gene, $choice)]);
            $new[] = $choice[array_rand($choice)];
        } else {
            $new[] = $gene;
        }
    }
    return $new;
}

$population = population();
//var_dump(select($population));
//var_dump(variation(crossover(select($population), select($population))));

function maxfitness($population)
{
    return max(array_map('fitness', $population));
}

function maxfitnessindividual($population)
{
    $fitnesses = array_map('fitness', $population);
    return $population[array_search(max($fitnesses), $fitnesses)];
}

for ($i = 0; $i < $generation; $i++) {
    $new = [];
    for ($j = 0; $j < $size; $j++) {
        $new[] = variation(crossover(select($population), select($population)));
    }
    $population = $new;
    $maxfitness = maxfitness($population);
    echo sprintf('%.6f', $maxfitness) . PHP_EOL;
    if ($maxfitness >= 0.999) {
        var_dump(maxfitnessindividual($population));
        return 0;
    }
}
