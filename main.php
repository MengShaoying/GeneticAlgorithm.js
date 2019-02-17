<?php
require 'Gene.php';
require 'Chromosome.php';
require 'Population.php';

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
    $pop = $pop->getNextPopulation(10, 0.01);
}
