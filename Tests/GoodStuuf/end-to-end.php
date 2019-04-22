<?php

require __DIR__ . '/../../vendor/autoload.php';

$files = glob('../../upload/*.php');
$testData = [];
foreach($files as $file) {
    echo "Analizying $file...\n";
    $startTime = microtime(true);
    $fileAnalyzer = new \MyApp\Controller\FileAnalyzer();
    $statKeeper = new MyApp\Statistics\StatKeeper();
    $output = $fileAnalyzer->analyzeUpload(basename($file), 1);
    $endTime = microtime(true);
    $testData[] = [
        'file' => $file,
        'time' => $endTime - $startTime,
        'output' => md5($output),
    ];
    file_put_contents(basename($file).'.output', $output);
    echo "Finished after " . ($endTime - $startTime) . "\n";
}
file_put_contents('test.json', json_encode($testData, JSON_PRETTY_PRINT));

$firstPass = json_decode(file_get_contents('..\firstPass\test.json'), true);
foreach($firstPass as $idx => $item) {
    if ($testData[$idx]['file'] !== $item['file']) {
        echo "We are analyzing not the same files\n";
    }
    if ($testData[$idx]['output'] != $item['output']) {
        echo "File {$item['file']} does not generate the same output\n";
    }
    if ($testData[$idx]['time'] > $item['time']) {
        echo "For file {$item['file']} time was affected (before: {$item['time']}, after: {$testData[$idx]['time']})\n";
    }
}
exit;