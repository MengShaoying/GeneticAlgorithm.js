<?php
/**
 * 基因类
 *
 * 基因类实现了遗传算法中的"基因"概念。
 *
 */
class Gene
{
    /** @var string 保存代表基因型的字符串。 */
    private $geneType;

    /**
     * 构造方法，通过给定的代表基因型的字符串创建一个基因对象。
     *
     * @param string $type 基因型。
     */
    public function __construct(string $type)
    {
        $this->geneType = $type;
    }

    /**
     * 创建并返回一个与自身基因型完全一样的新的基因对象。
     *
     * @return Gene
     */
    public function geneClone() :Gene
    {
        return new static($this->geneType);
    }

    /**
     * 获取代表基因型的字符串。
     *
     * @return string
     */
    public function type(): string
    {
        return $this->geneType;
    }
}
