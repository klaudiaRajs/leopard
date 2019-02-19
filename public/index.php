<?php

use MyApp\Analyzer\Rules;
use MyApp\Stats\StatKeeper;
use MyApp\Controller\FileAnalyzer;
use MyApp\View\ViewRenderer;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

//@TODO delete this part after testing
$files = glob(__DIR__ . "/../stats/*"); // get all file names
foreach ($files as $file) { // iterate files
    if (is_file($file))
        unlink($file); // delete file
}

$app->register(new FormServiceProvider());
$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.domains' => array(),
));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));

$app->match('/addFile', function(Request $request) use ($app){
    $data = array(
        'file' => 'Please, provide a file to analyze',
        'introducedProblems' => 'Please provide number of introduced problems',
    );

    $form = $app['form.factory']->createBuilder(FormType::class, $data)
        ->setAction($app['url_generator']->generate('analyzeUpload'))
        ->setMethod('GET')
        ->add('file')
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
});


$app->get('/analyzeUpload', function(Request $request) use ($app){
    $result = '';
    $requestFields = $request->query->get('form');

//    Rules::setNamingConvention($request->query->get('form')['convention']);
    $fileAnalyzer = new FileAnalyzer();

    $result = processRequest($requestFields, $result, $fileAnalyzer);
    return $result;
})->bind('analyzeUpload');

function processRequest($requestFields, $result, $fileAnalyzer){
    if (isset($requestFields['convention'])) {
        Rules::setNamingConvention($requestFields['convention']);
        $file = $requestFields['file'];
        $result .= $fileAnalyzer->analyzeUpload($file, $requestFields['introducedProblems']);
    }

    foreach ($requestFields as $key => $formField) {
        if (is_int($key)) {
            Rules::setNamingConvention($formField['convention']);
            $file = $formField['file'];
            $result .= $fileAnalyzer->analyzeUpload($file, $formField['introducedProblems']);
        }
    }
    return $result;
}

$app->get('/saveStatsTesting', function() use ($app){
    $statKeeps = new StatKeeper();
    $statKeeps->addProgress('test.php', 2, 1);
    $file = $statKeeps->saveProgress();

    return file_get_contents($file);
});

$app->get('/addFile', function() use ($app){
    return ViewRenderer::render('FileUploader');
});

$app->error(function(\Exception $e, Request $request, $code){
    echo 'We are sorry, but something went terribly wrong.<br/>';
    echo 'Code: ' . $code . '<br/>';
    echo '<pre>';
    print_r($e);
});

$app->run();