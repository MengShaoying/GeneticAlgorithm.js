<?php
/**
 * 种群
 *
 */
class Population
{
    /** @var array 保存了多个Chromosome对象 */
    private $chromosomes;

    /**
     * 初始化一个种群
     *
     * @param int $size 种群中个体数量
     * @param int $len 个体的染色体长度
     * @param array $genes 所有基因型
     * @param Chromosome[] $chromosomes 保存了能组成一个种群的染色体数组。当存在这个参数的时候将会从这个参数初始化种群。
     */
    public function __construct(int $size, int $len, array $genes, array $chromosomes = [])
    {
        if (!empty($chromosomes)) {
            $this->chromosomes = $chromosomes;
        } else {
            $this->chromosomes = [];
            for ($i = 0; $i < $size; $i++) {
                $this->chromosomes[] = new Chromosome(Chromosome::RANDOM_INITIALIZATION, $len, $genes);
            }
        }
    }

    /**
     * 返回种群染色体数量
     *
     * @return int
     */
    public function getPopulationSize() :int
    {
        return count($this->chromosomes, COUNT_NORMAL);
    }

    /**
     * 返回方便阅读的字符串
     *
     * @return string
     */
    public function dump() :string
    {
        $str = 'population[';
        $size = count($this->chromosomes, COUNT_NORMAL);
        $str .= $size . ']:' . PHP_EOL;
        for ($i = 0; $i < $size; $i++) {
            $str .=  "[$i]" . $this->chromosomes[$i]->dump() . PHP_EOL;
        }
        return $str;
    }

    /**
     * 获取种群中具有最大适应度的个体
     *
     * @return float
     */
    public function getMaximumFitnessChromosome() :Chromosome
    {
        $chromosomeMumber = count($this->chromosomes, COUNT_NORMAL);
        $maxIndex = 0;
        $maxFitness = $this->chromosomes[$maxIndex]->fitness();
        for ($i = 1; $i < $chromosomeMumber; $i++) {
            $iFitness = $this->chromosomes[$i]->fitness();
            if ($iFitness > $maxFitness) {
                $maxFitness = $iFitness;
                $maxIndex = $i;
            }
        }
        return $this->chromosomes[$maxIndex];
    }

    /**
     * 获取种群中具有最小适应度的个体
     *
     * @return float
     */
    public function getMinimumFitnessChromosome() :Chromosome
    {
        $chromosomeMumber = count($this->chromosomes, COUNT_NORMAL);
        $minIndex = 0;
        $minFitness = $this->chromosomes[$minIndex]->fitness();
        for ($i = 1; $i < $chromosomeMumber; $i++) {
            $iFitness = $this->chromosomes[$i]->fitness();
            if ($iFitness < $minFitness) {
                $minFitness = $iFitness;
                $minIndex = $i;
            }
        }
        return $this->chromosomes[$minIndex];
    }

    /**
     * 获取种群的适应度的平均值。
     *
     * 方法返回的是种群的适应度的平均值。
     *
     * @return float 种群适应度的平均数，这个是一个浮点数。
     */
    public function getAverageFitness() :float
    {
        $chromosomeMumber = count($this->chromosomes, COUNT_NORMAL);
        $sum = 0;
        foreach ($this->chromosomes as $chromosome) {
            $sum += $chromosome->fitness();
        }
        return $sum / $chromosomeMumber;
    }

    /**
     * 返回种群的个体的适应度的方差
     *
     * @return float
     */
    public function getVariance(): float
    {
        $average = $this->getAverageFitness();
        $total = $this->getPopulationSize();
        $variance = 0;
        for ($i = 0; $i < $total; $i++) {
            $variance += pow($this->chromosomes[$i]->fitness() - $average, 2);
        }
        return $variance / $total;
    }

    /**
     * 根据指定的参数得到下一代种群
     *
     * 得到下一代的过程中，会根据染色体的适应度大小先进行排名，排名低的染色体会
     * 被去除
     *
     * 选择的过程是采用轮盘赌，适应度越大的染色体被选择的概率越大。
     *
     * 去除染色体和选择染色体的过程不会影响当前种群中的染色体对象和染色体的排
     * 列，此方法会将新生成的染色体创建为新的Population对象。
     *
     * @param int $kill 去除多少个适应度低的染色体。去除的过程将使用轮盘赌的方
     * 法，根据适应度排名，排名越低的染色体被去除的概率越大。
     * @param float $variation 发生基因突变的概率。
     */
    public function getNextPopulation($kill, $variation): Population
    {
        /* 为了不影响当前种群数组的顺序，这里复制当前的种群 */
        $chromosomes = [];
        foreach ($this->chromosomes as $chromosome) {
            $chromosomes[] = $chromosome;
        }
        /* 适应度由大到小对种群进行了排序 */
        usort($chromosomes, function ($c1, $c2) {
            return $c2->fitness() - $c1->fitness();
        });
        /* 后面的染色体是适应度低的染色体，它们将被新的个体覆盖掉 */
        $populationSize = $this->getPopulationSize();
        for ($i = $populationSize - $kill; $i < $populationSize; $i++) {
            $chromosomes[$i] = $this->selectAndCreate($chromosomes, 0, $populationSize - $kill - 1)->mutation($variation);
        }
        return new static($populationSize, $chromosomes[0]->getLength(), $chromosomes[0]->getAllTyps(), $chromosomes);
    }

    /**
     * 从数组中选择一对染色体并交叉得到新的染色体
     *
     * 这个方法给getNextPopulation用，不要用在其它地方。
     *
     * @param Chromosome[] $chromosomes 从大到小的排列
     * @param int $start 选择的起始，含
     * @param int $end 选择的结束，含
     * @return Chromosome
     */
    private function selectAndCreate($chromosomes, $start, $end) :Chromosome
    {
        /* 轮盘的起点等于0，结束点是$totalFitness */
        $totalFitness = 0;
        for ($i = $start; $i < $end + 1; $i++) {
            $totalFitness += $chromosomes[$i]->fitness();
        }
        /* 两个轮盘指针的位置 */
        $hit1 = mt_rand() / mt_getrandmax() * $totalFitness;
        $hit2 = mt_rand() / mt_getrandmax() * $totalFitness;
        /* 两个选择的染色体 */
        $select1 = null;
        $select2 = null;
        /* 判断指针位于哪个染色体 */
        $totalFitness = 0;
        for ($i = $start; $i < $end + 1; $i++) {
            if ($totalFitness <= $hit1 && $hit1 < $totalFitness + $chromosomes[$i]->fitness()) {
                $select1 = $chromosomes[$i];
            }
            if ($totalFitness <= $hit2 && $hit2 < $totalFitness + $chromosomes[$i]->fitness()) {
                $select2 = $chromosomes[$i];
            }
            if (!is_null($select1) && !is_null($select2)) {
                break;
            }
            $totalFitness += $chromosomes[$i]->fitness();
        }
        return $select1->crossover($select2);
    }
}
