<?php
// json to swagger
// based on https://roger13.github.io/SwagDefGen/
// 1. copy http-response (json)
// 2. past to https://roger13.github.io/SwagDefGen/ and convert to json
// 3. copy json from https://roger13.github.io/SwagDefGen/ to file ./swagger.json
// 4. run ./json2swagger.php and copy result from ./swagger.json.yaml
$filePath = __DIR__ . '/swagger.json';
$filePathResult = $filePath . '.yaml';
$text = trim(file_get_contents($filePath));
$text = preg_replace('#"example": "(.*)"#', 'example: ~~~\\1~~~', $text);
$text = preg_replace('#\n(.+)}\,#', '', $text);
$text = preg_replace('#\n([\s]+)}#', '', $text);
$text = str_replace('",', '', $text); // `"type": "number",`   =>   `"type": "number`
$text = str_replace('": "', ': ', $text); // `"type": "number`   =>   `"type: number`
$text = str_replace('  "', '  ', $text); // `  "type: number`   =>   `  type: number`
$text = str_replace('": {', ': ', $text); // `title": {`   =>   `title: {`
$text = str_replace('"', '', $text); // `title": {`   =>   `title: {`
if (mb_substr($text, -1) === '}') {
    $text = mb_substr($text, 0, -1);
}
$text = str_replace('~~~', '"', $text);
file_put_contents($filePathResult, $text);
echo 'data converted to file: ' . $filePathResult . PHP_EOL;
