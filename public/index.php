<?php

use MyApp\Analyzer\Rules;
use MyApp\Controller\FileAnalyzer;
use MyApp\Statistics\StatKeeper;
use MyApp\View\ViewRenderer;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

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

    $fileAnalyzer = new FileAnalyzer();

    $result = processRequest($requestFields, $result, $fileAnalyzer);
    $result .= $fileAnalyzer->analyzeResults();
    return $result;
})->bind('analyzeUpload');

$app->get('/addFile', function() use ($app){
    return ViewRenderer::render('FileUploader');
});

$app->error(function(\Exception $e, Request $request, $code){
    echo 'We are sorry, but something went terribly wrong.<br/>';
    echo 'Code: ' . $code . '<br/>';
    echo '<pre>';
    print_r($e);
});

function processRequest($requestFields, $result, $fileAnalyzer){
    $statKeeper = new StatKeeper();
    if (isset($requestFields['convention'])) {
        Rules::setNamingConvention($requestFields['convention']);
        $file = $requestFields['file'];
        $result .= $fileAnalyzer->analyzeUpload($file, $requestFields['introducedProblems'], $statKeeper);
    }

    foreach ($requestFields as $key => $formField) {
        if (is_int($key)) {
            Rules::setNamingConvention($formField['convention']);
            $file = $formField['file'];
            $result .= $fileAnalyzer->analyzeUpload($file, $formField['introducedProblems'], $statKeeper);
        }
    }
    $fileAnalyzer->statResultFilePath = $statKeeper->saveProgress();
    return $result;
}

$app->run();