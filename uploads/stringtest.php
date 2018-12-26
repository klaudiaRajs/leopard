<?php

echo "najprostszy";
echo "najprostszy /* ";
echo "najprostszy //";
echo "prosty \" z srodkiem";
echo "prosty ' z czyms";
echo "prosty z $zmianna";
echo "prosty z \$zmianna";
echo "prosty z tablica $var[0]";
echo "prosty z tablica \$var[0]";
echo "prosty z {$zmienna}";
echo "prosty z {\$zmienna}";
echo "prosty z {$var[0]}";
echo "prosty z \{$var[0]}";
echo "dlugi
 z \{$var[0]}";

echo '2 najprostszy';
echo '2 najprostszy /*';
echo '2 najprostszy //';
echo '2 prosty " z srodkiem';
echo '2 prosty \' z czyms';
echo '2 prosty z $zmianna';
echo '2 prosty z \$zmianna';
echo '2 prosty z tablica $var[0]';
echo '2 prosty z tablica \$var[0]';
echo '2 prosty z {$zmienna}';
echo '2 prosty z {\$zmienna}';
echo '2 prosty z {$var[0]}';
echo '2 prosty z \{$var[0]}';
echo '2 dlugi
 z \{$var[0]}';

echo <<<HERE
 test " ' HERE
 /* \\' \'
 $zmienna
 \$zmienna
 $var[0]
 \$var[0]
HERE;

echo <<<'HERE'
 test " ' HERE
 /* \\' \'
 $zmienna
 \$zmienna
 $var[0]
 \$var[0]
HERE;
