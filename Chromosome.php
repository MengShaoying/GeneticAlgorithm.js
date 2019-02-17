<?php
/**
 * 染色体类
 *
 */
class Chromosome
{
    /** @var int 随机初始化 */
    const RANDOM_INITIALIZATION = 0;

    /** @var int 由给定基因链初始化 */
    const STATIC_INITIALIZATION = 1;

    /** @var array 染色体其实就是基因链 */
    private $geneChain;

    /** @var array 字符串数组，允许出现的所有基因型 */
    private $allowTypes;

    /** @var float|null 为了防止重复计算适应度，这个值将会在第一次计算后用来保存适应度的值 */
    private $cacheFitness = null;

    /**
     * 初始化一个染色体
     *
     * @param int $type 初始化类型，目前值能取值RANDOM_INITIALIZATION或STATIC_INITIALIZATION
     * @param int $len 初始化长度
     * @param array $genes 允许随机出现的基因型，可以是Gene对象数组，也可以是字符串数组
     * @param array $chain 给定串初始化时将使用，可以是Gene对象数组，也可以是字符串数组
     */
    public function __construct(int $type, int $len, array $genes, array $chain = [])
    {
        $this->setAllowTypesFromGeneArray($genes);
        switch ($type) {
            case self::RANDOM_INITIALIZATION:
                $this->randomInitialization($len);
                break;
            case self::STATIC_INITIALIZATION:
                $this->initializationFromArray($chain);
                break;
            default:
                $this->randomInitialization($len);
        }
    }

    /**
     * 以字符串数组的方式，返回所有可能的基因型
     *
     * @return string
     */
    public function getAllTyps() :array
    {
        return $this->allowTypes;
    }

    /**
     * 返回染色体的长度
     *
     * @return int
     */
    public function getLength() :int
    {
        return count($this->geneChain, COUNT_NORMAL);
    }

    /**
     * 返回方便阅读的字符串
     *
     * @return string
     */
    public function dump(): string
    {
        $str = 'chromosome[';
        $size = count($this->geneChain, COUNT_NORMAL);
        $str .= $size . ']:';
        for ($i = 0; $i < $size; $i++) {
            $str .= $this->geneChain[$i]->type();
        }
        return $str;
    }

    /**
     * 以p的可能性进行变异
     *
     * 这个方法是不会改变原有对象的内容的。变异后的基因序列将会以新的染色体对象
     * 进行返回。
     *
     * @param float $p 变异概率
     * @return Chromosome 返回一个新的染色体对象
     */
    public function mutation(float $p) :Chromosome
    {
        $newChain = [];
        foreach ($this->geneChain as $gene) {
            if (mt_rand() / mt_getrandmax() > $p) {
                $newChain[] = $gene->geneClone();
            } else {
                $newChain[] = new Gene($this->randomGetOne($this->arrayRemove($this->allowTypes, [$gene->type()])));
            }
        }
        return new static(self::STATIC_INITIALIZATION, count($this->geneChain, COUNT_NORMAL), $this->allowTypes, $newChain);
    }

    /**
     * 与另外一个染色体进行交叉
     *
     * @param Chromosome $another
     * @return Chromosome
     */
    public function crossover(Chromosome $another) :Chromosome
    {
        $seed = mt_rand() % 2;
        $size = count($this->geneChain, COUNT_NORMAL);
        $half = intval($size / 2);
        $chain = [];
        for ($i = 0; $i <= $half; $i++) {
            if ($seed > 0) {
                $chain[] = $this->geneChain[$i]->type();
            } else {
                $chain[] = $another->getGeneByIndex($i)->type();
            }
        }
        for ($i = $half + 1; $i < $size; $i++) {
            if ($seed > 0) {
                $chain[] = $another->getGeneByIndex($i)->type();
            } else {
                $chain[] = $this->geneChain[$i]->type();
            }
        }
        return new static(self::STATIC_INITIALIZATION, $size, $this->allowTypes, $chain);
    }

    /**
     * 返回给定位置的基因
     *
     * @param int $key
     * @return Gene
     */
    public function getGeneByIndex(int $key) :Gene
    {
        return $this->geneChain[$key];
    }

    /**
     * 根据给定的匿名函数计算适应度
     *
     * @return float
     */
    public function fitness() :float
    {
        if (!is_null($this->cacheFitness)) {
            return $this->cacheFitness;
        }
        if ($this->getLength() != 60) {
            throw new Exception('length != 60');
        }
        list($x1, $x2) = $this->getNumber();
        $fitness = 1 / (0.001 + 100 * pow(pow($x1, 2) - $x2, 2) + pow(1 - $x1, 2));
        $this->cacheFitness = $fitness;
        return $fitness;
    }

    public function getNumber() :array
    {
        $min = -3;
        $max = 3;
        $d = 0;
        $bin = [];
        for ($i = $d; $i < $d + 30; $i++) {
            $bin[] = intval($this->geneChain[$i]->type());
        }
        $number1 = $this->toNumber($bin) / pow(2, 30) * ($max - $min) + $min;
        $d = 30;
        $bin = [];
        for ($i = $d; $i < $d + 30; $i++) {
            $bin[] = intval($this->geneChain[$i]->type());
        }
        $number2 = $this->toNumber($bin) / pow(2, 30) * ($max - $min) + $min;
        return [$number1, $number2];
    }

    /**
     * 基因转数字，用于fitness
     *
     * @param array $arr
     * @return int
     */
    private function toNumber(array $arr) :int
    {
        $index = $num = 0;
        foreach ($arr as $gene) {
            $num += $gene == 1 ? pow(2, $index) : 0;
            $index++;
        }
        return $num;
    }

    /**
     * 从给定的数组初始化基因链
     *
     * @param array $chain
     */
    private function initializationFromArray(array $chain)
    {
        $this->geneChain = [];
        foreach ($chain as $gene) {
            $this->geneChain[] = is_object($gene) ? new Gene($gene->type()) : new Gene($gene);
        }
    }

    /**
     * 根据指定的可能基因型随机初始化染色体
     *
     * @return string
     */
    private function randomInitialization(int $len)
    {
        $this->geneChain = [];
        $size = count($this->allowTypes, COUNT_NORMAL);
        for ($i = 0; $i < $len; $i++) {
            $this->geneChain[] = new Gene($this->allowTypes[mt_rand(0, $size - 1)]);
        }
    }

    /**
     * 设置允许随机出现的基因型
     *
     * @param array $genes 允许出现的基因类型，可以是字符串数组，也可以是Gene类
     * 的实例。方法在处理这个数组的元素的时候会判断元素的类型，当遇到元素是对象
     * 的时候会自动调用它的type()方法。
     * @return void
     */
    private function setAllowTypesFromGeneArray(array $genes)
    {
        $this->allowTypes = [];
        $len = count($genes, COUNT_NORMAL);
        for ($i = 0; $i < $len; $i++) {
            $this->allowTypes[] = is_object($genes[$i]) ? $genes[$i]->type() : $genes[$i];
        }
    }

    /**
     * 从给定数组中随机返回一个元素
     *
     * @param array $arr
     * @return mixed
     */
    private function randomGetOne(array $arr)
    {
        return $arr[mt_rand(0, count($arr, COUNT_NORMAL) - 1)];
    }

    /**
     * 从数组中去除指定元素
     *
     * @param array $src
     * @param array $remove
     * @return array
     */
    private function arrayRemove(array $src, array $remove): array
    {
        $result = [];
        foreach ($src as $e) {
            if (!in_array($e, $remove)) {
                $result[] = $e;
            }
        }
        return $result;
    }
}
