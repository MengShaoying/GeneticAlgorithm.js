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

$popSize = 200;
$chromosomeLength = 60;
$loop = 100000;
$geneTypes = ['0', '1'];

$pop = new Population($popSize, $chromosomeLength, $geneTypes);
for ($i = 0; $i < $loop; $i++) {
    $maximum = $pop->getMaximumFitnessChromosome()->fitness();
    $average = $pop->getAverageFitness();
    $minimum = $pop->getMinimumFitnessChromosome()->fitness();
    $variance = $pop->getVariance();
    list($x1, $x2) = $pop->getMaximumFitnessChromosome()->getNumber();
    echo 'maximum ' . sprintf('%.2f', $maximum);
    echo '  ';
    echo 'average ' . sprintf('%.2f', $average);
    echo '  ';
    echo 'minimum ' . sprintf('%.2f', $minimum);
    echo '  ';
    echo 'variance ' . sprintf('%.2f', $variance);
    echo '  ';
    echo "x1=$x1,x2=$x2";
    echo PHP_EOL;
    if ($i % 100 == 0)
        addCsv('log.csv', $maximum, $average, $minimum, $variance);
    $pop = $pop->getNextPopulation(10, 0.08);
}
