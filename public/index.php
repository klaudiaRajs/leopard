<?php

use MyApp\Analyzer\Rules;
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

$app->register(new FormServiceProvider());
$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.domains' => array(),
));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));

$app['asset.host'] = 'http://leopardslim.com/';

$app->match('/addFile', function(Request $request) use ($app){
    $data = array(
        'file' => 'Please, provide a file to analyze',
        'introducedProblems' => 'Please provide number of introduced problems',
    );

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


$app->get('/', function() use ($app){
    return $app->redirect('addFile');
});

$app->get('uploadFile', function(Request $request) use ($app){
    $files = $request->files->get('form');
    $fileAnalyzer = new FileAnalyzer();

    if (!$files) {
        $result = "Please, provide files using this site: <a href=\"" . $app['asset.host'] . "addFile\">Add File</a>";
    }

    $result = $fileAnalyzer->analyzeUpload("", 1, new StatKeeper(), $result);
    return $result;
});

$app->post('/uploadFile', function(Request $request) use ($app){
    $files = $request->files->get('form');
    $fileAnalyzer = new FileAnalyzer();

    if (!$files) {
        $result = "Please, provide files using this site: <a href=\"" . $app['asset.host'] . "addFile\">Add File</a>";
    } else {
        $result = '';

        foreach ($files as $file) {
            if (is_array($file)) {
                $file = $file['file'];
            }
            $directory = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "upload" . DIRECTORY_SEPARATOR;
            //        $fileName = count(scandir($directory)) . $file->getClientOriginalName();
            $fileName = $file->getClientOriginalName();
            try {
                $file->move($directory, $fileName);
            }catch(\Exception $e) {
                echo $e->getMessage();
            }
        }

        $requestFields = $request->get('form');

        /** @var FileAnalyzer $fileAnalyzer */
        $result = processRequest($files, $requestFields, $result, $fileAnalyzer);
        $result .= $fileAnalyzer->analyzeResults();
    }

    $result = $fileAnalyzer->analyzeUpload("", 1, new StatKeeper(), $result);

    return $result;
})->bind('uploadFile');

$app->get('/addFile', function() use ($app){
    return ViewRenderer::render('FileUploader');
});

$app->error(function(\Exception $e, Request $request, $code){
    echo 'We are sorry, but something went terribly wrong.<br/>';
    echo 'Code: ' . $code . '<br/>';
    exit;
    //    echo '<pre>';
    //    print_r($e);
});

function processRequest($files, $requestFields, $result, FileAnalyzer $fileAnalyzer){

    $statKeeper = new StatKeeper();
    if (isset($requestFields['convention'])) {
        Rules::setNamingConvention($requestFields['convention']);
        if (isset($files['file'])) {
            $fileName = $files['file']->getClientOriginalName();
            $result .= $fileAnalyzer->analyzeUpload($fileName, $requestFields['introducedProblems'], $statKeeper);
        }
    }

    if (isset($requestFields)) {
        foreach ($requestFields as $key => $formField) {
            if (is_int($key)) {
                Rules::setNamingConvention($formField['convention']);
                $fileName = $files[$key]['file']->getClientOriginalName();
                $result .= $fileAnalyzer->analyzeUpload($fileName, $formField['introducedProblems'], $statKeeper);
            }
        }
    } else {
        $result .= "File not found";
    }

    $fileAnalyzer->statResultFilePath = $statKeeper->saveProgress();
    return $result;
}

$app->run();