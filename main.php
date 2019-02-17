<?php
require 'Gene.php';
require 'Chromosome.php';
require 'Population.php';

function addCsv($file, $maximum, $average, $minimum, $variance)
{
    static $id = 0;
    $id++;
    if (!is_file($file)) {
        file_put_contents($file, 'id,maximum,average,minimum,variance' . PHP_EOL);
    }
    file_put_contents($file, "$id,$maximum,$average,$minimum,$variance" . PHP_EOL, FILE_APPEND);
}

$pop = new Population(100, 4, ['G', 'T', 'A', 'C']);
for ($i = 0; $i < 20; $i++) {
    $maximum = $pop->getMaximumFitnessChromosome()->fitness();
    $average = $pop->getAverageFitness();
    $minimum = $pop->getMinimumFitnessChromosome()->fitness();
    $variance = $pop->getVariance();
    echo 'maximum ' . sprintf('%.2f', $maximum);
    echo '  ';
    echo 'average ' . sprintf('%.2f', $average);
    echo '  ';
    echo 'minimum ' . sprintf('%.2f', $minimum);
    echo '  ';
    echo 'variance ' . sprintf('%.2f', $variance);
    echo PHP_EOL;
    addCsv('log.csv', $maximum, $average, $minimum, $variance);
    $pop = $pop->getNextPopulation(10, 0.01);
}
