<?php
namespace App;
use \Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;
use \Phpml\ModelManager;


class MegaRegression {
    protected array $predictions;
    protected \stdClass $all;
    protected string $models_path;
    protected string $algorithm;
    protected array $modelArgs;
    protected string $modelFile;

    public function __construct($algClass = LeastSquares::class, ...$args)
    {
        $class = str_replace("App\\", "", get_class($this));
        $this->all = json_decode(file_get_contents(__DIR__."/../data/all-data.json"));  
        $this->models_path = __DIR__."/../models/{$class}";
        $this->algorithm = $algClass;
        $this->modelArgs = $args;
        $this->modelFile = $this->models_path."{$algClass}";
    }

    public function dateToTime(string $date = ""){
        return strtotime(implode('-', array_reverse(explode('/', $date), false)));
    }

    private function getColumnSamplesAndLabels(array $data = [], string $column = "coluna_1") {
        $samples = $targets = [];
        foreach($data as $concurso) {
            if (isset($concurso->$column)) {
                $targets[] = [(int) $concurso->$column];
                $samples[] = [(int) $concurso->$column, $this->dateToTime($concurso->data_do_sorteio)];
            } 
        }

        return (object) [
            "samples" => $samples,
            "targets" => $targets, 
            "column" => $column, 
        ];
    }

    public function getLasConcurso(array $data = []){
        return (object) $data[count($data) - 1];; 
    }


    public function train($class = null,  $args = []){
        if ($class === null) $class = $this->algorithm;
        if ($this->all && count($this->all->data) > 0) {
            foreach (range(1, 6, 1) as $n) {
                $column = "coluna_{$n}";
                $trainData = $this->getColumnSamplesAndLabels($this->all->data, $column);

                $model = null;
                if(count($args) > 0) $model = new $class(...$args);
                else $model = new $class();

                $model->train($trainData->samples, $trainData->targets);
                $modelManager = new ModelManager();
                $modelManager->saveToFile($model, $this->modelFile);
            }
        }
        return $this;
    }


    public function predict(){
        $this->predictions = [];
        if ($this->all && count($this->all->data) > 0 && file_exists($this->modelFile)) {
            $last = $this->getLasConcurso($this->all->data);
            foreach (range(1, 6, 1) as $n) {
                $column = "coluna_{$n}";
                $modelManager = new ModelManager();
                $model = $modelManager->restoreFromFile($this->modelFile);
                $this->predictions[$column] = $model->predict([(int) $last->$column, $this->dateToTime($last->data_do_sorteio)]);

                if (method_exists($model, 'getIntercept')) 
                    $this->predictions["{$column}_intercept"] = $model->getIntercept();
                if (method_exists($model, 'getCoefficients')) {
                    $this->predictions["{$column}_coefficients_0"] = $model->getCoefficients()[0];
                    $this->predictions["{$column}_coefficients_1"] = $model->getCoefficients()[1];
                }
            }
        }
        return $this;
    }

    public function showPredictions(){
        echo "\n* ~ {$this->algorithm} 1 Predict ~*\n\n";
        foreach ($this->predictions as $col => $predict) {
            echo "{$col}: {$predict}\n";
        }
        return $this;
    }

    public function getPredictions() :array
    {
        return $this->predictions;
    }

    public function comparePredictions() :array
    {
        $predictions = $assetions = $last = [];
        foreach ($this->getPredictions() as $index => $value) if (strlen($index) === 8)  $predictions[$index] = $value;

        foreach($this->getLasConcurso($this->all->data) as $column => $value ) {
            if (strlen($column) === 8 && stripos($column, "coluna_") !== false) $last[$column] = (int) $value;
        }

        $assetions_count = 0;
        foreach($last as $column => $prediction) {
            if (strlen($column) === 8 && stripos($column, "coluna_") !== false) $assetions[$column] = ((int) $prediction === (int) $last[$column]);
            if ($assetions[$column] === true) $assetions_count++;
        }
    
        return [
            "last" => $last,
            "predictions" => $predictions,
            "assetions" => $assetions,
            "assetions_percent" => (100 / 6) * $assetions_count,
        ];
    }


    public function run(bool $train = true, bool $show = true){
        if ($train) $this->train();
        $this->predict();
        if ($show) $this->showPredictions();
    }
}
