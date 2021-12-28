<?php
namespace App;
use \Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;
use \Phpml\ModelManager;


class MegaRegression3 {
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

    public function getLasConcurso(int $index = 1){
        return (object) $this->all->data[count($this->all->data) - $index];; 
    }


    public function train(){
        if ($this->all && count($this->all->data) > 0) {
            $samples = $targets = [];
            foreach($this->all->data as $k => $concurso) {
                if ($k < (count($this->all->data))) {
                    foreach (range(1, 6, 1) as $n) {
                        $column = "coluna_{$n}";
                        $targets[$column][] =  [$concurso->$column];
                        $samples[$column][] = [$this->dateToTime($concurso->data_do_sorteio)];
                    }
                }
            }

            foreach (range(1, 6, 1) as $n) {
                $column = "coluna_{$n}";
                $model = new $this->algorithm();
                $model->train($samples[$column],  $targets[$column]);
                $modelManager = new ModelManager();
                $modelManager->saveToFile($model, $this->modelFile."_".$column);
            }
        }
        return $this;
    }


    public function predict(){
        $this->predictions = [];
        if ($this->all && count($this->all->data) > 0) {
            $last = $this->getLasConcurso(2);
            foreach (range(1, 6, 1) as $n) {
                $column = "coluna_{$n}";
                $modelManager = new ModelManager();
                try {
                    $model = $modelManager->restoreFromFile($this->modelFile."_".$column);
                } catch (\Exception $e) {
                    echo "File no exits!";
                    return;
                }

                $this->predictions[$column] = $model->predict([$this->dateToTime($last->data_do_sorteio)]);
                
                if (method_exists($model, 'getIntercept')) 
                    $this->predictions["{$column}_intercept"] = $model->getIntercept();
                if (method_exists($model, 'getCoefficients')) {
                    $this->predictions["{$column}_coefficients_0"] = $model->getCoefficients()[0];
                    if (count($model->getCoefficients()) > 1)$this->predictions["{$column}_coefficients_1"] = $model->getCoefficients()[1];
                }
            }
        }
        return $this;
    }

    public function showPredictions(){
        echo "\n* ~ {$this->algorithm} 3 Predict ~*\n\n";
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

        foreach($this->getLasConcurso() as $column => $value ) {
            if (strlen($column) === 8 && stripos($column, "coluna_") !== false) $last[$column] = (int) $value;
        }

        $assetions_count = 0;
        foreach($predictions as $column => $prediction) {
            if (strlen($column) === 8 && stripos($column, "coluna_") !== false) $assetions[$column] = ((int) $prediction == (int) $last[$column]);
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
