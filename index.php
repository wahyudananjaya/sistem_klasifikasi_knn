<?php
require_once __DIR__."/vendor/autoload.php";
use Phpml\Dataset\CsvDataset;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WordTokenizer;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\Dataset\ArrayDataset;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\Classification\KNearestNeighbors;
use Phpml\Metric\Accuracy;
use \Phpml\Math\Set;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Klasifikasi Teks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
</head>
<body>
    
<?php
// Load Dataset
$dataset = new CsvDataset('data2.csv',1,true,";");
$samples = [];
foreach ($dataset->getSamples() as $sample){
    $samples[] = $sample[0];
}
$data_dataset = $samples;
 // Data Prepocessing 
 $vectorizer = new TokenCountVectorizer(new WordTokenizer());
    
 $vectorizer->fit($samples);
 $vectorizer->transform($samples);
 
 // TF-id Transformer
 $tfIdfTransformer = new TfIdfTransformer();
 
 $tfIdfTransformer->fit($samples);
 $tfIdfTransformer->transform($samples);
 
 // Generate The Training Dataset
 $dataset = new ArrayDataset($samples, $dataset->getTargets());
 
 // Random Split Test
 $randomSplit = new StratifiedRandomSplit($dataset, 0.1);
 
 $trainingSamples = $randomSplit->getTrainSamples();
 $trainingLabels     = $randomSplit->getTrainLabels();
 
 $testSamples = $randomSplit->getTestSamples();
 $testLabels      = $randomSplit->getTestLabels();
 
 // Classification and train
 $classifier = new KNearestNeighbors($k=9);
 $classifier->train($trainingSamples, $trainingLabels);
 
 // Predict the sample
 $predictedLabels = $classifier->predict($testSamples);
?>
<div class="container-sm mt-6">
    <h6 class="display-6">Sistem Klasifikasi K-NN</h6>
</div>
<div class="container-sm mt-4">
     <form action="" method="POST">
		<div class="form-group pb-4">
			<label for="teksproses">Masukan Teks Berita:</label>
			<textarea class="form-control" id="teksberita" name="teksberita" rows="3"></textarea>
            <label for="teksproses"><?//= ($berita=='')? "Test":"B"; ?></label>
		</div>
        <button type="submit" class="btn btn-success" name="proses" >Proses</button>
     </form>
</div>

<?php
    
if(isset($_POST['proses'])){
     // Data Prepocessing Variable Input
     $berita = array_search($_POST['teksberita'],$data_dataset);
     $datasamples=$dataset->getSamples();
     $beritaLabel = $classifier->predict($datasamples[$berita]);
    
}
    
?>

<div class="container-sm mt-6">
    <p>
        <h4>
            <?= "Berita";?>
        </h4>
        <h6>
        <?= empty($_POST['teksberita'])?"-":$_POST['teksberita'];?>
        </h6>
        <h4>
            <?= "Klasifikasi";?>
        </h4>
          <h5>
              <?= empty($beritaLabel)?"-":$beritaLabel;?>
          </h5>  
    </p>
    <h5> <?= 'Accuracy: '.Accuracy::score($testLabels, $predictedLabels); ?></h5>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-u1OknCvxWvY5kfmNBILK2hRnQC3Pr17a+RTT6rIHI7NnikvbZlHgTPOOmMi466C8" crossorigin="anonymous"></script>
</body>
</html>