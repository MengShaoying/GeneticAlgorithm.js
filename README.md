# 使用PHP实现的遗传算法程序

源代码将基因Gene，染色体Chromosome和种群Population分开为对应的类，以面向对象的
方式简单地实现了遗传算法。

# 代码的使用方法

## Gene类

可以在创建基因的时候指定类型。类型是一个字符串，通过`type()`方法可以获取当初设
置的内容。示例：

    $t = new Gene('T');
    $c = new Gene('C');
    var_dump($t->type());
    var_dump($c->type());

`Gene`对象的`geneClone()`方法可以复制自身的类型生成一个新的Gene实例。

    $a = new Gene('T');
    $b = $a->geneClone();
    var_dump($b->type());

## Chrmosome类

染色体是`Chromosome`类的实例。随机初始化一个染色体需要指定染色体的初始化参数。
例如：

    $example = new Chromosome(
        Chromosome::RANDOM_INITIALIZATION, // 随机初始化
        10, // 染色体长度
        ['T', 'C', 'G', 'A'] // 从TCGA中随机选择
    );

利用`dump()`方法可以获得初始化的染色体中的信息。例如：

    echo $example->dump();

echo语句输出的内容类似：

    chromosome[10]:GTAGCTCCAC

也可以根据指定的基因排列来生成一个染色体：

    $example = new Chromosome(
        Chromosome::STATIC_INITIALIZATION, // 根据给定排列生成
        10,
        ['T', 'C', 'G', 'A'],
        ['T', 'T', 'T', 'T', 'T', 'C', 'G', 'A', 'T', 'T'] // 排列
    );
    echo $example->dump();

上面的代码运行时将输出：

    chromosome[10]:TTTTTCGATT

在遗传算法里，染色体是带有一个适应度的属性的。利用`fitness()`方法可获得这个适应
度：

    $chromosome = new Chromosome(
        Chromosome::RANDOM_INITIALIZATION,
        10,
        ['T', 'C', 'G', 'A']
    );
    var_dump($chromosome->dump());
    var_dump($chromosome->fitness());

这里需要注意的是，根据不同的问题可以计算不同的适应度，这里计算适应度的方法是基
因型在允许出现的基因型的数组中的次序进行求和算出来的。

# 种群:Population

随机初始化一个种群的方法

    $pop = new Population(3, 4, ['A', 'B']);
    print_r($pop->dump());

上面的代码3表示种群由3个个体组成，4表示个体的染色体的长度，`['A', 'B']`是基因
型。`dump()`方法用于返回调试信息。上面的例子返回内容类似

    population[3]:
    [0]chromosome[4]:BAAA
    [1]chromosome[4]:ABAA
    [2]chromosome[4]:BABA

获取具有最大适应度的个体的方法`getMaximumFitnessChromosome()`

    $pop = new Population(300, 4, ['A', 'B', 'C', 'D']);
    print_r($pop->getMaximumFitnessChromosome()->dump());

上面的代码输出类似

    chromosome[4]:DDDD
