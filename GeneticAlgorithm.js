
function GA(par){
    var pa = {
        fitness:function(g){return 1;},/* 适应度函数 */
        chrLength:5,/* 染色体长度 */
        geneType:[0,1],/* 基因类型 */
        popSize:100,/* 种群大小 */
        survival:0.8,/* 生存率 */
        variation:0.3,/* 变异概率 */
        loop:2000,/* 循环代数 */
        stop:12/* 停止适应度 */
    };

    /* 参数复制 */
    for (var name in par){
        pa[name] = par[name];
    }

    var _POP_ = [];

    /*
     * 从尾部到头部，比较一个个体的适应度，插入到种群中
     */
    function sortPush(v) {
        var len = _POP_.length;
        for (var i = len-1; i > -1; i--) {
            if (v.fitness <= _POP_[i].fitness) {
                _POP_[i+1] = new Object();
                _POP_[i+1].fitness = v.fitness;
                _POP_[i+1].chromosome = new Array(pa.chrLength);
                for (var j = 0; j < pa.chrLength; j++) {
                    _POP_[i+1].chromosome[j] = v.chromosome[j];
                }
                return true;
            } else {
                _POP_[i+1] = _POP_[i];
            }
        }
        _POP_[0] = new Object();
        _POP_[0].fitness = v.fitness;
        _POP_[0].chromosome = new Array(pa.chrLength);
        for (var j = 0; j < pa.chrLength; j++) {
            _POP_[0].chromosome[j] = v.chromosome[j];
        }
        return true;
    }

    /*
     * 为了减少调用用户外部定义的适应度函数，加快计算的时间，这里定义了一个字典。
     * 调用外部函数前先检查是不是以前计算过，是的话直接从以前的计算结果返回。
     */
    var _gHash = new Object();
    var _hid = 0;
    var _nHid = 0;
    var callFitness = function(gene) {
        var hash = '+';
        for (var i = 0; i < gene.length; i++) {
            hash += gene[i];
        }
        if ('undefined' != typeof(_gHash[hash])) {
            _hid++;
            return _gHash[hash];
        } else {
            _nHid++;
            _gHash[hash] = pa.fitness(gene);
            return _gHash[hash];
        }
    };

    /* 创建新的个体并将此个体插入种群 */
    var ind;
    for (var i = 0; i < pa.popSize; i++){
        ind = {"chromosome":[],"fitness":null};
        for (var j = 0; j < pa.chrLength; j++){
            ind.chromosome.push(pa.geneType[parseInt(Math.random()*pa.geneType.length)]);
        }
        ind.fitness = callFitness(ind.chromosome);
        sortPush(ind);
    }

    /*
     * 对基因进行变异
     */
    function variation(chr) {
        if (Math.random() > pa.variation) {
            return pa.geneType[parseInt(Math.random()*pa.geneType.length)];
        }
        return chr;
    }

    var saveNum   = parseInt(pa.survival * pa.popSize);
    var delNum    = pa.popSize - saveNum;
    var halfg = parseInt(pa.chrLength / 2);
    var newBd = new Object();
    newBd.chromosome = new Array(pa.chrLength);
    newBd.fitness = 0;
    var p1, p2;

    console.log('每次将保留:'+saveNum+'个个体，删除'+delNum+'个个体到下一次循环');
    console.log('染色体1/2断点:'+halfg);
    console.log('初始化结束，开始迭代');
    for (var i = 0; i < pa.loop; i++){
        if (_POP_[0].fitness >= pa.stop){
            break;
        }
        /* 删除后面适应度小的个体 */
        _POP_.length = saveNum;

        /* 诞生新的个体添加到种群 */
        for (var j = 0; j < delNum; j++) {
            /* 随机选择两个父辈 */
            p1 = parseInt(saveNum * Math.random());
            p2 = parseInt(saveNum * Math.random());
            /* 前半段用p1的基因，后半段用p2的 */
            for (var k = 0; k < pa.chrLength; k++) {
                if (k < halfg) {
                    newBd.chromosome[k] = variation(_POP_[p1].chromosome[k]);
                } else {
                    newBd.chromosome[k] = variation(_POP_[p2].chromosome[k]);
                }
            }
            newBd.fitness = callFitness(newBd.chromosome);
            sortPush(newBd);
        }
    }
    console.log('哈希字典命中率'+(_hid / (_hid+_nHid)));
    console.log('在第'+(i-1)+'代达到循环条件，最佳适应度'+JSON.stringify(_POP_[0].fitness));
    var result  = new Array(_POP_[0].chromosome.length);
    for (var i = 0; i < _POP_[0].chromosome.length; i++) {
        result[i] = _POP_[0].chromosome[i];
    }
    return result;
}