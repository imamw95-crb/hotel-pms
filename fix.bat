@echo off
cd /d c:\laragon\www\hotel-pms
rmdir /s /q vendor\psysh 2>nul
del /f vendor\psysh\src\Exception\ParseErrorException.php 2>nul
del /f vendor\psysh\src\Exception\ParseErrorException.php.bak 2>nul
echo Done!
