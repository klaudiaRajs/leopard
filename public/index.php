<?php

use MyApp\Analyzer\Rules;
use MyApp\Config\Config;
use MyApp\Controller\FileAnalyzer;
use MyApp\Statistics\StatKeeper;
use MyApp\View\ViewRenderer;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;
set_time_limit(60);
$app->register(new FormServiceProvider());
$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.domains' => array(),
));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));

$app['asset.host'] = Config::URL;

//route for adding files for analysis
$app->match('/addFile', function(Request $request) use ($app){
    $data = array(
        'file' => 'Please, provide a file to analyze',
        'introducedProblems' => 'Please provide number of introduced problems',
    );

    //gets automatically build form
    $form = $app['form.factory']->createBuilder(FormType::class, $data)
        ->setAction($app['url_generator']->generate('uploadFile'))
        ->setMethod('POST')
        ->add('file', FileType::class, array('data_class' => null))
        ->add('introducedProblems')
        ->add('convention', ChoiceType::class, array(
            'choices' => array(
                'camelCaseConvention' => 'camelCase',
                'PascalCaseConvention' => 'Pascal',
                'underscore_convention' => 'underscore'),
            'expanded' => true,
        ))
        ->add('submit', SubmitType::class, [
            'label' => 'Save',
        ])
        ->getForm();

    $form->handleRequest($request);

    return $app['twig']->render('index.twig', array('form' => $form->createView()));
})->bind('addFile');

//redirect from root to /addFile
$app->get('/', function() use ($app){
    return $app->redirect('addFile');
});

$app->get('uploadFile', function(Request $request) use ($app){
    $files = $request->files->get('form');
    $fileAnalyzer = new FileAnalyzer();

    //handling of missing file
    if (!$files) {
        $result = "Please, provide files using this site: <a href=\"" . $app['asset.host'] . "addFile\">Add File</a>";
    }

    $result = $fileAnalyzer->analyzeUpload("", 1, $result);
    return $result;
});

$app->post('/uploadFile', function(Request $request) use ($app){
    $files = $request->files->get('form');
    $fileAnalyzer = new FileAnalyzer();

    if (!$files) {
        $result = "Please, provide files using this site: <a href=\"" . $app['asset.host'] . "addFile\">Add File</a>";
    } else {
        $result = '';

        uploadFiles($files);

        //Gets data from form
        $requestFields = $request->get('form');

        //Analyzes contents of files
        /** @var FileAnalyzer $fileAnalyzer */
        $result = processRequest($files, $requestFields, $result, $fileAnalyzer);
        //Adds summary of analysis
        $result .= $fileAnalyzer->analyzeResults();
    }

    //Required for additional formatting
    $result = $fileAnalyzer->analyzeUpload("", 1, $result);

    return $result;
})->bind('uploadFile');

//Function saves files uploaded in the form
function uploadFiles($files){
    foreach ($files as $file) {
        if (is_array($file)) {
            $file = $file['file'];
        }
        $directory = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "upload" . DIRECTORY_SEPARATOR;
        $fileName = $file->getClientOriginalName();
        try {
            $file->move($directory, $fileName);
        }catch(\Exception $e) {
            echo $e->getMessage();
        }
    }
}

$app->get('/testRun', function() use ($app){
    //Provides data for analysis in test run
    $files = [['file' => 'FunctionSimilarityAnalyser.php', 'convention' => 'camelCase', 'introducedProblems' => 25], ['file' => 'Books.php', 'convention' => 'camelCase', 'introducedProblems' => 6]];
    $fileAnalyzer = new FileAnalyzer();
    $result = '';
    //Analyzes contents of files
    /** @var FileAnalyzer $fileAnalyzer */
    $result = processRequestTestRun($files, $result, $fileAnalyzer);
    //Adds summary of analysis
    $result .= $fileAnalyzer->analyzeResults();
    //Required for additional formatting
    $result = $fileAnalyzer->analyzeUpload("", 1, $result);
    return $result;
});

//Renders form for adding files for analysis
$app->get('/addFile', function() use ($app){
    return ViewRenderer::render('FileUploader');
});

//Handles exceptions
$app->error(function(\Exception $e, Request $request, $code){
    echo 'We are sorry, but something went terribly wrong.<br/>';
    echo 'Code: ' . $code . '<br/>';
    exit;
});

//Provides separate method for analyzing files for test runs. Simpler than imitating fields from the form.
function processRequestTestRun($files, $result, FileAnalyzer $fileAnalyzer){
    foreach ($files as $file) {
        Rules::setNamingConvention($file['convention']);
        $result .= $fileAnalyzer->analyzeUpload($file['file'], $file['introducedProblems']);
    }
    $fileAnalyzer->statResultFilePath = StatKeeper::saveProgress();
    return $result;
}

//Processes multiple files
function processRequest($files, $requestFields, $result, FileAnalyzer $fileAnalyzer){
    if (isset($requestFields['convention'])) {
        Rules::setNamingConvention($requestFields['convention']);
        if (isset($files['file'])) {
            $fileName = $files['file']->getClientOriginalName();
            $result .= $fileAnalyzer->analyzeUpload($fileName, $requestFields['introducedProblems']);
        }
    }

    if (isset($requestFields)) {
        foreach ($requestFields as $key => $formField) {
            if (is_int($key)) {
                Rules::setNamingConvention($formField['convention']);
                $fileName = $files[$key]['file']->getClientOriginalName();
                $result .= $fileAnalyzer->analyzeUpload($fileName, $formField['introducedProblems']);
            }
        }
    } else {
        $result .= "File not found";
    }

    $fileAnalyzer->statResultFilePath = StatKeeper::saveProgress();
    return $result;
}

$app->run();